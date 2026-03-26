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
        $filters = $request->query->all();
        $items = $this->paymentRepository->findByFilters($filters);
        return $this->json($items, 200, [], ['groups' => ["payment", "payment:contract", "contract:client"]]);
    }

    /**
     * @Route("/new", name="new_payment", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau payment", "permission"="PAYMENT:NEW"})
     */
    public function new (Request $request)
    {
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw) : (object)$request->request->all();
        if (!$data)
            $data = new \stdClass();

        try {
            $payment = $this->paymentManager->create($data, $request);
            return $this->json($payment, 201, [], ['groups' => ['payment', 'payment:contract', 'contract:client']]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la création du paiement.', 'details' => $e->getMessage()], 500);
        }
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
        return $this->json($item, 200, [], ['groups' => ["payment", "payment:contract", "contract:client"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_payment", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un payment", "permission"="PAYMENT:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
        $raw = $request->getContent();
        $data = $raw ? json_decode($raw) : (object)$request->request->all();
        if (!$data)
            $data = new \stdClass();

        try {
            $payment = $this->paymentManager->update($uuid, $data, $request);
            return $this->json($payment, 200, [], ['groups' => ['payment', 'payment:contract', 'contract:client']]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la modification.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/delete", name="delete_payment", methods={"DELETE"},
     * options={"description"="Supprimer un payment", "permission"="PAYMENT:DELETE"})
     */
    public function delete($uuid)
    {
        $payment = $this->paymentRepository->findOneByUuid($uuid);
        if (!$payment) {
            return $this->json(['message' => 'Paiement introuvable.'], 404);
        }

        try {
            $this->paymentManager->delete($payment);
            return $this->json(['message' => 'Paiement supprimé success'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la suppression.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/status", name="status_payment", methods={"POST", "PUT"},
     * options={"description"="Valider ou rejeter un paiement", "permission"="PAYMENT:CHANGE_STATUS"})
     */
    public function changeStatus(Request $request, $uuid)
    {
        $payment = $this->paymentRepository->findOneByUuid($uuid);
        if (!$payment) {
            return $this->json(['message' => 'Paiement introuvable.'], 404);
        }

        $raw = $request->getContent();
        $data = json_decode($raw);
        $status = $data->status ?? 'VALIDÉ';

        try {
            $payment = $this->paymentManager->toggleStatus($payment, $status);
            return $this->json($payment, 200, [], ['groups' => ['payment', 'payment:contract', 'contract:client']]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors du changement de statut.', 'details' => $e->getMessage()], 500);
        }
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