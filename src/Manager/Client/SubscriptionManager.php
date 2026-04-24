<?php

namespace App\Manager\Client;

use App\Entity\Client\Subscription;
use App\Mailing\SubscriptionMailing;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class SubscriptionManager
{
    private $em;
    private $uploadDir;
    private $subscriptionMailing;

    public function __construct(
        EntityManagerInterface $em,
        KernelInterface $kernel,
        SubscriptionMailing $subscriptionMailing
        )
    {
        $this->em = $em;
        $this->uploadDir = $kernel->getProjectDir() . '/public/uploads/subscriptions/';
        $this->subscriptionMailing = $subscriptionMailing;
    }

    public function create(Request $request): Subscription
    {
        $subscription = new Subscription();
        $subscription->setUuid(Uuid::uuid4());
        $this->hydrate($subscription, $request);
        $this->handleFiles($subscription, $request);

        $this->em->persist($subscription);
        $this->em->flush();

        $this->subscriptionMailing->subscriptionRequested($subscription);

        return $subscription;
    }

    private function handleFiles(Subscription $subscription, Request $request): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $fileKeys = [
            'permis', 'cni', 'casier', 'certif', 'bulletin',
            'rc', 'dfe', 'cniGerant', 'casierGerant', 'statut', 'releve'
        ];

        foreach ($fileKeys as $key) {
            $file = $request->files->get($key);
            if ($file && $file->isValid()) {
                $filename = uniqid() . '_' . $key . '_' . $file->getClientOriginalName();
                $file->move($this->uploadDir, $filename);
                $setter = 'set' . ucfirst($key);
                $subscription->$setter('/uploads/subscriptions/' . $filename);
            }
        }

        // Handle multiple photos
        $photos = $request->files->get('photos');
        if ($photos) {
            if (!is_array($photos)) {
                $photos = [$photos];
            }
            $paths = [];
            foreach ($photos as $i => $photo) {
                if ($photo && $photo->isValid()) {
                    $filename = uniqid() . '_photo' . $i . '_' . $photo->getClientOriginalName();
                    $photo->move($this->uploadDir, $filename);
                    $paths[] = '/uploads/subscriptions/' . $filename;
                }
            }
            if (!empty($paths)) {
                $subscription->setPhotos(implode(',', $paths));
            }
        }
    }

    private function hydrate(Subscription $subscription, Request $request): void
    {
        $data = $request->request;
        $clientType = $data->get('clientType', 'Particulier');

        $subscription->setClientType($clientType);
        $subscription->setFullName($data->get($clientType === 'Particulier' ? 'fullName' : 'managerName'));
        $subscription->setCompanyName($data->get('companyName'));

        if ($clientType === 'Particulier') {
            $subscription->setPhone($data->get('phoneParticulier', ''));
            $subscription->setEmail($data->get('emailParticulier', ''));
            $subscription->setLocation($data->get('locationParticulier', ''));
        }
        else {
            $subscription->setPhone($data->get('phoneEntreprise', ''));
            $subscription->setEmail($data->get('emailEntreprise', ''));
            $subscription->setLocation($data->get('locationEntreprise', ''));
        }

        $subscription->setProfession($data->get('profession'));
        $subscription->setMonthlyIncome($data->get('monthlyIncome'));
        $subscription->setTaxAccountNb($data->get('taxAccountNb'));
        $subscription->setVehicleType($data->get('vehicleType', ''));
        $subscription->setVehicleCount((int)$data->get('vehicleCount', 1));
        $subscription->setContractType($data->get('contractType', ''));
        $subscription->setPaymentMethod($data->get('paymentMethod', ''));
    }
}