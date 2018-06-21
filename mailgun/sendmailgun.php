<?php
# Allows the use of mailgun via CURL instead of importing another library
# Ron Egli - Sendmailgun.php
# Github.com/smugzombie

function sendmailgun($to,$toname,$mailfromnane,$mailfrom,$subject,$html,$text,$tag,$replyto,$cc="",$attachments=""){
    define('MAILGUN_URL', 'https://api.mailgun.net/v3/<YOUR DOMAIN>');
    define('MAILGUN_KEY', 'key-<YOUR KEY>');
    $array_data = array(
                'from'=> $mailfromname .'<'.$mailfrom.'>',
                'to'=>$toname.'<'.$to.'>',
                'subject'=>$subject,
                'html'=>$html,
                'text'=>$text,
                'o:tracking'=>'no',
                'o:tracking-clicks'=>'no',
                'o:tracking-opens'=>'no',
                'o:tag'=>$tag,
                'h:Reply-To'=>$replyto
    );
    // If we see attachments, add them to the request. 
    if($attachments){
        if(is_array($attachments)){
                $x = 1;
                for($i = 0; $i < count($attachments); $i ++){
                        $array_data['attachment['.$x.']'] = curl_file_create($attachments[$i]);
                        $x ++;
                }
        }
        else{
                $array_data['attachment[1]'] = curl_file_create($attachments);
        }
    }
    // If we see a cc request, add it to the request
    if($cc){
        $array_data['cc'] = $cc;
    }
    error_log(json_encode($array_data));
    $session = curl_init(MAILGUN_URL.'/messages');
    curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($session, CURLOPT_USERPWD, 'api:'.MAILGUN_KEY);
    curl_setopt($session, CURLOPT_POST, true);
    curl_setopt($session, CURLOPT_POSTFIELDS, $array_data);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($session);
    curl_close($session);
    $results = json_decode($response, true);
    return $results;
}
