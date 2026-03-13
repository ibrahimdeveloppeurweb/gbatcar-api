<?php

namespace App\Manager\Client;

use App\Entity\Client\Payment;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\PaymentRepository;

class PaymentManager
{
    private $em;
    private $paymentRepository;

    public function __construct(
        EntityManagerInterface $em,
        PaymentRepository $paymentRepository
    ) {
        $this->em = $em;
        $this->paymentRepository = $paymentRepository;
    }

    public function create(object $data): Payment
    {
        // To be implemented
        return new Payment();
    }

    public function update(string $uuid, object $data): Payment
    {
        // To be implemented
        return new Payment();
    }

    public function delete(Payment $payment): Payment
    {
        // To be implemented
        return $payment;
    }
}
