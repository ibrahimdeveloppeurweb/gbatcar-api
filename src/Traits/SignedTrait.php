<?php

namespace App\Traits;

use App\Entity\Extra\Folder;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait SignedTrait
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Extra\Folder", cascade={"persist", "remove"})
     * @Groups({"default", "list", "folder"})
     */
    protected $signed;

    private $signedUuid;

    abstract public function getSignedPath(): string;

    public function getSignedUuid()
    {
        return $this->signedUuid;
    }

    /**
     * @param mixed
     */
    public function setSignedUuid($signedUuid)
    {
        $this->signedUuid = $signedUuid;
        return $this;
    }

    /**
     * @param mixed $signed
     */
    public function setSigned($signed)
    {
        $this->signed = $signed;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSigned()
    {
        if ($this->signed instanceof Folder) {
            foreach ($this->signed->getFiles() as $file) {
                $file->setFullPath($this->getSignedPath() .  $file->getSrc());
            }
        }

        return $this->signed;
    }
}
