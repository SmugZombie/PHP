<?php
# Allows you to setup a cron job to verify certificates in your environment are fixed before expiry
# Ron Egli - SSLChecker.php
# github.com/smugzombie

// Today
$today = time();
$nomorethan = 30; //Days allowed from expiry

// Load hosts from file
$hosts = explode("\n",file_get_contents("hosts.txt"));
$expiring_hosts = array();

for($i=0;$i<count($hosts); $i ++){
        $host = $hosts[$i];
        if($host){
                $certinfo = checkSSL($host);

                if($certinfo){

                        $certexpiry = $certinfo['validTo_time_t'];
                        $certexpires = secondsToDays((($certexpiry - $today)));

                        echo $host . " : Expires in " .$certexpires." days"."\n";
                        if($certexpires <= $nomorethan){
                                $host_details['host'] = $host;
                                $host_details['days'] = $certexpires;
                                $host_details['issuer'] = $certinfo['issuer']['CN'];
                                $host_details['ip'] = $certinfo['ip'];
                                $expiring_hosts[count($expiring_hosts)] = $host_details;
                                echo "Warning! - $host is expiring soon!";
                        }
                }
        }
}

// If we have expiring hosts
if(count($expiring_hosts)){

        $message = "Warning!<br><br>There are currently ".count($expiring_hosts)." domains in the environment with soon to expire ssl certificates. More details below:<br><br><table><thead><tr><th>Id</th><th>Host</th><th>Expires in (Days)</th><th>Issuer</th></tr></thead><tbody>";

        for($i=0;$i<count($expiring_hosts);$i++){
                $message .= "<td>".($i+1)."</td><td style='text-weight: bold;'>".$expiring_hosts[$i]['host']." (".$expiring_hosts[$i]['ip'].")</td><td>".$expiring_hosts[$i]['days']."</td><td>".$expiring_hosts[$i]['issuer']."</td></tr>";
        }

        $message .= "</tbody></table><br><br>-<br>ReportBot<br><i>This is an automated message</i>";
        $message .= "<style>tbody.tr{border: solid 1px black;} .theader{ font-weight: bold; } tbody.tr:nth-child(even) {background-color: #f2f2f2;} th { background-color: black; color: white; } th, td { padding: 15px; text-align: left; } table, th, td { border: 1px solid black; } </style>";
        notifyDevOps($message);

}

function notifyDevOps($message){
        require("/var/www/html/api/mailgun/mailgun.php");
        $users = "<toaddy>";
        sendmailbymailgun($users,"DevOps","DevOps Automated Reporting","<fromaddy>","Soon to expire SSL certificates.",$message,$message,"","<replytoaddy>","","");
}

function checkSSL($url){
        global $today;
        $original_url = $url;
        $url = "https://$url";
        $orignal_parse = parse_url($url, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
        $domain = explode(":",$original_url)[0];
        $certinfo['ip'] = gethostbyname($domain);
        return $certinfo;
}

function secondsToDays($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a');
}
