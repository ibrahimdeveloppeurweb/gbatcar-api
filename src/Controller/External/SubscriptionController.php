<?php

namespace App\Controller\External;

use App\Helpers\JsonHelper;
use App\Manager\Client\SubscriptionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    private $subscriptionManager;

    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @Route("/api/subscription/public/new", name="subscription_public_new", methods={"POST"})
     */
    public function subscribe(Request $request): Response
    {
        try {
            $subscription = $this->subscriptionManager->create($request);
            $response = (new JsonHelper($subscription, 'Votre demande de souscription a été enregistrée avec succès.', 'success', 201))->serialize();
            return $this->json($response, 201, [], ['groups' => ['subscription']]);
        }
        catch (\Exception $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'error', 422))->serialize();
            return $this->json($response, 422);
        }
    }
}