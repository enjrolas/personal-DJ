<?php
/*

 Copyright 2001 - 2006 Ampache.org
 All Rights Reserved

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

/**
 * delete_now_playing
 * This function checks to see if we should delete the last now playing entry now that it's
 * finished streaming the song. Basicly this is an exception for WMP10
 * @package General
 * @catagory Now Playing
 */
function delete_now_playing($insert_id) { 

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

	// Account for WMP and the Flash Player
	if (stristr($user_agent,"NSPlayer") || $_REQUEST['flash_hack'] == 1) { 
                // Commented out until I can figure out the
                // trick to making this work
                return true;
        }

        // Remove the song from the now_playing table
        $sql = "DELETE FROM now_playing WHERE id = '$insert_id'";
        $db_result = mysql_query($sql, dbh());

} // delete_now_playing

/** 
 * gc_now_playing
 * this is a garbage collection function for now playing this is called every time something
 * is streamed
 * @package General
 * @catagory Now Playing
 */
function gc_now_playing() { 

	/* Account for WMP11's Initial Streaming */
	if (strstr($_SERVER['HTTP_USER_AGENT'],"WMFSDK/11")) { return false; } 

        $time 		= time();
        $expire 	= $time - 3200;  // 86400 seconds = 1 day
	$session_id	= sql_escape($_REQUEST['sid']);
	if (strlen($session_id)) { 
		$session_sql = " OR session = '$session_id'";
	}	

        $sql = "DELETE FROM now_playing WHERE start_time < $expire" . $session_sql;
        $db_result = mysql_query($sql, dbh());
        
} // gc_now_playing

/**
 * insert_now_playing
 * This function takes care of inserting the now playing data
 * we use this function because we need to do thing differently
 * depending upon which play is actually streaming
 * @package General
 * @catagory Now Playing
 */
function insert_now_playing($song_id,$uid,$song_length) {

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $time = time();

        /* Windows Media Player is evil and it makes multiple requests per song */
        if (stristr($user_agent,"Windows-Media-Player")) { return false; }

	// This version of ampache can't handle the abusive streaming behavior of Audacious 1.3.x
	if (stristr($user_agent, "Audacious/1.3")) { return false; } 

	/* Check for Windows Media Player 11 */
	if (strstr($user_agent,'NSPlayer/11') AND !strstr($user_agent,'WMFSDK/11')) { return false; } 

        /* Set the Expire Time */

        // If they are using Windows media player
	if (strstr($user_agent,"NSPlayer") || $_REQUEST['flash_hack'] == 1) { 
                // WMP does keep the session open so we need to cheat a little here
		$session_id 	= sql_escape($_REQUEST['sid']); 
        }

	/* Set expire time for worst case clean up */
	$expire = $time;

        $sql = "INSERT INTO now_playing (`song_id`, `user`, `start_time`,`session`)" .
                " VALUES ('$song_id', '$uid', '$expire','$session_id')";

        $db_result = mysql_query($sql, dbh());

        $insert_id = mysql_insert_id(dbh());

        return $insert_id;

} // insert_now_playing

/**
 * check_lock_songs
 * This checks to see if the song is already playing, if it is then it prevents the user
 * from streaming it
 * @package General
 * @catagory Security
 */
function check_lock_songs($song_id) { 

	$sql = "SELECT song_id FROM now_playing " . 
		"WHERE song_id = '$song_id'";
	$db_results = mysql_query($sql, dbh());

	if (mysql_num_rows($db_results)) { 
		debug_event('lock_songs','Song Already Playing, skipping...','5'); 
		return false;
	}

	return true;

} // check_lock_songs

/**
 * start_downsample
 * This is a rather complext function that starts the downsampling of a song and returns the 
 * opened file handled
 * @package General
 * @catagory Downsample
 * @param $song	The Song Object
 */
