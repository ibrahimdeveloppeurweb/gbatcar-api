<?php

namespace App\Controller\Dashboard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/main", name="dashboard_main", methods={"GET"}, 
     * options={"description"="Statistiques générales du Gbatcar Dashboard", "permission"="DASHBOARD:MAIN"})
     */
    public function mainDashboard(Request $request)
    {
        // To be implemented: Fetch KPIs and charts data for main dashboard
        return $this->json([
            'data' => [
                'kpi' => [],
                'charts' => []
            ]
        ], 200);
    }

    /**
     * @Route("/admin", name="dashboard_admin", methods={"GET"}, 
     * options={"description"="Statistiques du tableau de bord d'administration", "permission"="DASHBOARD:ADMIN"})
     */
    public function adminDashboard(Request $request)
    {
        // To be implemented: Fetch KPIs and charts data for admin dashboard
        return $this->json([
            'data' => [
                'kpi' => [],
                'charts' => []
            ]
        ], 200);
    }
}