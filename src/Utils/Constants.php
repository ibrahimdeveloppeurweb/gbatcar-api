<?php

namespace App\Utils;

class Constants
{
    const EQUIPEMENT = [
       'Porte',
       'Fenêtre',
       'Etanchéité',
       'Sol',
       'Mur',
       'Prise',
       'Éclairage',
       'Sanitaire',
       'Serrure',
       'Poignet',
       'WC',
       'Mirroire',
       'Chauffe eau'
    ];

    const PERIODICITE = [
        'MENSUEL' => 1,
        'TRIMESTRIEL' => 3,
        'SEMESTRIEL' => 6,
        'ANNUEL' => 12
    ];
    
    const SERVICES = [
        'Service commercial',
        'Service recouvrement',
        'Service comptabilité / trésorerie'
    ];

    const CAISSE = ['Caisse principale'];

    const USER_ROLES = [
        'ADMIN' => 'ROLE_USER_ADMIN', 
        'AGENCY' => 'ROLE_USER_AGENCY',
        'SITE' => 'ROLE_USER_AGENCY',
        'MARCHAND' => 'ROLE_USER_MARCHAND',
        'CUSTOMER' => 'ROLE_USER_CUSTOMER',
        'TENANT' => 'ROLE_USER_TENANT',
        'PROSPECT' => 'ROLE_USER_PROSPECT',
        'AGENT' => 'ROLE_USER_AGENT',
        'RECOUVREUR' => 'ROLE_USER_RECOUVREUR'
    ];

