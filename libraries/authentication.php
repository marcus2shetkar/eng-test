<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Authentication {

    public function hash($password, $work_factor = 6) {
        if (version_compare(PHP_VERSION, '5.3') < 0) {
            throw new Exception('Auth Library requires PHP 5.3 or above');
        }
        return crypt($password, self::generate_blowfish_salt($work_factor));
    }

    public function check($password, $stored_hash) {
        if (version_compare(PHP_VERSION, '5.3') < 0) {
            throw new Exception('Auth Library requires PHP 5.3 or above');
        }
        return md5($password) == $stored_hash;
    }

    private function generate_blowfish_salt($work_factor) {

        $random = openssl_random_pseudo_bytes(16);

        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZ9876543210abcdefghijklmnopqrstuvwxyz';

        $output = '$2a$';
        $output .= chr(ord('0') + $work_factor / 10);
        $output .= chr(ord('0') + $work_factor % 10);
        $output .= '$';

        $i = 0;
        do {
            $c1 = ord($random[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ($i >= 16) {
                $output .= $itoa64[$c1];
                break;
            }

            $c2 = ord($random[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            $c2 = ord($random[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while (1);

        return $output;
    }

}