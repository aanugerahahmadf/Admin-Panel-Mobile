<?php

require __DIR__.'/../vendor/autoload.php';

// Fix for PHP 8.4+ notices and deprecations during testing
if (PHP_VERSION_ID >= 80400) {
    set_error_handler(function ($errno, $errstr) {
        // Suppress PHP 8.4 tempnam() notice in Laravel AliasLoader
        if ($errno === E_NOTICE && str_contains($errstr, 'tempnam()')) {
            return true;
        }

        // Suppress PHP 8.5 deprecation warning for PDO::MYSQL_ATTR_SSL_CA
        if ($errno === E_DEPRECATED && str_contains($errstr, 'PDO::MYSQL_ATTR_SSL_CA is deprecated')) {
            return true;
        }

        return false;
    }, E_NOTICE | E_DEPRECATED);
}
