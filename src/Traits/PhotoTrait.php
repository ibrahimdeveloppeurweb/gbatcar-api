<?php

namespace App\Traits;

use App\Entity\Extra\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/*
- Les entités utilisant le trait FileTrait doivent implémenter l'interface FileInterface
afin de forcer l'implementation des méthodes obligatoires
- Les entités utilisant le trait FileTrait doivent utiliser l'annotation @ORM\HasLifecycleCallbacks()
afin d'effectuer les action en PreFlush et PreUpdate
*/
trait  PhotoTrait
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Extra\File", cascade={"persist", "remove"})
     * @Groups({"default", "list", "file", "photo"})
     * @var File
     */
    protected $photo;

    private $photoUuid;
    private $root = __DIR__ . '/';

    public function getPhoto()
    {
        if ($this->photo instanceof File) {
            $this->photo->setFullPath($this->getPhotoSrc());
        }
        return $this->photo;
    }

    abstract public function getFolderPath(): string;

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }

    /**
     * @return string
     * @Groups({"default", "list", "file", "photo"})
     */
    public function getPhotoSrc()
    {
        if ($this->photo instanceof File) { return $this->getFolderPath() . $this->photo->getSrc(); }
    }

    public function getPhotoUuid()
    {
        return $this->photoUuid;
    }

    /**
     * @param mixed
     */
    public function setPhotoUuid($photoUuid)
    {
        $this->photoUuid = $photoUuid;
        return $this;
    }
}
