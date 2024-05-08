<?php
declare(strict_types=1);
function ___generateUniqueString($length){
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($permitted_chars), 0, $length);
}
function ___encryption_sodium(string $message, string $key): string
{
    $nonce = random_bytes(5);
    $cipher = base64_encode(
        $nonce.
        sodium_crypto_secretbox(
            $message,
            $nonce,
            $key
        )
    );
    sodium_memzero($message);
    sodium_memzero($key);
    return $cipher;
}

function ___decryption_sodium(string $encrypted, string $key): string
{   
    $decoded = base64_decode($encrypted);
    $nonce = mb_substr($decoded, 0, 5, '8bit');
    $ciphertext = mb_substr($decoded, 5, null, '8bit');
    
    $plain = sodium_crypto_secretbox_open(
        $ciphertext,
        $nonce,
        $key
    );
    if (!is_string($plain)) {
        throw new Exception('Invalid MAC');
    }
    sodium_memzero($ciphertext);
    sodium_memzero($key);
    return $plain;
}

function ___encryption_openssl($string){
    if(preg_match('/pro_/', $string)==1) $string = str_replace('pro_', '', $string);
    if(preg_match('/_tbl/', $string)==1) $string = str_replace('_tbl', '', $string);
    $ciphering = "AES-128-CTR";   
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;
    $encryption_iv = '1894465891099921';
    $encryption_key = "CaseManagerSalt";
    return openssl_encrypt($string, $ciphering, $encryption_key, $options, $encryption_iv);
}

function ___decryption_openssl($string){
    $ciphering = "AES-128-CTR"; 
    $options = 0;
    $decryption_iv = '1894465891099921';
    $decryption_key = "CaseManagerSalt";
    return openssl_decrypt ($string, $ciphering, $decryption_key, $options, $decryption_iv);
}

?>