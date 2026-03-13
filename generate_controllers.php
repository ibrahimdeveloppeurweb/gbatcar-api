<?php

$entities = [
    ['name' => 'Client', 'var' => 'client', 'route' => 'client', 'group' => 'client'],
    ['name' => 'Vehicle', 'var' => 'vehicle', 'route' => 'vehicle', 'group' => 'vehicle'],
    ['name' => 'Contract', 'var' => 'contract', 'route' => 'contract', 'group' => 'contract'],
    ['name' => 'Payment', 'var' => 'payment', 'route' => 'payment', 'group' => 'payment'],
    ['name' => 'Penalty', 'var' => 'penalty', 'route' => 'penalty', 'group' => 'penalty'],
    ['name' => 'Maintenance', 'var' => 'maintenance', 'route' => 'maintenance', 'group' => 'maintenance'],
    ['name' => 'MaintenanceAlert', 'var' => 'maintenanceAlert', 'route' => 'maintenance-alert', 'group' => 'maintenance_alert'],
];

$managerDir = __DIR__ . '/src/Manager/Client';
$controllerDir = __DIR__ . '/src/Controller/Client';

if (!file_exists($managerDir)) mkdir($managerDir, 0777, true);
if (!file_exists($controllerDir)) mkdir($controllerDir, 0777, true);

foreach ($entities as $e) {
    $Name = $e['name'];
    $var = $e['var'];
    $route = $e['route'];
    $group = $e['group'];
    $PERM = strtoupper(str_replace('-', '_', $route));
    
    // MANAGER
    $managerContent = "<?php

namespace App\Manager\Client;

use App\Entity\Client\\$Name;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\\{$Name}Repository;

class {$Name}Manager
{
    private \$em;
    private \${$var}Repository;

    public function __construct(
        EntityManagerInterface \$em,
        {$Name}Repository \${$var}Repository
    ) {
        \$this->em = \$em;
        \$this->{$var}Repository = \${$var}Repository;
    }

    public function create(object \$data): $Name
    {
        // To be implemented
        return new $Name();
    }

    public function update(string \$uuid, object \$data): $Name
    {
        // To be implemented
        return new $Name();
    }

    public function delete($Name \${$var}): $Name
    {
        // To be implemented
        return \${$var};
    }
}
";
    file_put_contents("$managerDir/{$Name}Manager.php", $managerContent);

    // CONTROLLER
    $controllerContent = "<?php

namespace App\Controller\Client;

use App\Manager\Client\\{$Name}Manager;
use App\Repository\Client\\{$Name}Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path=\"/api/private/client/$route\")
 */
class {$Name}Controller extends AbstractController
{
    private \${$var}Repository;
    private \${$var}Manager;

    public function __construct(
        {$Name}Repository \${$var}Repository,
        {$Name}Manager \${$var}Manager
    ) {
        \$this->{$var}Repository = \${$var}Repository;
        \$this->{$var}Manager = \${$var}Manager;
    }

    /**
     * @Route(\"/\", name=\"index_$route\", methods={\"GET\"},
     * options={\"description\"=\"Liste des $route\", \"permission\"=\"{$PERM}:LIST\"})
     */
    public function index(Request \$request)
    {
        \$items = \$this->{$var}Repository->findAll();
        return \$this->json(\$items, 200, [], ['groups' => [\"$group\"]]);
    }

    /**
     * @Route(\"/new\", name=\"new_$route\", methods={\"POST\"}, 
     * options={\"description\"=\"Ajouter un nouveau $route\", \"permission\"=\"{$PERM}:NEW\"})
     */
    public function new(Request \$request)
    {
        // To be implemented with {$Name}Manager
    }

    /**
     * @Route(\"/{uuid}/show\", name=\"show_$route\", methods={\"GET\"}, 
     * options={\"description\"=\"Détails d'un $route\", \"permission\"=\"{$PERM}:SHOW\"})
     */
    public function show(\$uuid)
    {
        \$item = \$this->{$var}Repository->findOneByUuid(\$uuid);
        if (!\$item) {
            return \$this->json(['message' => 'Not found'], 404);
        }
        return \$this->json(\$item, 200, [], ['groups' => [\"$group\"]]);
    }

    /**
     * @Route(\"/{uuid}/edit\", name=\"edit_$route\", methods={\"PUT\", \"POST\"}, 
     * options={\"description\"=\"Modifier un $route\", \"permission\"=\"{$PERM}:EDIT\"})
     */
    public function edit(Request \$request, \$uuid)
    {
        // To be implemented with {$Name}Manager
    }

    /**
     * @Route(\"/{uuid}/delete\", name=\"delete_$route\", methods={\"DELETE\"},
     * options={\"description\"=\"Supprimer un $route\", \"permission\"=\"{$PERM}:DELETE\"})
     */
    public function delete(\$uuid)
    {
        // To be implemented with {$Name}Manager
    }
}
";
    file_put_contents("$controllerDir/{$Name}Controller.php", $controllerContent);
}

echo "Controllers and Managers generated successfully.\n";
