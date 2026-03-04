<?php

namespace App\Traits;

/*
- Les entités utilisant le trait FileTrait doivent implémenter l'interface FileInterface
afin de forcer l'implementation des méthodes obligatoires
- Les entités utilisant le trait FileTrait doivent utiliser l'annotation @ORM\HasLifecycleCallbacks()
afin d'effectuer les action en PreFlush et PreUpdate
*/

use Symfony\Component\Serializer\Annotation\Groups;

trait  SearchableTrait
{
    protected $searchableTitle;
    protected $searchableDetail;

    /**
     * @return string
     * @Groups({"searchable"})
     */
    abstract function getSearchableTitle(): string;
    
    /**
     * @return string
     * @Groups({"searchable"})
     */
    abstract function getSearchableDetail(): string;
}