function start_downsample($song,$now_playing_id=0,$song_name=0) { 

	/* Check to see if bitrates are set if so let's go ahead and optomize! */
	$max_bitrate = conf('max_bit_rate');
	$min_bitrate = conf('min_bit_rate');
	$time = time();
	$dbh = dbh();
	$user_sample_rate = $GLOBALS['user']->prefs['sample_rate'];
	$browser = new Browser();

	if (!$song_name) { 
		$song_name = $song->f_artist_full . " - " . $song->title . "." . $song->type;
	}

	if ($max_bitrate > 1 AND $min_bitrate < $max_bitrate) {   
		$last_seen_time = $time - 1200; //20 min.

		$sql = "SELECT COUNT(*) FROM now_playing, user_preference, preferences " . 
			"WHERE preferences.name = 'play_type' AND user_preference.preference = preferences.id " . 
			"AND now_playing.user = user_preference.user AND user_preference.value='downsample'";
		$db_results = mysql_query($sql,$dbh);
		$results = mysql_fetch_row($db_results);

		// Current number of active streams (current is already in now playing)
		$active_streams = $results[0];


		/* If only one user, they'll get all available.  Otherwise split up equally. */
		$sample_rate = floor($max_bitrate/$active_streams);

		/* If min_bitrate is set, then we'll exit if the bandwidth would need to be split up smaller than the min. */
		if ($min_bitrate > 1 AND ($max_bitrate/$active_streams) < $min_bitrate) {
               
			/* Log the failure */
			debug_event('downsample',"Error: Max bandwidith already allocated. $active_streams Active Streams",'2'); 
			
			/* Toast the now playing entry, then tell em to try again later */
			delete_now_playing($now_playing_id);
			echo "Maximum bandwidth already allocated.  Try again later.";
        	        exit();

		}
		else {
               		$sample_rate = floor($max_bitrate/$active_streams);
		} // end else
     
		// Never go over the users sample rate 
		if ($sample_rate > $user_sample_rate) { $sample_rate = $user_sample_rate; }	

		debug_event('downsample',"Downsampled: $active_streams current active streams, downsampling to $sample_rate",'2');

	} // end if we've got bitrates

	else { 
		$sample_rate = $user_sample_rate;
	}

	/* Validate the bitrate */
	$sample_rate = validate_bitrate($sample_rate);
 
	/* Never Upsample a song */
	if (($sample_rate*1000) > $song->bitrate) {
		$sample_rate = validate_bitrate($song->bitrate)/1000;
		$sample_ratio = '1';
	}
	else { 
		/* Set the Sample Ratio */
		$sample_ratio = $sample_rate/($song->bitrate/1000);
	}


	header("Content-Length: " . $sample_ratio*$song->size);
        $browser->downloadHeaders($song_name, $song->mime, false,$sample_ratio*$song->size);

        /* Get Offset */
	$offset = ( $start*$song->time )/( $sample_ratio*$song->size );
        $offsetmm = floor($offset/60);
        $offsetss = floor($offset-$offsetmm*60);
        $offset   = sprintf("%02d.%02d",$offsetmm,$offsetss);

        /* Get EOF */
	$eofmm  = floor($song->time/60);
        $eofss  = floor($song->time-$eofmm*60);
        $eof    = sprintf("%02d.%02d",$eofmm,$eofss);

	$song_file = escapeshellarg($song->file);

        /* Replace Variables */
        $downsample_command = conf($song->stream_cmd());
        $downsample_command = str_replace("%FILE%",$song_file,$downsample_command);
        $downsample_command = str_replace("%OFFSET%",$offset,$downsample_command);
        $downsample_command = str_replace("%EOF%",$eof,$downsample_command);
        $downsample_command = str_replace("%SAMPLE%",$sample_rate,$downsample_command);

        // If we are debugging log this event
	$message = "Start Downsample: $downsample_command";
        debug_event('downsample',$message,'3');

	$fp = @popen($downsample_command, 'rb');

	/* We need more than just the handle here */
	$return_array['handle'] = $fp;
	$return_array['size']	= $sample_ratio*$song->size;

	return ($return_array);

} // start_downsample

/** 
 * validate_bitrate
 * this function takes a bitrate and returns a valid one
 * @package Stream
 * @catagory Downsample
 */
function validate_bitrate($bitrate) { 

	if (!$bitrate) { $bitrate = conf('sample_rate'); } 

	// Setup an array of valid bitrates for Lame (yea yea, others might be different :P)
        $valid_rate = array('32','40','56','64','80','96','112','128','160','192','224','256','320');

	/* Round to standard bitrates */
        $sample_rate = 8*(floor($bitrate/8));

	if (in_array($sample_rate,$valid_rate)) { 
		return $sample_rate;
	}

	/* See if it's less than the lowest one */
	if ($sample_rate < $valid_rate['0']) { 
		return $valid_rate['0'];
	}

        /* Check to see if it's over 320 */
        if ($sample_rate > 320) {
		return '320';
        }

        foreach ($valid_rate as $key=>$rate) {
		$next_key = $key+1;
                                
                if ($sample_rate > $rate AND $sample_rate < $valid_rate[$next_key]) {
			return $rate;
		} 
	} // end foreach

} // validate_bitrate

?>
