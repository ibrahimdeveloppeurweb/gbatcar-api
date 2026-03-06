<?php

namespace App\Command;

use App\Entity\Extra\Path;
use App\Repository\Extra\PathRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandPath extends Command
{
    private $em;
    private $route;
    private $pathRepository;
    protected static $defaultName = 'z:g:path';
    public function __construct(
        RouterInterface $route,
        EntityManagerInterface $em,
        PathRepository $pathRepository
        )
    {
        parent::__construct();
        $this->em = $em;
        $this->route = $route;
        $this->pathRepository = $pathRepository;
    }

    protected function configure(): void
    {
    // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $routes = $this->route->getRouteCollection()->all();
        foreach ($routes as $nom => $route) {
            $options = $route->getOptions();
            if (
            preg_match('#^/api/private#', $route->getPath()) or
            preg_match('#^/printer#', $route->getPath()) or
            preg_match('#^/admin/package#', $route->getPath()) or
            preg_match('#^/api/auth#', $route->getPath()) or
            $route->getPath() === "/"
            ) {
                $description = (isset($options["description"])) ? $options["description"] : null;
                $permission = (isset($options["permission"])) ? $options["permission"] : null;
                if (isset($description) && $description !== null && $description !== "null") {
                    $role = $this->pathRepository->findOneBy(['nom' => $nom]);
                    $path = (!$role) ? new Path() : $role;
                    $path->setNom($nom);
                    $path->setChemin($route->getPath());
                    $path->setLibelle($description);
                    $path->setPermission($permission);

                    if (
                    preg_match('#^/api/private/client#', $route->getPath()) ||
                    preg_match('#^/printer/client#', $route->getPath()) ||
                    preg_match('#^/api/auth#', $route->getPath())
                    ) {
                        $path->setType(Path::TYPE['CLIENT']);
                    }
                    elseif (
                    preg_match('#^/api/private/admin#', $route->getPath()) ||
                    preg_match('#^/printer/admin#', $route->getPath()) ||
                    preg_match('#^/admin#', $route->getPath())
                    ) {
                        $path->setType(Path::TYPE['ADMIN']);
                    }
                    elseif (
                    preg_match('#^/api/private/extra#', $route->getPath())
                    ) {
                        $path->setType(Path::TYPE['EXTRA']);
                    }
                    else {
                        // Valeur par défaut si aucun filtre ne correspond exactement, mais qu'elle est dans /api/private
                        $path->setType(Path::TYPE['CLIENT']);
                    }
                    $this->em->persist($path);
                    $this->em->flush();
                }
            }
        }
        $output->writeln("La génération des routes s'est déroulé avec succès.");
        return Command::SUCCESS;
    }
}