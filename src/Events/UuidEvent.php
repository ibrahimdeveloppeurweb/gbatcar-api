<?php

namespace App\Events;

use Ramsey\Uuid\Uuid;
use App\Traits\PhotoTrait;
use App\Entity\Admin\Bill;
use App\Entity\Admin\User;
use App\Entity\Extra\File;
use App\Traits\FolderTrait;
use App\Entity\Extra\Folder;
use App\Entity\Admin\Agency;
use App\Entity\Admin\Service;
use App\Entity\Admin\Package;
use App\Services\QrCodeService;
use App\Exception\ExceptionApi;
use App\Entity\Extra\SettingSms;
use App\Entity\Admin\PaymentBill;
use App\Entity\Extra\SettingMail;
use App\Entity\Admin\Subscription;
use App\Entity\Admin\OptionPackage;
use App\Entity\Extra\SettingTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UuidEvent
{
    private $em;
    private $security;
  
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
       
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $uuid = Uuid::uuid4();
        if (method_exists($entity, 'setUuid')) {
            ($entity->getUuid()) ?? $entity->setUuid($uuid);
        }

        $this->fileSetter($args->getObject());
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (method_exists($entity, 'setQrCode')) {
            if (!$entity->getQrCode()) {
               // $this->qrCodeService->qrcode($entity);
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $user = $this->security->getUser();
        $entity = $args->getObject();
        if (
            $entity instanceof Agency 
            // $entity instanceof Package ||
            // $entity instanceof OptionPackage ||
            // $entity instanceof Subscription ||
            // $entity instanceof Bill ||
            // $entity instanceof SettingMail ||
            // $entity instanceof SettingSms ||
            // $entity instanceof SettingTemplate ||
            // $entity instanceof PaymentBill ||
            // $entity instanceof Service
        ) {
            return;
        }
        $this->fileSetter($args->getObject());
    }

    public function preFlush(PreFlushEventArgs $args)
    {
        /** @var EntityManagerInterface $em */
        // $em = $args->getEntityManager();
        // $this->fileSetter($args->getObject());
    }

    public function fileSetter($entity)
    {
        /*
        * Lie le fichier Image à une entité utilisant le trait PhotoTrait si elle l'attribut photo contient du texte plutot qu'une
        * entité App\Entity\Extra\File;
        */

        /** @var User $user */
        $user = $this->security->getUser();
        //-------------------NE PAS SUPPRIMER
        //        $publicFolder = __DIR__ . '/../../public/';
        //        $tempFolder = $publicFolder . 'uploads/' . $user->getAgency()->getUuid() . '/'
        //            . $entity->getCreateBy()->getUuid() . '/';
        //-------------------NE PAS SUPPRIMER
        if (in_array(PhotoTrait::class, class_uses(get_class($entity)), true)) {
            if (!empty($entity->getPhotoUuid())) {
                $photo = $this->em->getRepository(File::class)->findOneBy(['uuid' => (string)$entity->getPhotoUuid()]);
                if ($photo instanceof File) {

                    if (!$entity instanceof Agency && $entity->getPhoto() instanceof File) {
                        $filePath = __DIR__ . '/../../public/' . $entity->getPhotoSrc();
                        @unlink($filePath);
                        $this->em->remove($entity->getPhoto());
                    }
                    $entity->setPhoto($photo);
                    //-------------------NE PAS SUPPRIMER
                    //$tempFile = $tempFolder . $entity->getPhoto()->getSrc();
                    //$newPath = $publicFolder . $entity->getPhotoSrc();
                    //try {
                    //    mkdir(dirname($newPath), 0777, true);
                    //    if (file_exists($tempFile)) {
                    //        rename($tempFile, $newPath);
                    //    }
                    //}catch (\Throwable $exception){}
                    //-------------------NE PAS SUPPRIMER
                }
            }
        }
        //exit;

        if (in_array(FolderTrait::class, class_uses(get_class($entity)), true)) {
            if (!empty($entity->getFolderUuid())) {
                $folder = $this->em->getRepository(Folder::class)->findOneBy(['uuid' => (string)$entity->getFolderUuid()]);
                if ($folder instanceof Folder) {
                    $entity->setFolder($folder);
                }
            }
        }
    }
}
