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

/**
 * Fixture de création du Super Administrateur GbatCar.
 * Cette fixture est idempotente : elle ne crée l'utilisateur que s'il n'existe pas déjà.
 *
 * Commande : php bin/console doctrine:fixtures:load --append
 */
class UserFixture extends Fixture
{
    private $passwordEncoder;
    private $userRepository;
    private $pathRepository;

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

    public function load(ObjectManager $manager): void
    {
        // Idempotence : ne crée le super admin que s'il n'existe pas encore
        $check = $this->userRepository->findOneBy(['isFirst' => true]);
        if ($check) {
            return;
        }

        $pathsRow = $this->pathRepository->findAll();

        // ── 1. Création du Rôle Super Administrateur ──────────────────────────
        $role = new Role();
        $role
            ->setNom('Super Administrateur GbatCar')
            ->setDescription('Accès complet à toutes les fonctionnalités de la plateforme GbatCar')
            ->setCreatedAt(new \DateTime('now'))
            ->setIsFirst(true)
            ;

        // Assignation de toutes les routes ADMIN et des MENUS au rôle
        $apiPaths = RouteHelper::ADMIN_ROUTE($pathsRow);
        $menuPaths = RouteHelper::MENU_ROUTE($pathsRow);
        $paths = array_merge($apiPaths, $menuPaths);
        
        foreach ($paths as $path) {
            $path->addRole($role);
            $manager->persist($path);
        }
        $manager->persist($role);

        // ── 2. Création du profil Admin ────────────────────────────────────────
        $admin = new Admin();
        $admin
            ->setNom('GbatCar')
            ->setPrenom('Administrateur')
            ->setTelephone('+2250000000001')
            ->setCreatedAt(new \DateTime('now'))
            ;
        $manager->persist($admin);

        // ── 3. Création du compte Utilisateur (Super Admin) ────────────────────
        $user = new User();
        $user
            ->setUsername('admin@gbatcar.com')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'GbatCar@2024!'))
            ->setEmail('admin@gbatcar.com')
            ->setNom('GbatCar')
            ->setPrenom('Admin')
            ->setCivilite('Mr')
            ->setType(User::TYPE['ADMIN'])
            ->setAdmin($admin)
            ->setIsFirst(true)
            ->setIsEnabled(true)
            ->setIsLocked(false)
            ->setCreatedAt(new \DateTime('now'))
            ;
        $manager->persist($user);

        // ── 4. Liaison du User au Rôle ─────────────────────────────────────────
        $role->addUser($user);
        $manager->persist($role);

        $manager->flush();

        echo "\n✅ Super Administrateur GbatCar créé avec succès !";
        echo "\n   Login    : admin@gbatcar.com";
        echo "\n   Password : GbatCar@2024!\n";
    }
}