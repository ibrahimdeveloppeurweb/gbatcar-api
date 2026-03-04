<?php
namespace App\Security;

use App\Repository\Admin\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserDBProvider implements UserProviderInterface
{
    private $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function loadUserByUsername($username)
    {
        try{
            $user = $this->userRepository->findOneByUsername($username);
        }catch (NonUniqueResultException $exception) {
            $user = null;
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        error_log('roles : '.json_encode($this->loadUserByUsername($user->getUsername())->getRoles()));
        return $this->loadUserByUsername($user->getUsername());

    }

    public function supportsClass($class)
    {
        return $class === 'App\Entity\Admin\User';
    }
}
