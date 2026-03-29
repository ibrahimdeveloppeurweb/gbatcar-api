<?php

namespace App\Controller\Client;

use App\Entity\Client\ContractDocument;
use App\Manager\Client\ContractManager;
use App\Repository\Client\ContractRepository;
use App\Repository\Client\ContractDocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Client\PromiseToPay;
use App\Repository\Client\PromiseToPayRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/contract")
 */
class ContractController extends AbstractController
{
    private $contractRepository;
    private $contractManager;
    private $em;
    private $documentRepository;
    private $promiseRepository;

    public function __construct(
        ContractRepository $contractRepository,
        ContractManager $contractManager,
        EntityManagerInterface $em,
        ContractDocumentRepository $documentRepository,
        PromiseToPayRepository $promiseRepository
        )
    {
        $this->contractRepository = $contractRepository;
        $this->contractManager = $contractManager;
        $this->em = $em;
        $this->documentRepository = $documentRepository;
        $this->promiseRepository = $promiseRepository;
    }

    /**
     * @Route("/", name="index_contract", methods={"GET"},
     * options={"description"="Liste des contract", "permission"="CONTRACT:LIST"})
     */
    public function index(Request $request)
    {
        $filters = $request->query->all();
        $items = $this->contractRepository->findByFilters($filters);
        return $this->json($items, 200, [], ['groups' => ["contract", "contract:client", "contract:payments", "contract:promises"]]);
    }

    /**
     * @Route("/new", name="new_contract", methods={"POST"}, 
     * options={"description"="Ajouter un nouveau contract", "permission"="CONTRACT:NEW"})
     */
    public function new (Request $request)
    {
        $data = json_decode($request->getContent());
        $item = $this->contractManager->create($data);
        return $this->json($item, 201, [], ['groups' => ["contract", "contract:client", "contract:payments", "contract:promises"]]);
    }

    /**
     * @Route("/dashboard", name="dashboard_contract", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord", "permission"="CONTRACT:DASHBOARD"})
     */
    public function dashboard()
    {
        // To be implemented
        return $this->json([], 200);
    }

