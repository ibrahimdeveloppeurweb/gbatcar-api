<?php

namespace App\Events;

use App\Entity\Admin\User;

use Doctrine\Persistence\Event\LifecycleEventArgs;

class CodeGenerateEvent
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (method_exists($entity, 'setCode')) {
            if (!$this->exclus($entity)) {
                $code = "";
                // Generate a random 4 digit code prefix
                $codePrefix = 'GB-' . $this->aleatoire(4, 'C');
                $code .= $codePrefix . '-' . $this->aleatoire(2, 'C') . $this->aleatoire(2, 'C') . '-01';

                ($entity->getCode()) ?? $entity->setCode($code);
            }
        }
    }


    public function aleatoire($taille, $type = null)
    {
        $mdp = '';
        $cars = '';
        if ($type === 'C') {
            $cars = "6789012345";
        }
        elseif ($type === 'L') {
            $cars = "IOPQSDFGAZERTYHJKLMWXCVBN6789012345";
        }
        elseif ($type === null) {
            $cars = "IOPQSDFGAZERTYHJKLMWXCVBN6789012345";
        }
        srand((double)microtime() * 1000000);
        for ($i = 0; $i < $taille; $i++)
            $mdp = $mdp . substr($cars, rand(0, strlen($cars) - 1), 1);
        return $mdp;
    }

    public function exclus($entity)
    {
        if (
        $entity instanceof User
        // $entity instanceof Setting ||
        // $entity instanceof SettingMail ||
        // $entity instanceof SettingSms ||
        // $entity instanceof SettingTemplate ||
        // $entity instanceof Role ||
        // $entity instanceof Country
        ) {
            return $entity;
        }
    }
}