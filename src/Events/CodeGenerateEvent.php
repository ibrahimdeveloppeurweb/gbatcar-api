<?php

namespace App\Events;

use App\Entity\Extra\Role;
use App\Entity\Admin\User;
use App\Entity\Admin\Agency;
use App\Entity\Extra\Country;
use App\Entity\Extra\Setting;
use App\Entity\Extra\SettingSms;
use App\Entity\Extra\SettingMail;
use App\Entity\Extra\SettingTemplate;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CodeGenerateEvent
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if(method_exists($entity,'setCode')){
            if ($entity instanceof Agency) {
                $this->agence($entity, 'ZA-'.$this->aleatoire(4, 'C'));
            }

            if(method_exists($entity,'getAgency') && !$this->exclus($entity)) {
                $code = "";
                // 1- On recupere le code de l'agence lié à cette entité
                //Dans le cas ou l'agence n'est pas encore de code
                if ($entity->getAgency() instanceof Agency && null === $entity->getAgency()->getId()) {
                    $codeAgence = 'ZA-'.$this->aleatoire(4, 'C');                
                    // 2- Genérer un code de 4 chiffres alléatoires
                    $code .= $codeAgence.'-'.$this->aleatoire(2, 'C').$this->aleatoire(2, 'C').'-01';
                } elseif ($entity->getAgency() instanceof Agency && null !== $entity->getAgency()->getId()) {
                    // 1- Genérer un code de 4 chiffres alléatoires
                    $aleatoire = $this->aleatoire(2, 'C').$this->aleatoire(2, 'C');
                    $code .= $entity->getAgency()->getCode().'-'.$aleatoire.'-01';
                }
                
                ($entity->getCode()) ?? $entity->setCode($code);
            }
        }
    }

    public function agence($entity, $genere)
    {
        $code = null;        
        $code .= $genere;
        return $entity->setCode($code);
    }

    public function aleatoire($taille , $type = null)
    {
        $mdp = '';
        $cars = '';
        if ($type === 'C') {
            $cars = "6789012345";
        } elseif ($type === 'L') {
            $cars = "IOPQSDFGAZERTYHJKLMWXCVBN6789012345";
        } elseif ($type === null) {
            $cars = "IOPQSDFGAZERTYHJKLMWXCVBN6789012345";
        }
        srand((double)microtime()*1000000); 
        for($i=0;$i<$taille;$i++)$mdp=$mdp.substr($cars,rand(0,strlen($cars)-1),1);
        return $mdp;
    }

    public function exclus($entity) {
        if(
            $entity instanceof User 
            // $entity instanceof Setting ||
            // $entity instanceof SettingMail ||
            // $entity instanceof SettingSms ||
            // $entity instanceof SettingTemplate ||
            // $entity instanceof Role ||
            // $entity instanceof Country
        ){
            return $entity;
        }
    }
}