    /**
     * @Route("/{uuid}/show", name="show_contract", methods={"GET"}, 
     * options={"description"="Détails d'un contract", "permission"="CONTRACT:SHOW"})
     */
    public function show($uuid)
    {
        $item = $this->contractRepository->findOneByUuid($uuid);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        return $this->json($item, 200, [], ['groups' => ["contract", "contract:client", "contract:payments", "contract:promises"]]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_contract", methods={"PUT", "POST"}, 
     * options={"description"="Modifier un contract", "permission"="CONTRACT:EDIT"})
     */
    public function edit(Request $request, $uuid)
    {
        $data = json_decode($request->getContent());
        try {
            $item = $this->contractManager->update($uuid, $data);
            return $this->json($item, 200, [], ['groups' => ["contract", "contract:client", "contract:payments", "contract:promises"]]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la modification : ' . $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/{uuid}/delete", name="delete_contract", methods={"DELETE"},
     * options={"description"="Supprimer un contract", "permission"="CONTRACT:DELETE"})
     */
    public function delete($uuid)
    {
        $item = $this->contractRepository->findOneBy(['uuid' => $uuid]);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        try {
            $this->contractManager->delete($item);
            return $this->json(['message' => 'Deleted successfully'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la suppression : ' . $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/{uuid}/validate", name="validate_contract", methods={"POST", "PUT"},
     * options={"description"="Valider un contrat", "permission"="CONTRACT:VALIDATE"})
     */
    public function validateContract(Request $request, $uuid)
    {
        $item = $this->contractRepository->findOneBy(['uuid' => $uuid]);
        if (!$item) {
            return $this->json(['message' => 'Contrat introuvable'], 404);
        }

        try {
            $this->contractManager->validate($item);
            return $this->json($item, 200, [], ['groups' => ["contract", "contract:client", "contract:payments", "contract:promises"]]);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la validation : ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/pdf", name="pdf_contract", methods={"GET"},
     * options={"description"="Générer PDF d'un contrat", "permission"="CONTRACT:PDF"})
     */
    public function generatePdf($uuid)
    {
    // To be implemented
    }

    /**
     * @Route("/late", name="contract_late_list", methods={"GET"}, 
     * options={"description"="Afficher le suivi des retards", "permission"="CONTRACT:LATE:LIST"})
     */
    public function getLateList(Request $request)
    {
        $filters = $request->query->all();
        $contracts = $this->contractRepository->findLateContracts($filters);

        $totalArrears = 0;
        $criticalCasesCount = 0;
        $today = new \DateTimeImmutable('today');

        foreach ($contracts as $contract) {
            $analysis = $contract->getRiskAnalysis();
            if ($analysis['level'] === 'CRITIQUE') {
                $criticalCasesCount++;
            }

            // Calculate unpaid arrears for this contract from its schedules
            foreach ($contract->getPaymentSchedules() as $schedule) {
                if ($schedule->getStatus() !== 'Payé' && $schedule->getExpectedDate() < $today) {
                    $totalArrears += ($schedule->getAmount() - ($schedule->getPaidAmount() ?: 0));
                }
            }
        }

        return $this->json([
            'kpis' => [
                'totalArrears' => $totalArrears,
                'criticalCasesCount' => $criticalCasesCount,
                'promiseToPayCount' => $this->promiseRepository->findPendingPromisesCount()
            ],
            'contracts' => $contracts
        ], 200, [], ['groups' => ["contract", "contract:client", "contract:payments", "contract:promises"]]);
    }

    /**
     * @Route("/{uuid}/promises/new", name="new_promise", methods={"POST"},
     * options={"description"="Ajouter une promesse de paiement", "permission"="CONTRACT:PROMISE"})
     */
    public function addPromise(Request $request, string $uuid)
    {
        $contract = $this->contractRepository->findOneByUuid($uuid);
        if (!$contract) {
            return $this->json(['message' => 'Contrat introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['expectedDate'])) {
            return $this->json(['message' => 'La date est obligatoire'], 400);
        }

        $promise = new PromiseToPay();
        $promise->setContract($contract);
        $promise->setExpectedDate(new \DateTimeImmutable($data['expectedDate']));
        $promise->setAmount(isset($data['amount']) ? (float)$data['amount'] : null);
        $promise->setNote($data['note'] ?? null);
        $promise->setStatus(PromiseToPay::STATUS['PENDING']);

        $this->em->persist($promise);
        $this->em->flush();

        return $this->json($promise, 201, [], ['groups' => ["promise"]]);
    }

    /**
     * @Route("/{uuid}/documents", name="upload_contract_document", methods={"POST"},
     * options={"description"="Upload document(s) pour un contrat", "permission"="CONTRACT:DOCUMENT"})
     */
    public function uploadDocument(Request $request, string $uuid)
    {
        $contract = $this->contractRepository->findOneByUuid($uuid);
        if (!$contract) {
            return $this->json(['message' => 'Contrat introuvable'], 404);
        }

        $files = $request->files->get('files');
        $libelle = $request->request->get('libelle');

        if (!$files) {
            return $this->json(['message' => 'Aucun fichier reçu'], 400);
        }
        if (!is_array($files)) {
            $files = [$files];
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/contract/' . $uuid . '/';
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

            $doc = new ContractDocument();
            $doc->setLibelle($libelle);
            $doc->setOriginalName($originalName);
            $doc->setStoredName($storedName);
            $doc->setMimeType($mimeType);
            $doc->setSize($size);
            $doc->setContract($contract);
            $this->em->persist($doc);
            $saved[] = ['name' => $originalName, 'uuid' => $doc->getUuid()];
        }
        $this->em->flush();
        return $this->json(['message' => count($saved) . ' fichier(s) ajouté(s)', 'files' => $saved], 201);
    }

    /**
     * @Route("/{cUuid}/documents/{dUuid}/download", name="download_contract_document", methods={"GET"},
     * options={"description"="Télécharger un document du contrat", "permission"="CONTRACT:DOCUMENT"})
     */
    public function downloadDocument(string $cUuid, string $dUuid)
    {
        try {
            $doc = $this->documentRepository->findOneBy(['uuid' => $dUuid]);
            if (!$doc) {
                return $this->json(['message' => 'Document introuvable'], 404);
            }
            $path = $this->getParameter('kernel.project_dir') . '/public/uploads/contract/' . $cUuid . '/' . $doc->getStoredName();
            if (!file_exists($path)) {
                return $this->json(['message' => 'Fichier introuvable sur le serveur'], 404);
            }
            $response = new BinaryFileResponse($path);

            if ($doc->getMimeType()) {
                $response->headers->set('Content-Type', $doc->getMimeType());
            }

            $fallbackName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $doc->getOriginalName());

            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $doc->getOriginalName(),
                $fallbackName
            );
            return $response;
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors du téléchargement : ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{cUuid}/documents/{dUuid}/delete", name="delete_contract_document", methods={"DELETE"},
     * options={"description"="Supprimer un document du contrat", "permission"="CONTRACT:DOCUMENT"})
     */
    public function deleteDocument(string $cUuid, string $dUuid)
    {
        $doc = $this->documentRepository->findOneBy(['uuid' => $dUuid]);
        if (!$doc) {
            return $this->json(['message' => 'Document introuvable'], 404);
        }
        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/contract/' . $cUuid . '/' . $doc->getStoredName();
        if (file_exists($path)) {
            @unlink($path);
        }
        $this->em->remove($doc);
        $this->em->flush();
        return $this->json(['message' => 'Document supprimé'], 200);
    }
}