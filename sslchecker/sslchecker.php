<?php
# Allows you to setup a cron job to verify certificates in your environment are fixed before expiry
# Ron Egli - SSLChecker.php
# github.com/smugzombie

$today = time(); // NOW's time
$nomorethan = 30; //Days allowed from expiry

// Load hosts from file
$hosts = explode("\n",file_get_contents("hosts.txt"));
$expiring_hosts = array();

for($i=0;$i<count($hosts); $i ++){
        $host = $hosts[$i];
        if($host){
                $days = checkSSL($host);
                echo $host . " : Expires in " .checkSSL($host)." days"."\n";

                if($days <= $nomorethan){
                        $host_details['host'] = $host;
                        $host_details['days'] = $days;
                        $expiring_hosts[count($expiring_hosts)] = $host_details;
                        echo "Warning! - $host is expiring soon!";
                }
        }
}

// If we have expiring hosts
if(count($expiring_hosts)){

        $message = "Warning!<br><br>There are currently ".count($expiring_hosts)." domains in the environment with soon to expire ssl certificates. More details below:<br><br><table><thead><tr><th>Id</th><th>Host</th><th>Expires in (Days)</th></tr></thead><tbody>";

        for($i=0;$i<count($expiring_hosts);$i++){
                $message .= "<td>".($i+1)."</td><td style='text-weight: bold;'>".$expiring_hosts[$i]['host']."</td><td>".$expiring_hosts[$i]['days']."</td></tr>";
        }

        $message .= "</tbody></table><br><br>-<br>ReportBot<br><i>This is an automated message</i>";
        $message .= "<style>tbody.tr{border: solid 1px black;} .theader{ font-weight: bold; } tbody.tr:nth-child(even) {background-color: #f2f2f2;} th { background-color: black; color: white; } th, td { padding: 15px; text-align: left; } table, th, td { border: 1px solid black; } </style>";
        notifyDevOps($message);
}

function notifyDevOps($message){
        require("/var/www/html/api/sendmailgun.php"); //github.com/smugzombie/PHP/mailgun/
        $users = "<youremail(s)here>";
        sendmailbymailgun($users,"DevOps","DevOps Automated Reporting","<From Address>","Soon to expire SSL certificates.",$message,$message,"","","","");
}

function checkSSL($url){
        global $today;
        $url = "https://$url";
        $orignal_parse = parse_url($url, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
        $certexpiry = $certinfo['validTo_time_t'];
        $certexpires = (($certexpiry - $today));
        return secondsToDays($certexpires);
}

function secondsToDays($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a');
}
