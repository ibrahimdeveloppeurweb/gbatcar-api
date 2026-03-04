<?php

namespace App\Helpers;

class ContactHelper
{   
    public static function INDICATIF($contact)
    {
        return substr($contact, 0, 4); 
    }

    public static function FORMAT($contact)
    {
        $espaces = [' ', "\t"];
        return str_replace($espaces, "", $contact); 
    }
}
