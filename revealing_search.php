<?php

require_once('lib/init.php');

//$search_request['s_all']=$_REQUEST['TranscriptionText'];
$search_request['s_all']="everybody";
$results=run_search($search_request);
$song_url=$results[0]->get_url();

echo $song_url."<br/>";
echo $session_id."<br/>";