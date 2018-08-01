<?php
// aes.php
// Simple CLI powered script to encode / decode AES-256 content
// Example usage: aes.php -d "encryptedstring" "secret" >> outputs descrypted string
// Ron Egli - https://github.com/smugzombie

function encryptAES($content, $secret){
        return openssl_encrypt($content, "AES-256-CBC", $secret);
}
function decryptAES($content, $secret){
        return openssl_decrypt($content, "AES-256-CBC", $secret);
}

$action = $argv[1];
$string = $argv[2];
$secret = $argv[3];

if($action == "-d" || $action == "--decrypt"){
        echo decryptAES($string,$secret);
}elseif($action == "-e" || $action == "--encode"){
        echo encryptAES($string,$secret);
}
