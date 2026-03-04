<?php

namespace App\Controller\Extra;

use App\Helpers\JsonHelper;
use App\Entity\Extra\Folder;
use App\Exception\ExceptionApi;
use App\Manager\Extra\UploaderManager;
use App\Repository\Extra\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UploaderController extends AbstractController
{
    private $em;
    private $uploadManager;
    private $fileRepository;
    public function __construct(
        EntityManagerInterface $em,
        UploaderManager $uploadManager,
        FileRepository $fileRepository
    ) {
        $this->em = $em;
         $this->uploadManager = $uploadManager;
        $this->fileRepository = $fileRepository;
    }

    /**
     * @Route("/api/uploader", name="uploader", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function uploader(Request $request): Response
    {
        try {
            $result = $this->uploadManager->folder($request); 
            // $result = null; 
            $response = (new JsonHelper(json_decode(json_encode($result)), 'Image ajouté avec succès', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(),'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['default','file','photo','folder']]);
        } 
        return $this->json($response, 200, []);
    }

    /**
     * @Route("/api/uploader/create/folder", name="uploader_folder_creation")
     * @param Request $request
     * @return Response
     */
    public function createFolder(Request $request): Response
    {
        $folder = new Folder();
        $this->em->persist($folder);
        $this->em->flush();
        return $this->json(['status' => 'success', 'uuid' => (string)$folder->getUuid()], 200);
    }

    /**
     * @Route("/api/uploader/folder/delete", name="delete_file_folder")
     * @param Request $request
     * @return Response
     */
    public function deleteFolder(Request $request): Response
    {
        try {
            $data = \json_decode($request->getContent());
            $this->uploadManager->deleteFolder($data->uuid, $data->path, $this->getUser()->getAgency());
            $this->em->flush(); 
            $response = (new JsonHelper(null, 'Image Supprimer avec succès', 'success', 200, []))->serialize();
        } catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(),'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['default','file','photo','folder']]);
        }
        return $this->json($response, 200, [], ['groups' => ['default','file','photo','folder']]);
    }

    /**
     * @Route("/api/uploader/file/delete", name="delete_file")
     * @param Request $request
     * @return Response
     */
    public function deleteFile(Request $request): Response
    {
        try{
            $data = \json_decode($request->getContent());
            $file = $this->fileRepository->findOneBySrc($data->src);
            $path = __DIR__ . '/../../public/uploads/' . $this->getUser()->getAgency()->getUuid() . '_' .str_replace(' ', '', $this->getUser()->getAgency()->getNom()). '/'.$data->path. '/'.$data->src;
            $this->uploadManager->deleteFile($path);
            $this->em->remove($file);
            $this->em->flush();
            $response = (new JsonHelper(null, 'Image Supprimer avec succès', 'success', 200, []))->serialize(); 
        } catch(ExceptionApi $e){
            $response = (new JsonHelper(null, $e->getMessage(),'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['default','file','photo','folder']]);
        }
        return $this->json($response, 200, [], ['groups' => ['default','file','photo','folder']]);
    }
}
