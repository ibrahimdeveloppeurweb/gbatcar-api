<?php

namespace App\Controller\Admin;

use App\Helpers\JsonHelper;
use App\Exception\ExceptionApi;
use App\Manager\Extra\NotificationSettingManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/admin/notifications")
 */
class NotificationController extends AbstractController
{
    private $settingManager;

    public function __construct(NotificationSettingManager $settingManager)
    {
        $this->settingManager = $settingManager;
    }

    /**
     * @Route("", name="admin_notifications_list", methods={"GET"}, 
     * options={"description"="Afficher les notifications systèmes", "permission"="NOTIFICATION:LIST"})
     */
    public function getNotifications(Request $request)
    {
        return $this->json(['data' => []], 200);
    }
}