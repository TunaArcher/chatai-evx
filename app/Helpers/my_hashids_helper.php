<?php

use Hashids\Hashids;

if( ! function_exists('hashidsEncrypt'))
{
    function hashidsEncrypt($input)
    {
        $hashids = new Hashids(getenv('CLIENT_SECRET_KEY'));

        return $hashids->encode("$input");
    }
}

if( ! function_exists('hashidsDecrypt'))
{
    function hashidsDecrypt($input)
    {
        $hashids = new Hashids(getenv('CLIENT_SECRET_KEY'));

        return $hashids->decode("$input")[0];
    }
}