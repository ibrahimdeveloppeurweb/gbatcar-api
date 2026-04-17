<?php

namespace App\Controller\Admin;

use App\Repository\Admin\AuditLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/private/audit-logs")
 */
class AuditLogController extends AbstractController
{
    /**
     * @Route("", name="audit_logs_list", methods={"GET"})
     */
    public function list(Request $request, AuditLogRepository $auditLogRepository): Response
    {
        $limit = $request->query->getInt('limit', 20);
        $offset = $request->query->getInt('offset', 0);

        $logs = $auditLogRepository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $total = $auditLogRepository->count([]);

        return $this->json([
            'data' => $logs,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ], 200, [], ['groups' => ['audit:read']]);
    }
}