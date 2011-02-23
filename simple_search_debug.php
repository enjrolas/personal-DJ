<?php

require_once('lib/init.php');

$search_request['s_all']=$_REQUEST['transcriptionText'];
$results=run_search($search_request);
if($results)
  {
    $song_url="http://www.artiswrong.com:80/ampache/play/index.php?song=".$results[0]->id;
    //  $song_url=$results[0]->get_url();
  }
echo $song_url;

