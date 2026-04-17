<?php

namespace App\Controller\Dashboard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Manager\Admin\DashboardManager;

/**
 * @Route(path="/api/private/dashboard")
 */
class DashboardController extends AbstractController
{
    private $dashboardManager;

    public function __construct(DashboardManager $dashboardManager)
    {
        $this->dashboardManager = $dashboardManager;
    }

    /**
     * @Route("/main", name="dashboard_main", methods={"GET"}, 
     * options={"description"="Statistiques générales du Gbatcar Dashboard", "permission"="DASHBOARD:MAIN"})
     */
    public function mainDashboard(Request $request)
    {
        $months = (int)$request->query->get('month', 6);
        $data = $this->dashboardManager->getMainDashboardData($months);

        return $this->json([
            'data' => $data
        ], 200);
    }

    /**
     * @Route("/admin", name="dashboard_admin", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord d'administration", "permission"="DASHBOARD:ADMIN"})
     */
    public function adminDashboard(Request $request)
    {
        $data = $this->dashboardManager->getAdminDashboardData();

        return $this->json([
            'data' => $data
        ], 200);
    }
}