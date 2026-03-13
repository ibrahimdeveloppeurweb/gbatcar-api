<?php

namespace App\Command;

use App\Data\MenuData;
use App\Entity\Extra\Path;
use App\Entity\Extra\Role;
use App\Repository\Extra\PathRepository;
use App\Repository\Extra\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncMenuPathsCommand extends Command
{
    protected static $defaultName = 'z:g:sync:menu';
    protected static $defaultDescription = 'Synchronise les éléments du menu Angular (MenuData.php) vers la table Path pour la gestion des permissions.';

    private $em;
    private $pathRepository;
    private $roleRepository;

    public function __construct(EntityManagerInterface $em, PathRepository $pathRepository, RoleRepository $roleRepository)
    {
        parent::__construct();
        $this->em = $em;
        $this->pathRepository = $pathRepository;
        $this->roleRepository = $roleRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Synchronisation du Menu Angular avec la base de données (Permissions)');

        $menuItems = MenuData::getMenu();
        $superAdminRole = $this->roleRepository->findOneBy(['isFirst' => true]); // Généralement le Super-Admin

        if (!$superAdminRole) {
            $io->warning("Aucun rôle Super-Administrateur (isFirst=true) n'a été trouvé. Les chemins seront créés mais non rattachés par défaut.");
        }

        $addedPaths = 0;
        $updatedPaths = 0;

        foreach ($menuItems as $item) {
            // Synchronisation du parent
            $this->syncPath($item['nom'], $item['label'], $item['link'] ?? null, $superAdminRole, $addedPaths, $updatedPaths);

            // Synchronisation des sous-menus s'ils existent
            if (isset($item['subItems']) && is_array($item['subItems'])) {
                foreach ($item['subItems'] as $subItem) {
                    $this->syncPath($subItem['nom'], $subItem['label'], $subItem['link'] ?? null, $superAdminRole, $addedPaths, $updatedPaths);
                }
            }
        }

        $this->em->flush();

        $io->success([
            'Synchronisation du menu terminée.',
            sprintf('%d nouveaux menus ajoutés.', $addedPaths),
            sprintf('%d menus mis à jour.', $updatedPaths)
        ]);

        return Command::SUCCESS;
    }

    private function syncPath(string $nom, string $label, ?string $link, ?Role $superAdminRole, int&$added, int&$updated): void
    {
        // On cherche le path par son nom unique pour les menus
        $path = $this->pathRepository->findOneBy(['nom' => $nom]);

        if (!$path) {
            $path = new Path();
            $path->setNom($nom);
            $path->setType('ADMIN');
            $added++;
        }
        else {
            $updated++;
        }

        $path->setChemin($link);
        $path->setLibelle($label); // Le label affiché dans l'UI des permissions

        // Lier automatiquement au super-admin s'il existe et n'a pas déjà ce path
        if ($superAdminRole && !$superAdminRole->getPaths()->contains($path)) {
            $path->addRole($superAdminRole);
        }

        $this->em->persist($path);
    }
}