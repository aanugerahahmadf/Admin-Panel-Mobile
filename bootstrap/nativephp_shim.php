<?php

/**
 * NativePHP Development Shim.
 *
 * This file provides a stub for the global `nativephp_call()` function used
 * by the NativePHP Mobile client when running in a non-native environment
 * (e.g. local web server on Windows/Linux/Mac). It is only loaded when the
 * function does not already exist, so it is completely safe to include.
 */

use Illuminate\Support\Facades\Log;

if (! function_exists('nativephp_call')) {
    /**
     * @param  string  $params  JSON-encoded parameter string
     * @return string JSON-encoded response
     */
    function nativephp_call(string $method, string $params): string
    {
        Log::info("Native call (Shim): {$method}", (array) json_decode($params, true));

        return json_encode(['status' => 'success']);
    }
}
