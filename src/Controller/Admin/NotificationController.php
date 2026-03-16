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

    /**
     * @Route("/settings", name="get_notification_settings", methods={"GET"}, 
     * options={"description"="Récupérer la configuration des notifications", "permission"="NOTIFICATION:SETTINGS_READ"})
     */
    public function getSettings()
    {
        try {
            $setting = $this->settingManager->getSettings();
            $response = (new JsonHelper($setting, null, 'success', 200, []))->serialize();
            return $this->json($response, 200, [], ['groups' => ['setting']]);
        }
        catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['setting']]);
        }
    }

    /**
     * @Route("/settings/update", name="update_notification_settings", methods={"POST", "PUT"},
     * options={"description"="Mettre à jour la configuration des notifications", "permission"="NOTIFICATION:SETTINGS_EDIT"})
     */
    public function update(Request $request)
    {
        try {
            $data = json_decode($request->getContent());
            $setting = $this->settingManager->updateOrCreate($data);
            $response = (new JsonHelper($setting, 'Paramètres enregistrés avec succès', 'success', 200, []))->serialize();
            return $this->json($response, 200, [], ['groups' => ['setting']]);
        }
        catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['setting']]);
        }
    }
}
