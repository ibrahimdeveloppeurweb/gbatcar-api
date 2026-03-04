<?php

namespace App\Utils;

class FonctionUtil
{
    /**
     * Generer un mot de passe aleatoire en fonction de caratere voulu
     * @param integer $longueur
     * @return string
     */
    public static function password(int $longueur): string
    {
        $listeCar = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789/[!@#$%^&*()_+-=\]{};:"|,.<>?';
        $chaine = '';
        $max = mb_strlen($listeCar, '8bit') - 1;
        for ($i = 0; $i < $longueur; ++$i) {
            $chaine .= $listeCar[random_int(0, $max)];
        }
        return $chaine;
    }

    public static function aleatoire($taille, $type = null)
    {
        $mdp = '';
        $cars = '';
        if ($type === 'C') {
            $cars = "abcdefghijklmnopqrstuvwxyz6789012345";
        } elseif ($type === 'L') {
            $cars = "IOPQSDFGAZERTYHJKLMWXCVBN6789012345";
        } elseif ($type === null) {
            $cars = "IOPQSDFGAZERTYHJKLMWXCVBN6789012345";
        }
        srand((float)microtime() * 1000000);
        for ($i = 0; $i < $taille; $i++) $mdp = $mdp . substr($cars, rand(0, strlen($cars) - 1), 1);
        return $mdp;
    }
}