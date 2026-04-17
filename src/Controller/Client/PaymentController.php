<?php

namespace App\Controller\Client;

use App\Entity\Client\PaymentDocument;
use App\Manager\Client\PaymentManager;
use App\Repository\Client\PaymentDocumentRepository;
use App\Repository\Client\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Manager\Admin\AuditLogManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/payment")
 */
class PaymentController extends AbstractController
{
    private $paymentRepository;
    private $paymentManager;
    private $em;
    private $documentRepository;
    private $auditLogManager;

    public function __construct(
        PaymentRepository $paymentRepository,
        PaymentManager $paymentManager,
        EntityManagerInterface $em,
        PaymentDocumentRepository $documentRepository,
        AuditLogManager $auditLogManager
        )
    {
        $this->paymentRepository = $paymentRepository;
        $this->paymentManager = $paymentManager;
        $this->em = $em;
        $this->documentRepository = $documentRepository;
        $this->auditLogManager = $auditLogManager;
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
     * @Route("/dashboard", name="dashboard_payment", methods={"GET"}, 
     * options={"description"="Statistiques financières du tableau de bord", "permission"="PAYMENT:DASHBOARD"})
     */
    public function dashboard(Request $request)
    {
        $months = (int)$request->query->get('months', 6);
        $data = $this->paymentRepository->getDashboardMetrics($months);
        return $this->json($data, 200);
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

            $this->auditLogManager->log(
                'Paiement',
                'Encaissement',
                sprintf('Enregistrement d\'un paiement de %s FCFA (%s)', number_format($payment->getAmount(), 0, ',', ' '), $payment->getMethod())
            );

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

            $this->auditLogManager->log(
                'Paiement',
                'Modification',
                sprintf('Modification d\'un paiement de %s FCFA', number_format($payment->getAmount(), 0, ',', ' '))
            );

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
            $amount = $payment->getAmount();
            $this->paymentManager->delete($payment);

            $this->auditLogManager->log(
                'Paiement',
                'Suppression',
                sprintf('Suppression d\'un paiement de %s FCFA', number_format($amount, 0, ',', ' '))
            );

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

            $this->auditLogManager->log(
                'Paiement',
                $status === 'ANNULÉ' ? 'Annulation' : 'Validation',
                sprintf('Le statut du paiement est passé à %s', $status)
            );

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

    /**
     * @Route("/{uuid}/documents", name="upload_payment_document", methods={"POST"},
     * options={"description"="Upload document(s) pour un paiement", "permission"="PAYMENT:DOCUMENT"})
     */
    public function uploadDocument(Request $request, string $uuid)
    {
        $payment = $this->paymentRepository->findOneByUuid($uuid);
        if (!$payment) {
            return $this->json(['message' => 'Paiement introuvable'], 404);
        }

        $files = $request->files->get('files');
        if (!$files) {
            return $this->json(['message' => 'Aucun fichier reçu'], 400);
        }
        if (!is_array($files)) {
            $files = [$files];
        }

        $libelle = $request->request->get('libelle', '');
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/payment/' . $uuid . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $saved = [];
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $size = $file->getSize();
            $storedName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

            $file->move($uploadDir, $storedName);

            $doc = new PaymentDocument();
            $doc->setLibelle($libelle ?: $originalName);
            $doc->setOriginalName($originalName);
            $doc->setStoredName($storedName);
            $doc->setMimeType($mimeType);
            $doc->setSize($size);
            $doc->setPayment($payment);
            $this->em->persist($doc);
            $saved[] = ['name' => $originalName, 'uuid' => $doc->getUuid()];
        }
        $this->em->flush();
        return $this->json(['message' => count($saved) . ' fichier(s) ajouté(s)', 'files' => $saved], 201);
    }

    /**
     * @Route("/{pUuid}/documents/{dUuid}/download", name="download_payment_document", methods={"GET"},
     * options={"description"="Télécharger un document d'un paiement", "permission"="PAYMENT:DOCUMENT"})
     */
    public function downloadDocument(string $pUuid, string $dUuid)
    {
        try {
            $doc = $this->documentRepository->findOneBy(['uuid' => $dUuid]);
            if (!$doc) {
                return $this->json(['message' => 'Document introuvable'], 404);
            }
            $path = $this->getParameter('kernel.project_dir') . '/public/uploads/payment/' . $pUuid . '/' . $doc->getStoredName();
            if (!file_exists($path)) {
                return $this->json(['message' => 'Fichier introuvable sur le serveur'], 404);
            }
            $response = new BinaryFileResponse($path);
            if ($doc->getMimeType()) {
                $response->headers->set('Content-Type', $doc->getMimeType());
            }
            $fallbackName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $doc->getOriginalName());
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $doc->getOriginalName(), $fallbackName);
            return $response;
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{pUuid}/documents/{dUuid}/delete", name="delete_payment_document", methods={"DELETE"},
     * options={"description"="Supprimer un document d'un paiement", "permission"="PAYMENT:DOCUMENT"})
     */
    public function deleteDocument(string $pUuid, string $dUuid)
    {
        $doc = $this->documentRepository->findOneBy(['uuid' => $dUuid]);
        if (!$doc) {
            return $this->json(['message' => 'Document introuvable'], 404);
        }
        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/payment/' . $pUuid . '/' . $doc->getStoredName();
        if (file_exists($path)) {
            @unlink($path);
        }
        $this->em->remove($doc);
        $this->em->flush();
        return $this->json(['message' => 'Document supprimé'], 200);
    }
}