    const COUNTRIES = [ 
        "AD" => "999 999",
        "AE" => "999 999 9999",
        "AF" => "999 999 9999",
        "AG" => "999-9999",
        "AI" => "999-9999",
        "AL" => "999 999 9999",
        "AM" => "999 999999",
        "AN" => "999 9999",
        "AO" => "999 999 999",
        "AQ" => "9 99999",
        "AR" => "999 99-9999-9999",
        "AS" => "999-9999",
        "AT" => "9999 999999",
        "AU" => "9999 999 999",
        "AW" => "999 9999",
        "AZ" => "999 999 99 99",
        "BA" => "999 999 999",
        "BB" => "999-9999",
        "BD" => "99999-999999",
        "BE" => "9999 99 99 99",
        "BF" => "99 99 99 99",
        "BG" => "999 999 999",
        "BH" => "9999 9999",
        "BI" => "99 99 99 99",
        "BJ" => "99 99 99 99",
        "BL" => "9999 99 99 99",
        "BM" => "999-9999",
        "BN" => "999 9999",
        "BO" => "99999999",
        "BR" => "(99) 99999-9999",
        "BS" => "999-9999",
        "BT" => "99 99 99 99",
        "BW" => "99 999 999",
        "BY" => "9 999 999-99-99",
        "BZ" => "999-9999",
        "CA" => "(999) 999-9999",
        "CC" => "9999 999 999",
        "CD" => "9999 999 999",
        "CF" => "99 99 99 99",
        "CG" => " 99 999 9999",
        "CH" => "999 999 99 99",
        "CI" => "99 99 99 99",
        "CK" => "99 9999",
        "CL" => "9 9999 9999",
        "CM" => "9 99 99 99 99",
        "CN" => "999 9999 9999",
        "CO" => "999 9999999",
        "CR" => "9999 9999",
        "CU" => "99 9999999",
        "CV" => "999 99 99",
        "CW" => "999 9999",
        "CX" => "9999 999 999",
        "CY" => "99 999999",
        "CZ" => "999 999 999",
        "DE" => "99999 9999999",
        "DJ" => "99 99 99 99",
        "DK" => "99 99 99 99",
        "DM" => "999-9999",
        "DO" => "999-9999",
        "DZ" => "9999 99 99 99",
        "EC" => "999 999 9999",
        "EE" => "9999 9999",
        "EG" => "9999 999 9999",
        "EH" => "9999-999999",
        "ER" => "99 999 999",
        "ES" => "999 99 99 99",
        "ET" => "999 999 9999",
        "FI" => "999 9999999",
        "FJ" => "999 9999",
        "FK" => "99999",
        "FM" => "999 9999",
        "FO" => "999999",
        "FR" => "99 99 99 99 99",
        "GA" => "99 99 99 99",
        "GB" => "99999 99999",
        "GD" => "999-9999",
        "GE" => "999 99 99 99",
        "GG" => "99999 999999",
        "GH" => "999 999 9999",
        "GI" => "99999999",
        "GL" => "99 99 99",
        "GM" => "999 9999",
        "GN" => "999 99 99 99",
        "GQ" => "999 999 999",
        "GR" => "999 999 9999",
        "GT" => "9999 9999",
        "GU" =>  "999-9999",
        "GW" => "999 999 999",
        "GY" => "999 9999",
        "HK" => "9999 9999",
        "HN" => "9999-9999",
        "HR" => "999 999 9999",
        "HT" => "99 99 9999",
        "HU" => "(99) 999 9999",
        "ID" => "9999-999-999",
        "IE" => "999 999 9999",
        "IL" => "999-999-9999",
        "IM" => "99999 999999",
        "IN" => "99999 99999",
        "IO" => "999 9999",
        "IQ" => "9999 999 9999",
        "IR" => "9999 999 9999",
        "IS" => "999 9999",
        "IT" => "999 999 9999",
        "JE" => "99999 999999",
        "JM" => "999-9999",
        "JO" => "99 9999 9999",
        "JP" => "999 9999-9999",
        "KE" => "9999 999999",
        "KG" => "9999 999 999",
        "KH" => "999 999 999",
        "KI" => "99999999",
        "KM" => "999 99 99",
        "KN" => "999-9999",
        "KP" => "9999 999 9999",
        "KR" => "999-9999-9999",
        "KW" => "999 99999",
        "KY" => "999-9999",
        "KZ" => "9 (999) 999 9999",
        "LA" => "999 99 999 999",
        "LB" => "99 999 999",
        "LC" => "999-9999",
        "LI" => "999 999 999",
        "LK" => "999 999 9999",
        "LR" => "999 999 9999",
        "LS" => "9999 9999",
        "LT" => "(9-999) 99999",
        "LU" => "999 999 999",
        "LV" => "99 999 999",
        "LY" => "999-9999999",
        "MA" => "9999-999999",
        "MC" => "99 99 99 99 99",
        "MD" => "9999 99 999",
        "ME" => "999 999 999",
        "MF" => "9999 99 99 99",
        "MG" => "999 99 999 99",
        "MH" => "999-9999",
        "MK" => "999 999 999",
        "ML" => "99 99 99 99",
        "MM" => "99 999 9999",
        "MN" => "9999 9999",
        "MO" => "9999 9999",
        "MP" => "999-9999",
        "MR" => "99 99 99 99",
        "MS" => "999-9999",
        "MT" => "9999 9999",
        "MU" => "9999 9999",
        "MV" => "999-9999",
        "MW" => "9999 99 99 99",
        "MX" => "999 999 999 9999",
        "MY" => "999-999 9999",
        "MZ" => "99 999 9999",
        "NA" => "999 999 9999",
        "NC" => "99 99 99",
        "NE" => "99 99 99 99",
        "NG" => "9999 999 9999",
        "NI" => "9999 9999",
        "NL" => "99 99999999",
        "NO" => "999 99 999",
        "NP" => "999-9999999",
        "NR" => "999 9999",
        "NU" => "999 9999",
        "NZ" => "999 999 9999",
        "OM" => "9999 9999",
        "PA" => "9999-9999",
        "PE" => "999 999 999",
        "PF" => "99 99 99 99",
        "PG" => "9999 9999",
        "PH" => "9999 999 9999",
        "PK" => "9999 9999999",
        "PL" => "999 999 999",
        "PM" => "999 99 99",
        "PN" => "99 999999",
        "PR" => "999-9999",
        "PS" => "9999 999 999",
        "PT" => "999 999 999",
        "PW" => "999 9999",
        "PY" => "9999 999999",
        "QA" => "9999 9999",
        "RE" => "9999 99 99 99",
        "RO" => "9999 999 999",
        "RS" => "999 9999999",
        "RU" => "9 (999) 999-99-99",
        "RW" => "9999 999 999",
        "SA" => "999 999 9999",
        "SB" => "99 99999",
        "SC" => "9 999 999",
        "SD" => "999 999 9999",
        "SE" => "999-999 99 99",
        "SG" => "9999 9999",
        "SH" => "99999",
        "SI" => "999 999 999",
        "SJ" => "999 99 999",
        "SK" => "9999 999 999",
        "SL" => "(999) 999999",
        "SM" => "99 99 99 99",
        "SN" => "99 999 99 99",
        "SO" => "9 9999999",
        "SR" => "999-9999",
        "SS" => "9999 999 999",
        "ST" => "999 9999",
        "SV" => "9999 9999",
        "SX" => "999-9999",
        "SY" => "9999 999 999",
        "SZ" => "9999 9999",
        "TC" => "999-9999",
        "TD" => "99 99 99 99",
        "TG" => "99 99 99 99",
        "TH" => "999 999 9999",
        "TJ" => "999 99 9999",
        "TK" => "9999",
        "TL" => "9999 9999",
        "TM" => "9 99 999999",
        "TN" => "99 999 999",
        "TO" => "999 9999",
        "TR" => "9999 999 99 99",
        "TT" => "999-9999",
        "TV" => "999999",
        "TW" => "9999 999 999",
        "TZ" => "9999 999 999",
        "UA" => "999 999 9999",
        "UG" => "9999 999999",
        "US" => "(999) 999-9999",
        "UY" => "999 999 999",
        "UZ" => "9 99 999 99 99",
        "VA" => "999 999 9999",
        "VC" => "999-9999",
        "VE" => "9999-9999999",
        "VG" => "999-9999",
        "VI" => "999-9999",
        "VN" => "999 999 99 99",
        "VU" => "999 9999",
        "WF" => "99 99 99",
        "WS" => "99 99999",
        "XK" => "999 999 999",
        "YE" => "9999 999 999",
        "YT" => "9999 99 99 99",
        "ZA" => "999 999 9999",
        "ZM" => "999 9999999",
        "ZW" => "999 999 9999"
    ];

