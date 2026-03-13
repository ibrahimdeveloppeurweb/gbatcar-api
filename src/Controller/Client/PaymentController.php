<?php

namespace App\Controller\Client;

use App\Manager\Client\PaymentManager;
use App\Repository\Client\PaymentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/payment")
 */
class PaymentController extends AbstractController
{
    private $paymentRepository;
    private $paymentManager;

    public function __construct(
        PaymentRepository $paymentRepository,
        PaymentManager $paymentManager
        )
    {
        $this->paymentRepository = $paymentRepository;
        $this->paymentManager = $paymentManager;
    }

    /**
     * @Route("/", name="index_payment", methods={"GET"},
     * options={"description"="Liste des payment", "permission"="PAYMENT:LIST"})
     */
    public function index(Request $request)
    {
        $items = $this->paymentRepository->findAll();
        return $this->json($items, 200, [], ['groups' => ["payment"]]);
    }

    /**
     * @Route("/new", name="new_payment", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau payment", "permission"="PAYMENT:NEW"})
     */
    public function new (Request $request)
    {
    // To be implemented with PaymentManager
    }

    /**
     * @Route("/{uuid}/show", name="show_payment", methods={"GET"}, 
     * options={"description"="Détails d'un payment", "permission"="PAYMENT:SHOW"})
     */
    public function show($uuid)
    {
        $item = $this->paymentRepository->findOneByUuid($uuid);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        return $this->json($item, 200, [], ['groups' => ["payment"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_payment", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un payment", "permission"="PAYMENT:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
    // To be implemented with PaymentManager
    }

    /**
     * @Route("/{uuid}/delete", name="delete_payment", methods={"DELETE"},
     * options={"description"="Supprimer un payment", "permission"="PAYMENT:DELETE"})
     */
    public function delete($uuid)
    {
    // To be implemented with PaymentManager
    }

    /**
     * @Route("/{uuid}/status", name="status_payment", methods={"POST", "PUT"},
     * options={"description"="Valider ou rejeter un paiement", "permission"="PAYMENT:CHANGE_STATUS"})
     */
    public function changeStatus(Request $request, $uuid)
    {
    // To be implemented
    }

    /**
     * @Route("/{uuid}/receipt", name="receipt_payment", methods={"GET"},
     * options={"description"="Imprimer le reçu d'un paiement", "permission"="PAYMENT:RECEIPT"})
     */
    public function generateReceipt($uuid)
    {
    // To be implemented
    }
}