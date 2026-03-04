<?php

namespace App\Manager\Extra;

use App\Entity\Admin\User;
use App\Entity\Extra\File;
use App\Entity\Extra\Folder;
use App\Repository\Admin\AgencyRepository;
use App\Repository\Admin\UserRepository;
use App\Repository\Extra\FolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @package App\Manager
 * La fonction handle sera notre point d'entrée lors de l'upload de fichier
 */
class UploaderManager
{
    private $em;

    /** @var User */
    private $user;
    /*
    * Taille des morceaux
    */
    private $chunkSize = 1024 * 1024 * 100;
    /*
    * Nombre de morceaux reçu d'un fichier
    */
    private $totalChunkReceived = 0;
    private $missingChunk;
    private $missingMessage = '';
    private $tempFolder = null;
    private $folderOld = null;
    private $folderNew = null;
    /** @var null | File */
    private $file = null;
    private $folder = null;
    private $folderUuid = null;
    /*
    * Dossier d'upload temporaire
    */
    private $uploadFolder = __DIR__ . '/../../../public/';
    private $allowedExtensions = [
        'image/jpeg', 
        'image/png', 
        'image/gif', 
        'application/pdf', 
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    private $agencyRepository;
    private $folderRepository;
    private $userRepository;

    public function __construct(
        EntityManagerInterface $em, 
        AgencyRepository $agencyRepository,
        FolderRepository $folderRepository,
        TokenStorageInterface $tokenStorage,
         UserRepository $userRepository,
    ) {
        if ($tokenStorage->getToken()) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
        $this->em = $em;
        $this->agencyRepository = $agencyRepository;
        $this->folderRepository = $folderRepository;
        $this->userRepository = $userRepository;
    }
    
    public function base64($data, $entity)
    {
        
        $path = $this->uploadFolder . $entity->getFolderPath();
        if (!file_exists($path)) {
            mkdir($path, 0777, true); // Changez les permissions selon vos besoins
        }

        $base64Data = substr($data->chunk, strpos($data->chunk, ',') + 1);
        if (base64_decode($base64Data, true) === false) {
            throw new \Exception('Le fichier est corrompu ');
        }
        $fileContent = base64_decode($base64Data);
        
        // Génération d'un nom de fichier unique
        $extension = $this->mime2ext($data->fileType);
        $src = $data->uniqId . '.' . $extension;
        
        // Sauvegarde du fichier dans le répertoire spécifié
        $fileSaved = file_put_contents($path . $src, $fileContent);

        $this->file = new File();
        $this->file->setSrc($src);
        $this->file->setCreateBy($this->user);
        $this->file->setRealName($data->fileName);
        $this->file->setType($data->fileType);

        $this->em->persist($this->file);
        $entity->setPhoto($this->file);
        
    }
    
    public function create($data, $entity)
    {
        // On verifie ici que l'utilisateur en cours à bien l'autorisation d'uploader des fichiers sur le serveur
        if (!$this->isUserAllowedToUpload()) {
            throw new \Exception('Cet Utilisateur n\'a pas le droit de téléverser des fichiers');
        }
        // On recupére uniquement les informations prévues pour l'upload
        // On peut ici par exemple decider de bloquer une requête qui contient des paramètres
        
        $this->tempFolder .= $this->uploadFolder . $entity->getFolderPath();
        
        // On verifie les types de fichiers autorisé
        if (!in_array($data->fileType, $this->allowedExtensions, false)) {
            throw New \Exception('Type de fichier ' . $data->fileType . ' non autorisé');
        }
        $fileUniqFolder = $this->createUniqFolder($data, "IMAGE");
        $fileChunks = $this->getFileChunks($fileUniqFolder);
        $chunksNum = ceil($data->fileSize / ($this->chunkSize));

        // on verifie qu'aucun morceau intermédiaire ne manque
        if ($this->isChunkMissing($data, $fileUniqFolder, $fileChunks)) {
            if ((int)$data['loaded'] !== (int)$this->missingChunk) {
                return [
                    'success' => true,
                    "status" => "missing",
                    'message' => $this->missingMessage,
                    "part" => $this->missingChunk,
                    "full" => $chunksNum,
                    "folderUuid" => $this->folderUuid,
                    "received" => (int)$data['loaded']
                ];
            }
        }
        
        // on enregistre le morceau actuel
        $result = null;
        if ((int)$data->loaded < (int)$chunksNum) {
            $result = $this->saveChunk($data, "IMAGE");
            if ($result !== true) {
                return [
                    'success' => false,
                    'message' => 'un problème a été rencontré lors de l\'écriture du fichier. Votre espace disque est peut être plein.',
                    "folderUuid" => $this->folderUuid,
                    'error' => $result->getMessage(),
                ];
            }
        }
        
        if ($this->isReadyToMerge($data, $fileUniqFolder, $chunksNum)) {
            $genResult = $this->generateFile($data, $fileUniqFolder, $entity);
            return [
                'success' => true,
                "status" => "finished",
                'id' => $this->file instanceof File ? $this->file->getUuid() : '',
                "folderUuid" => $this->folderUuid,
                "generated" => true,
            ];
        }
    }
    
    public function signature($user, $data)
    {
        if ($user->getAgency() && $user->getTenant() && $user->getCustomer() && $user->getOwner()) {
            $agency =null;
            if ($user->getAgency()) {
                $agency = $user->getAgency();
            }elseif ($user->getTenant()) {
                $agency = $user->getTenant()->getAgency();
            }elseif ($user->getCustomer()) {
                $agency = $user->getCustomer()->getAgency();
            }elseif ($user->getOwner()) {
                $agency = $user->getOwner()->getAgency();
            }
            $this->tempFolder .= $this->uploadFolder . 'uploads/' . $agency->getUuid() . '_' .str_replace(' ', '', $agency->getNom()). '/signature' .'/';
        } else {
            $this->tempFolder .= $this->uploadFolder . 'uploads/signature' .'/';
        }

        // On verifie les types de fichiers autorisé
        if (!in_array($data->fileType, $this->allowedExtensions, false)) {
            throw New \Exception('Type de fichier ' . $data->fileType . ' non autorisé');
        }
        $fileUniqFolder = $this->createUniqFolder($data, "IMAGE");
        $fileChunks = $this->getFileChunks($fileUniqFolder);
        $chunksNum = ceil($data->fileSize / ($this->chunkSize));

        // on verifie qu'aucun morceau intermédiaire ne manque
        if ($this->isChunkMissing($data, $fileUniqFolder, $fileChunks)) {
            if ((int)$data['loaded'] !== (int)$this->missingChunk) {
                return [
                    'success' => true,
                    "status" => "missing",
                    'message' => $this->missingMessage,
                    "part" => $this->missingChunk,
                    "full" => $chunksNum,
                    "folderUuid" => $this->folderUuid,
                    "received" => (int)$data['loaded']
                ];
            }
        }
        
        // on enregistre le morceau actuel
        $result = null;
        if ((int)$data->loaded < (int)$chunksNum) {
            $result = $this->saveChunk($data, "IMAGE");
            if ($result !== true) {
                return [
                    'success' => false,
                    'message' => 'un problème a été rencontré lors de l\'écriture du fichier. Votre espace disque est peut être plein.',
                    "folderUuid" => $this->folderUuid,
                    'error' => $result->getMessage(),
                ];
            }
        }
        
        // if ($this->isReadyToMerge($data, $fileUniqFolder, $chunksNum)) {
        //     $genResult = $this->generateSignature($data, $fileUniqFolder, $user);
        //     return [
        //         'success' => true,
        //         "status" => "finished",
        //         'id' => $this->file instanceof File ? $this->file->getUuid() : '',
        //         "folderUuid" => $this->folderUuid,
        //         "generated" => true,
        //     ];
        // }
    }
    
    public function folder($request)
    {
        $data = \json_decode($request->getContent());
        $agency = $this->agencyRepository->findOneByUuid($data->agency); 
        $user = $this->userRepository->findOneBy(['isFirst' => true]); 
        if ($agency) {
         $this->tempFolder .= $this->uploadFolder . 'uploads/' . $agency->getUuid() . '_' .str_replace(' ', '', $agency->getNom()). '/' . $data->path.  '/';

        } else {
         $this->tempFolder .= $this->uploadFolder . 'uploads/' . $user->getUuid() . '_' .str_replace(' ', '', $user->getNom()). '/' . $data->path.  '/';

        }
        

        // On verifie ici que l'utilisateur en cours à bien l'autorisation d'uploader des fichiers sur le serveur
        if (!$this->isUserAllowedToUpload()) {
            throw new \Exception('Cet Utilisateur n\'a pas le droit de téléverser des fichiers');
        }
        // On recupére uniquement les informations prévues pour l'upload
        // On peut ici par exemple decider de bloquer une requête qui contient des paramètres
        
        // $this->tempFolder .= $this->uploadFolder . $entity->getFolderPath();
        
        // On verifie les types de fichiers autorisé
        if (!in_array($data->fileType, $this->allowedExtensions, false)) {
            throw New \Exception('Type de fichier ' . $data->fileType . ' non autorisé');
        }
        $folder = $this->createUniqFolder($data, "FOLDER");
        $fileChunks = $this->getFileChunks($folder);
        $chunksNum = ceil($data->fileSize / ($this->chunkSize));

        // on verifie qu'aucun morceau intermédiaire ne manque
        if ($this->isChunkMissing($data, $folder, $fileChunks)) {
            if ((int)$data['loaded'] !== (int)$this->missingChunk) {
                return [
                    'success' => true,
                    "status" => "missing",
                    'message' => $this->missingMessage,
                    "part" => $this->missingChunk,
                    "full" => $chunksNum,
                    "folderUuid" => $this->folderUuid,
                    "received" => (int)$data['loaded']
                ];
            }
        }
        
        // on enregistre le morceau actuel
        $result = null;
        if ((int)$data->loaded < (int)$chunksNum) {
            $result = $this->saveChunk($data, "FOLDER");
            if ($result !== true) {
                return [
                    'success' => false,
                    'message' => 'un problème a été rencontré lors de l\'écriture du fichier. Votre espace disque est peut être plein.',
                    "folderUuid" => $this->folderUuid,
                    'error' => $result->getMessage(),
                ];
            }
        }
        
        if ($this->isReadyToMerge($data, $folder, $chunksNum)) {
            $file = $this->generateFolder($data, $folder);
            return [
                'success' => true,
                "status" => "finished",
                'uuid' => $this->file instanceof File ? $this->file->getUuid() : '',
                "folderUuid" => $this->folderUuid,
                "generated" => true,
            ];
        }
    }
    
    public function signed($request)
    {
        $data = \json_decode($request->getContent());
        $agency = $this->agencyRepository->findOneByUuid($data->agency); 
        $this->tempFolder .= $this->uploadFolder . 'uploads/' . $agency->getUuid() . '_' .str_replace(' ', '', $agency->getNom()). '/' . $data->path.  '/';

        // On verifie ici que l'utilisateur en cours à bien l'autorisation d'uploader des fichiers sur le serveur
        if (!$this->isUserAllowedToUpload()) {
            throw new \Exception('Cet Utilisateur n\'a pas le droit de téléverser des fichiers');
        }
        // On recupére uniquement les informations prévues pour l'upload
        // On peut ici par exemple decider de bloquer une requête qui contient des paramètres
        
        // $this->tempFolder .= $this->uploadFolder . $entity->getFolderPath();
        
        // On verifie les types de fichiers autorisé
        if (!in_array($data->fileType, $this->allowedExtensions, false)) {
            throw New \Exception('Type de fichier ' . $data->fileType . ' non autorisé');
        }
        $folder = $this->createUniqFolder($data, "FOLDER");
        $fileChunks = $this->getFileChunks($folder);
        $chunksNum = ceil($data->fileSize / ($this->chunkSize));

        // on verifie qu'aucun morceau intermédiaire ne manque
        if ($this->isChunkMissing($data, $folder, $fileChunks)) {
            if ((int)$data['loaded'] !== (int)$this->missingChunk) {
                return [
                    'success' => true,
                    "status" => "missing",
                    'message' => $this->missingMessage,
                    "part" => $this->missingChunk,
                    "full" => $chunksNum,
                    "folderUuid" => $this->folderUuid,
                    "received" => (int)$data['loaded']
                ];
            }
        }
        
        // on enregistre le morceau actuel
        $result = null;
        if ((int)$data->loaded < (int)$chunksNum) {
            $result = $this->saveChunk($data, "FOLDER");
            if ($result !== true) {
                return [
                    'success' => false,
                    'message' => 'un problème a été rencontré lors de l\'écriture du fichier. Votre espace disque est peut être plein.',
                    "folderUuid" => $this->folderUuid,
                    'error' => $result->getMessage(),
                ];
            }
        }
        
        if ($this->isReadyToMerge($data, $folder, $chunksNum)) {
            $this->generateSigned($data, $folder);
            return [
                'success' => true,
                "status" => "finished",
                'uuid' => $this->file instanceof File ? $this->file->getUuid() : '',
                "folderUuid" => $this->folderUuid,
                "generated" => true,
            ];
        }
    }

    /** Todo */
    public function isUserAllowedToUpload()
    {
        return true;
    }

    public function createUniqFolder($data)
    {
        if (!is_dir($this->tempFolder . $data->uniqId . '/')) {
            try {
                mkdir($this->tempFolder . $data->uniqId, 0777, true);
            } catch (\Exception $e) {
                throw New \Exception('impossible de créer le dossier temporaire ' . $data->uniqId . ' dans ' . $this->tempFolder . ' Vérifier les droits d\'accès');
            }
        }
        return $this->tempFolder . $data->uniqId . '/';
    }

    public function renameFolder($agency, $nom)
    {
        // on recupere l'uuid concatené au nom 
        if ($agency && $nom) {
            $old = 'uploads/' . $agency->getUuid(). '_' .str_replace(' ', '', $nom) .'/';
            $new = 'uploads/' . $agency->getUuid(). '_' .str_replace(' ', '',$agency->getNom()).'/';

            $this->folderOld .= $this->uploadFolder . $old;
            $this->folderNew .= $this->uploadFolder . $new;
            if (is_dir($this->folderOld)) {
                rename($this->folderOld, $this->folderNew);
            }
        }
    }

    public function deleteFolder($uuid, $path, $agency)
    {
        $folder = $this->folderRepository->findOneByUuid($uuid);
        if ($folder) {
            foreach ($folder->getFiles() as $file) {
                if (isset($path) && $path !== null && $path !== 'null') {
                    $path = 'uploads/'.$agency->getUuid().'_'.str_replace(' ', '', $agency->getNom()).'/'.$path.'/'.$file->getSrc();
                }else{
                    $path = $file->getFullPath();
                }
                $this->deleteFile($path);
                $this->em->remove($file);
            }
        }
        $this->em->remove($folder);
    }

    public function deleteFile($path)
    {
        $path = $this->uploadFolder.$path;
        if (file_exists($path)) 
            @unlink($path);
    }

    private function getFileChunks($tempFolder)
    {
        $files = [];
        if ($handle = opendir($tempFolder)) {
            $files = array();
            while ($file = readdir($handle)) {
                if ($file !== '.' && $file !== '..') {
                    $files[] = $file;
                }
            }
            closedir($handle);
        }
        
        return $files;
    }

    private function isChunkMissing($data, $fileUniqFolder, $fileChunks)
    {
        natsort($fileChunks);
        $files = array_values($fileChunks);
        $currentChunkCount = count($files);
        if ($currentChunkCount > 1) {
            $chunksNum = ceil($data->fileSize / ($this->chunkSize));
            $last = explode('.', $files[count($files) - 1])[1];
            $last = (int)str_replace($data->fileType, '', $last);
            $last++;
            $received = 0;
            for ($i = 0; $i < $currentChunkCount; $i++) {
                if (file_exists($fileUniqFolder . $data->uniqId . '.filePart' . $i)) {
                    ++$received;
                } else {
                    $this->missingMessage = 'missing ' . $data->uniqId . '.filePart' . $i;
                    $this->missingChunk = $i;
                    return true;
                }
            }
            if ($received === $chunksNum) {
                return false;
            }
        }
        return false;
    }
    
    private function saveChunk($data)
    {
        $chunk = $this->decode_chunk($data->chunk);
        try {
            file_put_contents($this->createUniqFolder($data) . $data->uniqId . '.filePart' . $data->loaded, $chunk, FILE_APPEND);
            return true;
        } catch (\Exception $e) {
            return $e;
        }

    }

    public function decode_chunk($data)
    {
        $data = explode(';base64,', $data);
        // dd($data);
        if (!is_array($data) || !isset($data[1])) {
            return false;
        }
        $data = base64_decode($data[1]);
        if (!$data) {
            return false;
        }
        return $data;
    }

    public function isReadyToMerge($data, $fileUniqFolder, $chunksNum)
    {
        $fileChunks = $this->getFileChunks($fileUniqFolder);
        // on verifie si un morceau intermédiaire ne manque pas
        if ($this->isChunkMissing($data, $fileUniqFolder, $fileChunks) || (int)$chunksNum !== (int)count($fileChunks)) {
            return false;
        }
        return true;
    }

    // public function generateSignature($data, $fileUniqFolder, $user)
    // {
    //     if (is_dir($fileUniqFolder)) {
    //         $files = [];
    //         if ($handle = opendir($fileUniqFolder)) {
    //             $files = array();
    //             while ($file = readdir($handle)) {
    //                 if ($file !== '.' && $file !== '..') {
    //                     $files[] = $file;
    //                 }
    //             }
    //             closedir($handle);
    //         }
    //         natsort($files);
    //         $files = array_values($files);

    //         $extension = $this->mime2ext($data->fileType);
    //         $base = $this->tempFolder . $user->getUuid().'_'.$user->getUsername() . '.' . $extension;
    //         if (file_exists($base)) {
    //             @unlink($base);
    //         }
    //         $signature = $user->getSignature() ? $user->getSignature() : new Signature();
    //         $signature->setUser($user);

    //         $this->file = $signature->getPhoto() ? $signature->getFile() : new File();
    //         $this->file->setCreateBy($this->user);
    //         $this->file->setSrc($data->uniqId . '.' . $extension);
    //         $this->file->setRealName($data->fileName);
    //         $this->file->setType($data->fileType);

    //         $this->em->persist($user);
    //         $signature->setPhoto($this->file);
    //         $this->em->persist($signature);
    //         $this->em->persist($this->file);
    //         foreach ($files as $file) {
    //             file_put_contents($base, file_get_contents($this->tempFolder . $data->uniqId . '/' . $file), FILE_APPEND);
    //             unlink($fileUniqFolder . $file);
    //         }
    //         rmdir($fileUniqFolder);
    //         return $this->file;
    //     }
    //     return false;
    // }

    public function generateFile($data, $fileUniqFolder, $entity)
    {
        if (is_dir($fileUniqFolder)) {
            $files = [];
            if ($handle = opendir($fileUniqFolder)) {
                $files = array();
                while ($file = readdir($handle)) {
                    if ($file !== '.' && $file !== '..') {
                        $files[] = $file;
                    }
                }
                closedir($handle);
            }
            natsort($files);
            $files = array_values($files);

            $extension = $this->mime2ext($data->fileType);
            $base = $this->tempFolder . $data->uniqId . '.' . $extension;
            if (file_exists($base)) {
                @unlink($base);
            }

            $this->file = new File();
            $this->file->setCreateBy($this->user);
            $this->file->setSrc($data->uniqId . '.' . $extension);
            $this->file->setRealName($data->fileName);
            $this->file->setType($data->fileType);

            $this->em->persist($this->file);
            $entity->setPhoto($this->file);
            foreach ($files as $file) {
                file_put_contents($base, file_get_contents($this->tempFolder . $data->uniqId . '/' . $file), FILE_APPEND);
                unlink($fileUniqFolder . $file);
            }
            rmdir($fileUniqFolder);
            return $this->file;
        }
        return false;
    }

    // public function generateFolder($data, $folderPath)
    // {
    //     if (is_dir($folderPath)) {
    //         $files = [];
    //         if ($handle = opendir($folderPath)) {
    //             $files = array();
    //             while ($file = readdir($handle)) {
    //                 if ($file !== '.' && $file !== '..') {
    //                     $files[] = $file;
    //                 }
    //             }
    //             closedir($handle);
    //         }
    //         natsort($files);
    //         $files = array_values($files);

    //         $extension = $this->mime2ext($data->fileType);
    //         $base = $this->tempFolder . $data->uniqId . '.' . $extension;
    //         if (file_exists($base)) {
    //             @unlink($base);
    //         }
            
    //         $folder = $this->folderRepository->findOneByUuid($data->folderUuid);
    //         $this->file = new File();
    //         $this->file->setCreateBy($this->user);
    //         $this->file->setSrc($data->uniqId . '.' . $extension);
    //         $this->file->setRealName($data->fileName);
    //         $this->file->setType($data->fileType);
    //         $this->file->setFolder($folder);

    //         $this->em->persist($this->file);
    //         $this->em->flush();
    //         foreach ($files as $file) {
    //             file_put_contents($base, file_get_contents($this->tempFolder . $data->uniqId . '/' . $file), FILE_APPEND);
    //             unlink($folderPath . $file);
    //         }
    //         rmdir($folderPath);
    //         return $this->file;
    //     }
    //     return false;
    // }
    public function generateFolder($data, $folderPath)
    {
        if (is_dir($folderPath)) {
            $files = [];
            if ($handle = opendir($folderPath)) {
                while ($file = readdir($handle)) {
                    if ($file !== '.' && $file !== '..') {
                        $files[] = $file;
                    }
                }
                closedir($handle);
            }
            natsort($files);
            $files = array_values($files);

            $extension = $this->mime2ext($data->fileType);
            $finalFileName = $data->uniqId . '.' . $extension;
            $base = $this->tempFolder . $finalFileName;

            if (file_exists($base)) {
                @unlink($base);
            }

            $folder = $this->folderRepository->findOneByUuid($data->folderUuid);
            $this->file = new File();
            $this->file->setCreateBy($this->user);
            $this->file->setSrc($finalFileName);
            $this->file->setRealName($data->fileName);
            $this->file->setType($data->fileType);
            $this->file->setFolder($folder);

            $this->em->persist($this->file);
            $this->em->flush();

            foreach ($files as $file) {
                file_put_contents($base, file_get_contents($folderPath . $file), FILE_APPEND);
                unlink($folderPath . $file);
            }
            rmdir($folderPath);
            return $this->file;
        }
        return false;
    }

    public function generateSigned($data, $signedPath)
    {
        if (is_dir($signedPath)) {
            $files = [];
            if ($handle = opendir($signedPath)) {
                $files = array();
                while ($file = readdir($handle)) {
                    if ($file !== '.' && $file !== '..') {
                        $files[] = $file;
                    }
                }
                closedir($handle);
            }
            natsort($files);
            $files = array_values($files);

            $extension = $this->mime2ext($data->fileType);
            $base = $this->tempFolder . $data->uniqId . '.' . $extension;
            if (file_exists($base)) {
                @unlink($base);
            }
            
            $signed = $this->folderRepository->findOneByUuid($data->signedPath);
            $this->file = new File();
            $this->file->setCreateBy($this->user);
            $this->file->setSrc($data->uniqId . '.' . $extension);
            $this->file->setRealName($data->fileName);
            $this->file->setType($data->fileType);
            $this->file->setSigned($signed);

            $this->em->persist($this->file);
            $this->em->flush();
            foreach ($files as $file) {
                file_put_contents($base, file_get_contents($this->tempFolder . $data->uniqId . '/' . $file), FILE_APPEND);
                unlink($signedPath . $file);
            }
            rmdir($signedPath);
            return $this->file;
        }
        return false;
    }

    public function mime2ext($mime)
    {
        $mime_map = [
            'video/3gpp2' => '3g2',
            'video/3gp' => '3gp',
            'video/3gpp' => '3gp',
            'application/x-compressed' => '7zip',
            'audio/x-acc' => 'aac',
            'audio/ac3' => 'ac3',
            'application/postscript' => 'ai',
            'audio/x-aiff' => 'aif',
            'audio/aiff' => 'aif',
            'audio/x-au' => 'au',
            'video/x-msvideo' => 'avi',
            'video/msvideo' => 'avi',
            'video/avi' => 'avi',
            'application/x-troff-msvideo' => 'avi',
            'application/macbinary' => 'bin',
            'application/mac-binary' => 'bin',
            'application/x-binary' => 'bin',
            'application/x-macbinary' => 'bin',
            'image/bmp' => 'bmp',
            'image/x-bmp' => 'bmp',
            'image/x-bitmap' => 'bmp',
            'image/x-xbitmap' => 'bmp',
            'image/x-win-bitmap' => 'bmp',
            'image/x-windows-bmp' => 'bmp',
            'image/ms-bmp' => 'bmp',
            'image/x-ms-bmp' => 'bmp',
            'application/bmp' => 'bmp',
            'application/x-bmp' => 'bmp',
            'application/x-win-bitmap' => 'bmp',
            'application/cdr' => 'cdr',
            'application/coreldraw' => 'cdr',
            'application/x-cdr' => 'cdr',
            'application/x-coreldraw' => 'cdr',
            'image/cdr' => 'cdr',
            'image/x-cdr' => 'cdr',
            'zz-application/zz-winassoc-cdr' => 'cdr',
            'application/mac-compactpro' => 'cpt',
            'application/pkix-crl' => 'crl',
            'application/pkcs-crl' => 'crl',
            'application/x-x509-ca-cert' => 'crt',
            'application/pkix-cert' => 'crt',
            'text/css' => 'css',
            'text/x-comma-separated-values' => 'csv',
            'text/comma-separated-values' => 'csv',
            'application/vnd.msexcel' => 'csv',
            'application/x-director' => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/x-dvi' => 'dvi',
            'message/rfc822' => 'eml',
            'application/x-msdownload' => 'exe',
            'video/x-f4v' => 'f4v',
            'audio/x-flac' => 'flac',
            'video/x-flv' => 'flv',
            'image/gif' => 'gif',
            'application/gpg-keys' => 'gpg',
            'application/x-gtar' => 'gtar',
            'application/x-gzip' => 'gzip',
            'application/mac-binhex40' => 'hqx',
            'application/mac-binhex' => 'hqx',
            'application/x-binhex40' => 'hqx',
            'application/x-mac-binhex40' => 'hqx',
            'text/html' => 'html',
            'image/x-icon' => 'ico',
            'image/x-ico' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
            'text/calendar' => 'ics',
            'application/java-archive' => 'jar',
            'application/x-java-application' => 'jar',
            'application/x-jar' => 'jar',
            'image/jp2' => 'jp2',
            'video/mj2' => 'jp2',
            'image/jpx' => 'jp2',
            'image/jpm' => 'jp2',
            'image/jpeg' => 'jpeg',
            'image/pjpeg' => 'jpeg',
            'application/x-javascript' => 'js',
            'application/json' => 'json',
            'text/json' => 'json',
            'application/vnd.google-earth.kml+xml' => 'kml',
            'application/vnd.google-earth.kmz' => 'kmz',
            'text/x-log' => 'log',
            'audio/x-m4a' => 'm4a',
            'audio/mp4' => 'm4a',
            'application/vnd.mpegurl' => 'm4u',
            'audio/midi' => 'mid',
            'application/vnd.mif' => 'mif',
            'video/quicktime' => 'mov',
            'video/x-sgi-movie' => 'movie',
            'audio/mpeg' => 'mp3',
            'audio/mpg' => 'mp3',
            'audio/mpeg3' => 'mp3',
            'audio/mp3' => 'mp3',
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'application/oda' => 'oda',
            'audio/ogg' => 'ogg',
            'video/ogg' => 'ogg',
            'application/ogg' => 'ogg',
            'font/otf' => 'otf',
            'application/x-pkcs10' => 'p10',
            'application/pkcs10' => 'p10',
            'application/x-pkcs12' => 'p12',
            'application/x-pkcs7-signature' => 'p7a',
            'application/pkcs7-mime' => 'p7c',
            'application/x-pkcs7-mime' => 'p7c',
            'application/x-pkcs7-certreqresp' => 'p7r',
            'application/pkcs7-signature' => 'p7s',
            'application/pdf' => 'pdf',
            'application/octet-stream' => 'pdf',
            'application/x-x509-user-cert' => 'pem',
            'application/x-pem-file' => 'pem',
            'application/pgp' => 'pgp',
            'application/x-httpd-php' => 'php',
            'application/php' => 'php',
            'application/x-php' => 'php',
            'text/php' => 'php',
            'text/x-php' => 'php',
            'application/x-httpd-php-source' => 'php',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'application/powerpoint' => 'ppt',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.ms-office' => 'ppt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop' => 'psd',
            'image/vnd.adobe.photoshop' => 'psd',
            'audio/x-realaudio' => 'ra',
            'audio/x-pn-realaudio' => 'ram',
            'application/x-rar' => 'rar',
            'application/rar' => 'rar',
            'application/x-rar-compressed' => 'rar',
            'audio/x-pn-realaudio-plugin' => 'rpm',
            'application/x-pkcs7' => 'rsa',
            'text/rtf' => 'rtf',
            'text/richtext' => 'rtx',
            'video/vnd.rn-realvideo' => 'rv',
            'application/x-stuffit' => 'sit',
            'application/smil' => 'smil',
            'text/srt' => 'srt',
            'image/svg+xml' => 'svg',
            'application/x-shockwave-flash' => 'swf',
            'application/x-tar' => 'tar',
            'application/x-gzip-compressed' => 'tgz',
            'image/tiff' => 'tiff',
            'font/ttf' => 'ttf',
            'text/plain' => 'txt',
            'text/x-vcard' => 'vcf',
            'application/videolan' => 'vlc',
            'text/vtt' => 'vtt',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/wav' => 'wav',
            'application/wbxml' => 'wbxml',
            'video/webm' => 'webm',
            'image/webp' => 'webp',
            'audio/x-ms-wma' => 'wma',
            'application/wmlc' => 'wmlc',
            'video/x-ms-wmv' => 'wmv',
            'video/x-ms-asf' => 'wmv',
            'font/woff' => 'woff',
            'font/woff2' => 'woff2',
            'application/xhtml+xml' => 'xhtml',
            'application/excel' => 'xl',
            'application/msexcel' => 'xls',
            'application/x-msexcel' => 'xls',
            'application/x-ms-excel' => 'xls',
            'application/x-excel' => 'xls',
            'application/x-dos_ms_excel' => 'xls',
            'application/xls' => 'xls',
            'application/x-xls' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xlsx',
            'application/xml' => 'xml',
            'text/xml' => 'xml',
            'text/xsl' => 'xsl',
            'application/xspf+xml' => 'xspf',
            'application/x-compress' => 'z',
            'application/x-zip' => 'zip',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'application/s-compressed' => 'zip',
            'multipart/x-zip' => 'zip',
            'text/x-scriptzsh' => 'zsh',
        ];  
        return isset($mime_map[$mime]) ? $mime_map[$mime] : false;
    }
}
