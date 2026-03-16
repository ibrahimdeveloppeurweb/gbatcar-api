<?php

namespace App\Controller\Apps;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/apps/chat")
 */
class ChatController extends AbstractController
{
    /**
     * @Route("", name="chat_messages_list", methods={"GET"}, 
     * options={"description"="Accès à la messagerie Web Chat", "permission"="CHAT:LIST"})
     */
    public function getMessages(Request $request)
    {
        return $this->json(['data' => []], 200);
    }
}