    /**
     * Recupere les prochaines mois eb fonction de la periodicite (Cadence) et du mois referent
     * @param string $periodicite
     * @param string $mois
     * @return array
     */
    public function mois(string $periodicite, string $mois)
    {
        $tab = [];
        //On retire un mois sur le mois courant afin de pourvoir generer les mois suivants
        //En prenant en compte ce mois
        $month = (new \DateTime($mois))->modify('-1 month');
        for ($i=1; $i <= Constants::PERIODICITE[$periodicite]; $i++) {
            $month = $month->modify('+1 month');
            $ani = [];
            $ani[] = $month;
            $tab = $ani;
        }
        return $tab;
    }

    static function END(int $index, int $taille): string {
        return ($index === $taille) ? "" : " / ";
    }

    /**
     * Fonction d'arrondi
     *
     * @param $value Il s'agit de la valeur à arrondir
     * @param $arrondi Il s'agit du niveau d'arrondi (0,05 / 0,10 / 0,20 / ...) --> Valeur utilisées : 5 / 10 / 20 / 50 (je n'ai pas testé d'autres valeurs)
     * @param $roundType Il s'agit du type d'arrondi (arrondi à la valeur la plus proche ou à la valeur suppérieure) - Valeurs possibles : "round" / "ceil"
     * @param $precision 1(par défaut) pour les dizaines, 2 pour les centaines, 3 pour millier - En utilisant la valeur "1" c'est good!
     * @return unknown_type Retourne la valeur de la décimale une fois arrondie
    */
    static function decRound($value, $arrondi, $roundType, $precision = 1)
    {
        $p = pow ($arrondi, $precision);
        return $roundType ($value / $p) * $p;
    }
}
