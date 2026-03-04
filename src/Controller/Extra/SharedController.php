<?php

namespace App\Controller\Extra;

use App\Helpers\JsonHelper;
use App\Manager\Extra\SharedManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/extra/shared")
 */
class SharedController extends AbstractController
{
    /**
     * @Route("/", name="shared_extra", methods={"POST"}, 
     * options={"description"="Recherche approfondie"})
     */
    public function index(Request $request, SharedManager $manager): Response
    {
        $data = json_decode($request->getContent(), true);
        $result = $manager->search($data);
        $response = (new JsonHelper($result, 'Recherche effectuée', 'success', 200, []))->serialize();
        return $this->json($response, 200, [], ['groups' => $manager->getGroups()]);
    }
}
