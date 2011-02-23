<?php
    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Say>lay it on me bro!</Say>
    <Record transcribe="true" transcribeCallback="<?php 
        echo "song_callback.php?email=".urlencode($_REQUEST['email']); ?>"        
        action="dump.php" maxLength="15" />
</Response>
