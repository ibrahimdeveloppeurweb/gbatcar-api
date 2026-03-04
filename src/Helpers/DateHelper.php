<?php

namespace App\Helpers;

class DateHelper
{
    const MONTH_MED_FR = [
        'Jan' => 'janvier',
        'Feb' => 'février',
        'Mar' => 'mars',
        'Apr' => 'avril',
        'May' => 'mai',
        'Jun' => 'juin',
        'Jul' => 'juillet',
        'Aug' => 'août',
        'Sep' => 'septembre',
        'Oct' => 'octobre',
        'Nov' => 'novembre',
        'Dec' => 'decembre'
    ];

    const MONTH_UPP_FR = [
        'January' => 'Janvier',
        'February' => 'Février',
        'March' => 'Mars',
        'April' => 'Avril',
        'May' => 'Mai',
        'June' => 'Juin',
        'July' => 'Juillet',
        'August' => 'Août',
        'September' => 'Septembre',
        'October' => 'Octobre',
        'November' => 'Novembre',
        'December' => 'Décembre'
    ];

    const MONTH_SM_FR = [
        'January' => 'janv',
        'February' => 'fév',
        'March' => 'mars',
        'April' => 'avril',
        'May' => 'mai',
        'June' => 'juin',
        'July' => 'juil',
        'August' => 'août',
        'September' => 'sept',
        'October' => 'oct',
        'November' => 'nov',
        'December' => 'déc'
    ];

    const MONTH_FR = [
        'January' => 'janvier',
        'February' => 'février',
        'March' => 'mars',
        'April' => 'avril',
        'May' => 'mai',
        'June' => 'juin',
        'July' => 'juillet',
        'August' => 'août',
        'September' => 'septembre',
        'October' => 'octobre',
        'November' => 'novembre',
        'December' => 'décembre'
    ];

    public static function YEAR($date)
    {                       
        return $date->format("Y"); 
    }

    public static function MONTH_YEAR($date)
    {                       
        return DateHelper::MONTH_FR[$date->format("F")] .' '.$date->format('Y'); 
    }

    public static function DAY($date)
    {                       
        return $date->format("d"); 
    }
}
