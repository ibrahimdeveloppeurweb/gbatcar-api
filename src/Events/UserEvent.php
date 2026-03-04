<?php

namespace App\Events;

use App\Entity\Admin\User;
use App\Entity\Extra\SettingSms;
use App\Entity\Extra\SettingMail;
use App\Entity\Extra\SettingTemplate;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserEvent
{
    /** @var User */
    private $user;
    private $token;
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->getUser();
        $entity = $args->getObject();
        if ($this->user instanceof UserInterface && method_exists($entity, 'setCreateBy')) {
            $entity->setCreateBy($this->user);
        }
        if ($this->user instanceof UserInterface && method_exists($entity, 'setValidateBy') && method_exists($entity, 'setEtat')) {
            if (
                $entity->getEtat() === 'ACTIF' ||
                $entity->getEtat() === 'VALIDE' ||
                $entity->getEtat() === 'DECAISSER'
            ) {
                $entity->setValidateAt(new \DateTime('now'));
                $entity->setValidateBy($this->user);
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->getUser();
        $entity = $args->getObject();
        if ($this->user instanceof UserInterface && method_exists($entity, 'setUpdateBy')) {
            $entity->setUpdateBy($this->user);
        }
        if ($this->user instanceof UserInterface && method_exists($entity, 'setValidateBy') && method_exists($entity, 'setEtat')) {
            if (
                $entity->getEtat() === 'ACTIF' ||
                $entity->getEtat() === 'VALIDE' ||
                $entity->getEtat() === 'DECAISSER'
            ) {
                $entity->setValidateAt(new \DateTime('now'));
                $entity->setValidateBy($this->user);
            }
        }
        // if(
        //     $entity instanceof SettingMail || 
        //     $entity instanceof SettingSms || 
        //     $entity instanceof SettingTemplate
        // ){ return; }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->getUser();
        $entity = $args->getObject();
        if ($this->user instanceof UserInterface && method_exists($entity, 'setRemoveBy')) {
            $entity->setRemoveBy($this->user);
        }
    }

    public function getUser()
    {
        if ($this->token->getToken()) {
            $this->user = $this->token->getToken()->getUser();
        }
        return $this->user;
    }

}
