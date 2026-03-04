<?php

namespace App\Utils;

class TypeVariable
{
    const CURRENTCASE = 0;
    const LOWERCASE = 1;
    const UPPERCASE = 2;
    const CARACTERE = ["#", "'", ";", "-", "_", "/", " ", ",", "<", ">", "!", "|", "(", ")", "@", "$"];

    public function __construct() {}

    public function text($value)
    {
        return (string)htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, "UTF-8");
    }

    public function integer($value)
    {
        return (int)$value;
    }

    public function float($value)
    {
        return (float)$value;
    }

    public function date($value)
    {
        try {
            return new \DateTime($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function trim($value)
    {
        return (string)rtrim(ltrim($value));
    }

    public function not_carractere($value)
    {
        return (string)htmlspecialchars(ltrim(rtrim(str_replace(TypeVariable::CARACTERE, "", $value))));
    }

    public static function is_not_null($value): bool {
        if($value === 'undefined' or $value === null or $value === 'null' or $value === '' or $value === []) {
            return false;
        }
        return true;
    }
    public static function is_null($value): bool {
        if($value === 'undefined' or $value === null or $value === 'null' or $value === '' or $value === []) {
            return true;
        }
        return false;
    }

    public static function slugify(string $text = '', string $replaceBy = '_', int $case = 0)
    {
        $text = preg_replace('~[^\pL\d]+~u', $replaceBy, $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', $replaceBy, $text);
        $text = preg_replace('/\./', $replaceBy, $text);
        $text = preg_replace('/\s+/', $replaceBy, $text);
        if ($case === TypeVariable::UPPERCASE) {
            $text = strtoupper($text);
        }
        if ($case === TypeVariable::LOWERCASE) {
            $text = strtolower($text);
        }
        if (empty($text)) {
            return uniqid('', false);
        }
        return $text;
    }

    public static function htmlDecode($str) {
        $ret = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
        $p2 = -1;
        for(;;) {
            $p = strpos($ret, '&#', $p2+1);
            if ($p === FALSE)
                break;
            $p2 = strpos($ret, ';', $p);
            if ($p2 === FALSE)
                break;
               
            if (substr($ret, $p+2, 1) == 'x')
                $char = hexdec(substr($ret, $p+3, $p2-$p-3));
            else
                $char = intval(substr($ret, $p+2, $p2-$p-2));
               
            //echo "$char\n";
            $newchar = iconv(
                'UCS-4', 'UTF-8',
                chr(($char>>24)&0xFF).chr(($char>>16)&0xFF).chr(($char>>8)&0xFF).chr($char&0xFF)
            );
            //echo "$newchar<$p<$p2<<\n";
            $ret = substr_replace($ret, $newchar, $p, 1+$p2-$p);
            $p2 = $p + strlen($newchar);
        }
        return $ret;
    }
}
