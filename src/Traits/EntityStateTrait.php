<?php

namespace App\Traits;

use App\Helpers\EntityHelper;
use Symfony\Component\Serializer\Annotation\Groups;

trait  EntityStateTrait
{

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Groups({"default", "account", "log", "plan", "planModel", "tva"})
     */
    private $etat = EntityHelper::ETAT['INACTIF'];

    public function setEtat($etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getEtat()
    {        
        return $this->etat;
    }
}
