<?php
    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Say>We are searching for your song and will call you back in a moment to play it</Say>
    <Say><?php echo $_REQUEST['Caller'];?></Say>
</Response>
