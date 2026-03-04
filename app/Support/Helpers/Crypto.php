<?php

namespace App\Support\Helpers;

use Illuminate\Support\Facades\Crypt;

class Crypto
{
    public static function encrypt(string $plain): string
    {
        return Crypt::encryptString($plain);
    }

    public static function decrypt(string $cipher): string
    {
        return Crypt::decryptString($cipher);
    }
}