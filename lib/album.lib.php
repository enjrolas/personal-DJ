<?php
/*

 This library handles album related functions.... wooo!
 //FIXME: Remove this in favor of /modules/class/album
*/

/*!
	@function get_albums
	@discussion pass a sql statement, and it gets full album info and returns
		an array of the goods.. can be set to format them as well
*/
function get_albums($sql, $action=0) {

	$db_results = mysql_query($sql, dbh());
	while ($r = mysql_fetch_array($db_results)) {
		$album = new Album($r[0]);
		$album->format_album();
		$albums[] = $album;
	}

	return $albums;


} // get_albums

/**
 * get_image_from_source
 * This gets an image for the album art from a source as 
 * defined in the passed array. Because we don't know where
 * its comming from we are a passed an array that can look like
 * ['url']	= URL *** OPTIONAL ***
 * ['file']	= FILENAME *** OPTIONAL ***
 * ['raw']	= Actual Image data, already captured
 */
function get_image_from_source($data) { 

	// Already have the data, this often comes from id3tags
	if (isset($data['raw'])) { 
		return $data['raw'];
	}

	// Check to see if it's a URL
	if (isset($data['url'])) { 
		$snoopy = new Snoopy(); 
		$snoopy->fetch($data['url']); 
		return $snoopy->results; 
	} 

	// Check to see if it's a FILE
	if (isset($data['file'])) { 
		$handle = fopen($data['file'],'rb'); 
		$image_data = fread($handle,filesize($data['file'])); 		
		fclose($handle); 
		return $image_data; 
	} 

	return false; 

} // get_image_from_source

/**
 * get_random_albums
 * This returns a random number of albums from the catalogs
 * this is used by the index to return some 'potential' albums to play
 */
function get_random_albums($count='') { 

	if (!$count) { $count = 5; }

        $count = intval($count);

        // We avoid a table scan by using the id index and then using a rand to pick a row #
        $sql = "SELECT `id` FROM `album` WHERE `art` IS NOT NULL";
        $db_results = mysql_query($sql,dbh());

        while ($r = mysql_fetch_assoc($db_results)) {
                $albums[] = $r['id'];
        }

        $total = count($albums);

	// We've gotta have some chance
	if ($total < $count+2) { return array(); } 

        for ($i=0; $i <= $count; $i++) {
		$tries++; 
                $record = rand(0,$total);
		// If we've already set this one, we gotta get another!
		if (isset($results[$record]) || !$albums[$record]) { $i--; continue; } 
                $results[$record] = $albums[$record];
		
		// If we still can't get this figured out just give up
		if ($tries > 50) { return array(); } 
        }

        return $results;

} // get_random_albums

?>
