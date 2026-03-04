<?php

namespace App\Traits;

use App\Entity\Extra\Folder;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait  FolderTrait
{
 

    private $folderUuid;

    abstract public function getFolderPath(): string;

    public function getFolderUuid()
    {
        return $this->folderUuid;
    }

    /**
     * @param mixed
     */
    public function setFolderUuid($folderUuid)
    {
        $this->folderUuid = $folderUuid;
        return $this;
    }

    /**
     * @param mixed $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
        return $this;
    }

 
}