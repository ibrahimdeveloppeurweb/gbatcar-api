<?php

namespace App\Controller\Client;

use App\Entity\Client\MaintenanceBudget;
use App\Repository\Client\MaintenanceBudgetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/private/maintenance/budgets")
 */
class MaintenanceBudgetController extends AbstractController
{
    private $budgetRepository;
    private $em;

    public function __construct(MaintenanceBudgetRepository $budgetRepository, EntityManagerInterface $em)
    {
        $this->budgetRepository = $budgetRepository;
        $this->em = $em;
    }

    /**
     * @Route("/", name="index_maintenance_budget", methods={"GET"},
     * options={"description"="Liste des budgets", "permission"="MAINTENANCE:BUDGET:LIST"})
     */
    public function index()
    {
        $items = $this->budgetRepository->findAll();
        return $this->json($items, 200, [], ['groups' => ["maintenance_budget"]]);
    }

    /**
     * @Route("/save", name="save_maintenance_budget", methods={"POST"},
     * options={"description"="Enregistrer un budget", "permission"="MAINTENANCE:BUDGET:SAVE"})
     */
    public function save(Request $request)
    {
        $data = json_decode($request->getContent());
        if (!isset($data->period) || !isset($data->amount)) {
            return $this->json(['message' => 'Période et montant requis'], 400);
        }

        $budget = $this->budgetRepository->findOneBy(['period' => $data->period]);
        if (!$budget) {
            $budget = new MaintenanceBudget();
            $budget->setPeriod($data->period);
        }

        $budget->setAmount((float)$data->amount);
        $this->em->persist($budget);
        $this->em->flush();

        return $this->json($budget, 200, [], ['groups' => ["maintenance_budget"]]);
    }
}