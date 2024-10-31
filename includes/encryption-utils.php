<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function rocket_validator_encrypt_data($data) {
    $salt = wp_salt('auth');
    $key = hash('sha256', $salt, true);
    
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    
    return base64_encode($encrypted . '::' . $iv);
}

function rocket_validator_decrypt_data($encrypted_data) {
    $salt = wp_salt('auth');
    $key = hash('sha256', $salt, true);
    
    list($encrypted, $iv) = explode('::', base64_decode($encrypted_data), 2);
    
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

?>