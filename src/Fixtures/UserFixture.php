<?php

namespace App\Fixtures;

use App\Entity\Extra\Role;
use App\Entity\Admin\User;
use App\Entity\Admin\Admin;
use App\Helpers\RouteHelper;
use Doctrine\Persistence\ObjectManager;
use App\Repository\Admin\UserRepository;
use App\Repository\Extra\PathRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    private $passwordEncoder;
    private $userRepository;
    private $pathRepository;
    private $countryRepository;
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        PathRepository $pathRepository,
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->pathRepository = $pathRepository;
    }

    public function load(ObjectManager $manager)
    {
        $check = $this->userRepository->findOneBy(['isFirst' => true]);
        $pathsRow = $this->pathRepository->findAll();
        // $country = $this->countryRepository->findOneByIsoCode3('CIV');
        // if (!$check && $country) {   
            if (!$check) {  
            //ROLE
            $role = new Role();
            $role
                ->setNom('Accès super administrateur')
                ->setDescription('Accès super administrateur')
                ->setCreatedAt(new \DateTime('now'))
                ->setIsFirst(true)
            ;
            $paths = RouteHelper::ADMIN_ROUTE($pathsRow);
            foreach ($paths as $path) {
                $path->addRole($role);
                $manager->persist($path);
            }
            $manager->persist($role);

            //ADMIN
            $admin = new Admin();
            $admin
                ->setNom('Cisse')
                ->setPrenom('Ibrahim')
                ->setTelephone('2250555568405')
                ->setCreatedAt(new \DateTime('now'))
            ;
            $manager->persist($admin);
    
            //USER
            $user = new User();                 
            $user
                ->setUsername('cisseibrahim1995@gmail.com')
                ->setPassword($this->passwordEncoder->encodePassword($user, 'ibrahim@55'))
                ->setEmail('cisseibrahim1995@gmail.com')
                ->setIsFirst(true)
                ->setType(User::TYPE['ADMIN'])
                ->setAdmin($admin)
                ->setIsEnabled(true)
                ->setCreatedAt(new \DateTime('now'))
            ;
            $manager->persist($user);

            $role->addUser($user);
            $manager->persist($role);

           
            $manager->flush();
        }
    }

   
}