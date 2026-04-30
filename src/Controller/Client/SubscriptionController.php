<?php

namespace App\Controller\Client;

use App\Repository\Client\SubscriptionRepository;
use App\Manager\Admin\AuditLogManager;
use App\Mailing\SubscriptionMailing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/subscription")
 */
class SubscriptionController extends AbstractController
{
    private $subscriptionRepository;
    private $auditLogManager;
    private $subscriptionMailing;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        AuditLogManager $auditLogManager,
        SubscriptionMailing $subscriptionMailing
        )
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->auditLogManager = $auditLogManager;
        $this->subscriptionMailing = $subscriptionMailing;
    }

    /**
     * @Route("/", name="index_subscription", methods={"GET"},
     * options={"description"="Liste des souscriptions", "permission"="SUBSCRIPTION:LIST"})
     */
    public function index(Request $request)
    {
        // On récupère toutes les souscriptions, triées par la plus récente
        // Note : on pourrait intégrer des filtres ici si nécessaire
        $items = $this->subscriptionRepository->findBy([], ['id' => 'DESC']);
        return $this->json($items, 200, [], $this->getContext(["subscription"]));
    }

    /**
     * @Route("/{uuid}/show", name="show_subscription", methods={"GET"}, 
     * options={"description"="Détails d'une souscription", "permission"="SUBSCRIPTION:SHOW"})
     */
    public function show($uuid)
    {
        $item = $this->subscriptionRepository->findOneByUuid($uuid);
        if (!$item) {
            return $this->json(['message' => 'Not found'], 404);
        }
        return $this->json($item, 200, [], $this->getContext(["subscription"]));
    }

    /**
     * @Route("/{uuid}/delete", name="delete_subscription", methods={"DELETE"},
     * options={"description"="Supprimer une souscription", "permission"="SUBSCRIPTION:DELETE"})
     */
    public function delete($uuid)
    {
        $subscription = $this->subscriptionRepository->findOneByUuid($uuid);
        if (!$subscription) {
            return $this->json(['message' => 'Souscription introuvable.'], 404);
        }

        try {
            $name = $subscription->getFullName() ?: $subscription->getCompanyName();

            // Soft delete via l'EntityManager (qui gérera le deletedAt)
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($subscription);
            $entityManager->flush();

            $this->auditLogManager->log(
                'Souscription',
                'Suppression',
                sprintf('Suppression de la souscription de : %s', $name)
            );

            return $this->json(['message' => 'Souscription supprimée avec succès.'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la suppression.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/reject", name="reject_subscription", methods={"POST"},
     * options={"description"="Refuser une souscription", "permission"="SUBSCRIPTION:REJECT"})
     */
    public function reject($uuid, Request $request)
    {
        $subscription = $this->subscriptionRepository->findOneByUuid($uuid);
        if (!$subscription) {
            return $this->json(['message' => 'Souscription introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $reason = $data['reason'] ?? null;

        try {
            $subscription->setStatus('REJETÉ');
            $subscription->setRejectionReason($reason);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Envoi de l'email de notification (optionnel, on ne bloque pas si le serveur mail est éteint en dev)
            try {
                $this->subscriptionMailing->subscriptionRejected($subscription, $reason);
            }
            catch (\Exception $mailEx) {
                // On log l'erreur mail mais on laisse le reste du processus continuer
                error_log("Mailing error during subscription rejection: " . $mailEx->getMessage());
            }

            $this->auditLogManager->log(
                'Souscription',
                'Refus',
                sprintf('Refus de la souscription de : %s. Motif : %s', $subscription->getFullName(), $reason)
            );

            return $this->json(['message' => 'Souscription rejetée avec succès.'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors du rejet.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/{uuid}/validate", name="validate_subscription", methods={"POST"},
     * options={"description"="Valider une souscription", "permission"="SUBSCRIPTION:VALIDATE"})
     */
    public function validate($uuid)
    {
        $subscription = $this->subscriptionRepository->findOneByUuid($uuid);
        if (!$subscription) {
            return $this->json(['message' => 'Souscription introuvable.'], 404);
        }

        try {
            $entityManager = $this->getDoctrine()->getManager();

            // 1. Mark subscription as validated
            $subscription->setStatus('VALIDÉ');

            // 2. Create a new Client from Subscription data
            $client = new \App\Entity\Client\Client();

            $client->setType($subscription->getClientType());
            $client->setProfession($subscription->getProfession());

            // Identify whether Particulier or Entreprise to spread names correctly
            if ($subscription->getClientType() === 'Particulier') {
                $client->setName($subscription->getFullName());
                $nameParts = explode(' ', $subscription->getFullName() ?? '', 2);
                $client->setFirstName($nameParts[0] ?? '');
                $client->setLastName($nameParts[1] ?? '');
            }
            else {
                $client->setManagerName($subscription->getFullName());
                $client->setCompanyName($subscription->getCompanyName());
                $client->setTaxAccountNb($subscription->getTaxAccountNb());
                $client->setName($subscription->getCompanyName());
            }

            $client->setEmail($subscription->getEmail());
            $client->setPhone($subscription->getPhone());
            $client->setAddress($subscription->getLocation());

            // Documents transfer
            $client->setIdScanUrl($subscription->getCni());
            $client->setLicenseScanUrl($subscription->getPermis());
            $client->setPhoto($subscription->getPhotos());

            $client->setCasierUrl($subscription->getCasier());
            $client->setCertifResidenceUrl($subscription->getCertif());
            $client->setBulletinSalaireUrl($subscription->getBulletin());

            // Enterprise Documents transfer
            $client->setRcUrl($subscription->getRc());
            $client->setDfeUrl($subscription->getDfe());
            $client->setStatutUrl($subscription->getStatut());
            $client->setCniGerantUrl($subscription->getCniGerant());
            $client->setCasierGerantUrl($subscription->getCasierGerant());
            $client->setReleveBancaireUrl($subscription->getReleve());

            // Finalize client
            $client->setStatus('En attente de Validation');

            $entityManager->persist($client);
            $entityManager->flush();

            $this->auditLogManager->log(
                'Souscription',
                'Validation',
                sprintf('Validation de la souscription de : %s. Client créé avec succès.', $subscription->getFullName())
            );

            return $this->json(['message' => 'Souscription validée et client créé avec succès.'], 200);
        }
        catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la validation.', 'details' => $e->getMessage()], 500);
        }
    }

    private function getContext(array $groups = ["subscription"]): array
    {
        return [
            'groups' => $groups,
            'circular_reference_handler' => function ($object) {
            return method_exists($object, 'getId') ? $object->getId() : null;
        }
        ];
    }
}