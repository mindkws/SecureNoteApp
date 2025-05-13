<?php
function encryptAES($data, $key, $iv) {
    return openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
}

function decryptAES($data, $key, $iv) {
    return openssl_decrypt($data, 'aes-256-cbc', $key, 0, $iv);
}
?>
