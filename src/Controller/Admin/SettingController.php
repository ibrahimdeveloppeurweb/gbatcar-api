<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/admin/settings")
 */
class SettingController extends AbstractController
{
    /**
     * @Route("", name="admin_settings_list", methods={"GET"}, 
     * options={"description"="Afficher les paramètres globaux", "permission"="SETTING:LIST"})
     */
    public function getSettings(Request $request)
    {
        return $this->json(['data' => []], 200);
    }

    /**
     * @Route("", name="admin_settings_edit", methods={"PUT"}, 
     * options={"description"="Modifier les paramètres globaux", "permission"="SETTING:EDIT"})
     */
    public function updateSettings(Request $request)
    {
        return $this->json(['message' => 'Updated'], 200);
    }
}
