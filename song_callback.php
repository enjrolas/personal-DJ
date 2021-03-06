<?php
require "twilio.php";

/* Twilio REST API version */
$ApiVersion = "2008-08-01";

/* Set our AccountSid and AuthToken */
$AccountSid = "****";
$AuthToken = "*****";

/* Outgoing Caller ID you have previously validated with Twilio */
$CallerID = '6173541973';

/* Instantiate a new Twilio Rest Client */
$client = new TwilioRestClient($AccountSid, $AuthToken);

    $headers = 'From: help@twilio.com' . "\r\n" .
        'Reply-To: help@twilio.com' . "\r\n" .
        'X-Mailer: Twilio';
//    mail("alex@nublabs.com", "song request transcription from song callback", "transcription: ".$_REQUEST['TranscriptionText']." caller: ".$_REQUEST['Caller']."<a href=\"".$_REQUEST['RecordingUrl']."\">message recording</a> transcription status: ".$_REQUEST['TranscriptionStatus'], $headers);
$TranscriptionText=$_REQUEST['TranscriptionText'];
$TranscriptionText=stripslashes($TranscriptionText);

/* make Twilio REST request to initiate outgoing call */
$response = $client->request("/$ApiVersion/Accounts/$AccountSid/Calls",
			     "POST", array(
					   "Caller" => $CallerID,
					   "Called" => $_REQUEST['Caller'],
					   "Url" => "http://www.artiswrong.com/ampache/simple_search.php?TranscriptionText=".urlencode($TranscriptionText)));


//echo $_REQUEST['TranscriptionText'];
if($response->IsError) {
  $err = urlencode($response->ErrorMessage);
  header("Location: .?msg=$err");
  die;
}

/* redirect back to the main page with CallSid */
$msg = urlencode("Calling... ".$TranscriptionText." ".$_REQUEST['Caller']);
header("Location: .?msg=$msg&CallSid=".
       $response->ResponseXml->Call->Sid);

?>