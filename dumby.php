<?php

require_once('lib/init.php');

$search_request['s_all']=urldecode($_REQUEST['TranscriptionText']);
$results=run_search($search_request);
if($results)
  {
    $song_url="http://www.artiswrong.com:80/ampache/play/index.php?song=".$results[0]->id;
  }

    $headers = 'From: help@twilio.com' . "\r\n" .
        'Reply-To: help@twilio.com' . "\r\n" .
        'X-Mailer: Twilio';
//mail("alex@nublabs.com", "song request transcription from simple_search", "transcription: ".$_REQUEST['TranscriptionText']." song url: ".$song_url." phone number: ".$_REQUEST['Called'], $headers);
echo $search_request['s_all'];
?>
