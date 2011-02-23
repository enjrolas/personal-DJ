<?php
/*

 Copyright (c) 2001 - 2006 Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License v2
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

/* Because this is accessed via Ajax we are going to allow the session_id 
 * as part of the get request
 */

define('NO_SESSION','1');
require_once('../lib/init.php');

/* Verify the existance of the Session they passed in */
if (!session_exists($_REQUEST['sessid'])) { exit(); }

$GLOBALS['user'] = new User($_REQUEST['user_id']);
$action = scrub_in($_REQUEST['action']);

/* Set the correct headers */
header("Content-type: text/xml; charset=" . conf('site_charset'));
header("Content-Disposition: attachment; filename=ajax.xml");
header("Expires Sun, 19 Nov 1978 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache"); 

switch ($action) { 
	/* Controls Localplay */
	case 'localplay':
		$localplay = init_localplay();
		$localplay->connect();
		$function 	= scrub_in($_GET['cmd']);
		$value		= scrub_in($_GET['value']);
		$return		= scrub_in($_GET['return']);
		$localplay->$function($value); 
		/* Return information based on function */
		switch($function) { 
			case 'skip':
				ob_start();
				require_once(conf('prefix') . '/templates/show_localplay_playlist.inc.php');
				$results['lp_playlist'] = ob_get_contents();
				ob_end_clean();
			case 'volume_up':
			case 'volume_down':
			case 'volume_mute':
				$status = $localplay->status();
				$results['lp_volume']	= $status['volume'];
			break;
			case 'next':
			case 'stop':
			case 'prev':
			case 'pause':
			case 'play':
				if ($return) { 
					$results['lp_playing'] = $localplay->get_user_playing(); 
				} 	
			default:
				$results['rfc3514'] = '0x1';	
			break;
		} // end switch on cmd
		$xml_doc = xml_from_array($results);
		echo $xml_doc;
	break;
	/* For changing the current play type FIXME:: need to allow select of any type  */
	case 'change_play_type':
		$pref_id = get_preference_id('play_type');
		$GLOBALS['user']->update_preference($pref_id,$_GET['type']);

		/* Uses a drop down, no need to replace text */
		$results['play_type'] = '';
		$xml_doc = xml_from_array($results);
		echo $xml_doc;
	break;
	/* reloading the now playing information */
	case 'reloadnp':
		ob_start();
		show_now_playing();	
		$results['np_data'] = ob_get_contents();
		ob_clean();
		$data = get_recently_played(); 
		if (count($data)) { require_once(conf('prefix') . '/templates/show_recently_played.inc.php'); }
		$results['recently_played'] = ob_get_contents(); 
		ob_end_clean();
		$xml_doc = xml_from_array($results);
		echo $xml_doc;
	break;
	/* Reloading of the TV Now Playing, formated differently */
	case 'reload_np_tv':

		/* Update the Now Playing */
		ob_start();
		require_once(conf('prefix') . '/templates/show_tv_nowplaying.inc.php');
		$results = array();
		$results['tv_np'] = ob_get_contents(); 
		ob_end_clean(); 

		/* Update the Playlist */
		ob_start();
		$tmp_playlist = get_democratic_playlist(-1);
                $songs = $tmp_playlist->get_items();
                require_once(conf('prefix') . '/templates/show_tv_playlist.inc.php');
                $results['tv_playlist'] = ob_get_contents();
                ob_end_clean();
		
		$xml_doc = xml_from_array($results);
		echo $xml_doc;
	break;
	/* Setting ratings */
	case 'set_rating':
		ob_start(); 
		$rating = new Rating($_REQUEST['object_id'],$_REQUEST['rating_type']);
		$rating->set_rating($_REQUEST['rating']);
		show_rating($_REQUEST['object_id'],$_REQUEST['rating_type']);
		$key = "rating_" . $_REQUEST['object_id'] . "_" . $_REQUEST['rating_type'];
		$results[$key] = ob_get_contents();
		ob_end_clean();
		$xml_doc = xml_from_array($results);
		echo $xml_doc;
	break;
	/* Activate the Democratic Instance */
	case 'tv_activate':
		if (!$GLOBALS['user']->has_access(100)) { break; } 
		$tmp_playlist = new tmpPlaylist();
		/* Pull in the info we need */
		$base_id 	= scrub_in($_REQUEST['playlist_id']);

		/* create the playlist */
		$playlist_id = $tmp_playlist->create('0','vote','song',$base_id);

		$playlist = new tmpPlaylist($playlist_id);
		ob_start();
		require_once(conf('prefix') . '/templates/show_tv_adminctl.inc.php');
		$results['tv_control'] = ob_get_contents(); 
		ob_end_clean();
		$xml_doc = xml_from_array($results);
		echo $xml_doc;
	break;
	/* Admin Actions on the tv page */
	case 'tv_admin':
		if (!$GLOBALS['user']->has_access(100) || !conf('allow_democratic_playback')) { break; } 

		/* Get the playlist */
		$tmp_playlist = get_democratic_playlist(-1); 
		
		ob_start();
	
		/* Switch on the command we need to run */
		switch ($_REQUEST['cmd']) { 
			case 'delete':
				$tmp_playlist->delete_track($_REQUEST['track_id']); 
				$songs = $tmp_playlist->get_items(); 
				require_once(conf('prefix') . '/templates/show_tv_playlist.inc.php');
				$results['tv_playlist'] = ob_get_contents(); 
			break;	
			default: 
				// Rien a faire
			break;
		} // end switch

		ob_end_clean(); 
		$xml_doc = xml_from_array($results);
		echo $xml_doc; 
	break;
	/* This can be a positve (1) or negative (-1) vote */
	case 'vote':
		if (!$GLOBALS['user']->has_access(25) || !conf('allow_democratic_playback')) { break; }
		/* Get the playlist */
		$tmp_playlist = get_democratic_playlist(-1);
		
		if ($_REQUEST['vote'] == '1') { 
			$tmp_playlist->vote(array($_REQUEST['object_id']));
		}
		else { 
			$tmp_playlist->remove_vote($_REQUEST['object_id']);
		}

		ob_start();
		$songs = $tmp_playlist->get_items();
		require_once(conf('prefix') . '/templates/show_tv_playlist.inc.php');
		$results['tv_playlist'] = ob_get_contents(); 
		ob_end_clean();
		$xml_doc = xml_from_array($results);
		echo $xml_doc; 
	break;
	default:
		$results['rfc3514'] = '0x1';
		echo xml_from_array($results); 
	break;
} // end switch action
?>
