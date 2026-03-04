<?php

namespace App\Command;

use App\Entity\Extra\Role;
use App\Helpers\RouteHelper;
use App\Repository\Extra\PathRepository;
use App\Repository\Extra\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncAdminRoutesCommand extends Command
{
    protected static $defaultName = 'z:g:sync:admin-routes';

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

    protected function configure()
    {
        $this->setDescription('Synchronise les nouvelles routes avec le rôle super administrateur existant.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $role = $this->roleRepository->findOneBy(['isFirst' => true]);
        if (!$role) {
            $output->writeln('<error>Le rôle "Accès super administrateur" n\'existe pas.</error>');
            return Command::FAILURE;
        }

        $pathsRow = $this->pathRepository->findAll();
        $paths = RouteHelper::ADMIN_ROUTE($pathsRow);
        $added = 0;
        foreach ($paths as $path) {
            if (!$role->getPaths()->contains($path)) {
                $path->addRole($role);
                $this->em->persist($path);
                $added++;
            }
        }

        if ($added > 0) {
            $this->em->flush();
            $output->writeln("<info>$added nouvelles routes ajoutées au rôle super administrateur.</info>");
        } else {
            $output->writeln('<info>Aucune nouvelle route à ajouter.</info>');
        }

        return Command::SUCCESS;
    }
}
