<?php

namespace App\Custom;

class Formatter
{
    public static function apiResponse(int $code = 200, string $message = "no message", mixed $data = null, mixed $error = null)
    {
        $success = is_numeric($code) && $code > 199 && $code < 300;
        return response()->json([
            "success" => $success,
            "message" => $message,
            "data" => $data,
            "error" => $error
        ], $code);
    }

    public static function removeVowel(string $s)
    {
        $vowels = ["A","I","U","E","O","a","i","u","e","o"];
        return str_replace($vowels, "", $s);
    }

    public function makeDash(string $s)
    {
        return str_replace(" ", "-", $s);
    }
}
