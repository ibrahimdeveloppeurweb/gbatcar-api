<?php

namespace App\Entity\Extra;

use App\Entity\Extra\File;
use App\Repository\Extra\FolderRepository;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FolderRepository::class)
 */
class Folder
{

    use UserObjectNoCodeTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="folder")
     */
    private $files;

    /**
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="signed")
     */
    private $filesSigne;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->filesSigne = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setFolder($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getFolder() === $this) {
                $file->setFolder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFilesSigne(): Collection
    {
        return $this->filesSigne;
    }

    public function addFilesSigne(File $filesSigne): self
    {
        if (!$this->filesSigne->contains($filesSigne)) {
            $this->filesSigne[] = $filesSigne;
            $filesSigne->setSigned($this);
        }

        return $this;
    }

    public function removeFilesSigne(File $filesSigne): self
    {
        if ($this->filesSigne->removeElement($filesSigne)) {
            // set the owning side to null (unless already changed)
            if ($filesSigne->getSigned() === $this) {
                $filesSigne->setSigned(null);
            }
        }

        return $this;
    }

    
}
