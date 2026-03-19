<?php

namespace App\Controller\Extra;

use App\Helpers\JsonHelper;
use App\Exception\ExceptionApi;
use App\Manager\Extra\GeneralSettingManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/private/extra/settings")
 */
class GeneralSettingController extends AbstractController
{
    private $generalSettingManager;

    public function __construct(GeneralSettingManager $generalSettingManager)
    {
        $this->generalSettingManager = $generalSettingManager;
    }

    /**
     * @Route("/general", name="get_general_settings", methods={"GET"},
     * options={"description"="Récupérer la configuration générale", "permission"="SETTING:READ"})
     * @OA\Response(
     *     response=200,
     *     description="Return the global general settings",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="status", type="boolean", example="true"),
     *        @OA\Property(property="message", type="string", example="success"),
     *        @OA\Property(property="data", ref=@Model(type=App\Entity\Extra\GeneralSetting::class, groups={"setting"}))
     *     )
     * )
     * @OA\Tag(name="Extra - Settings")
     * @Security(name="Bearer")
     */
    public function getSettings(): JsonResponse
    {
        try {
            $setting = $this->generalSettingManager->getSettings();
            $response = (new JsonHelper($setting, null, 'success', 200, []))->serialize();
            return $this->json($response, 200, [], ['groups' => ['setting']]);
        }
        catch (ExceptionApi $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', $e->getCode(), $e->getErrors()))->serialize();
            return $this->json($response, $e->getCode(), [], ['groups' => ['setting']]);
        }
    }

    /**
     * @Route("/general/update", name="update_general_settings", methods={"POST", "PUT"},
     * options={"description"="Mettre à jour la configuration générale", "permission"="SETTING:EDIT"})
     * @OA\RequestBody(
     *    description="Update global general settings",
     *    required=true,
     *    @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="fraisDossier", type="integer", example=50000),
     *        @OA\Property(property="penaliteRetardJournaliere", type="number", example=1.5),
     *        @OA\Property(property="delaiGracePenalite", type="integer", example=3),
     *        @OA\Property(property="dureeContratDefautMois", type="integer", example=36),
     *        @OA\Property(property="apportInitialPourcentage", type="number", example=20),
     *        @OA\Property(property="reason", type="string", example="Promo de Mars")
     *    )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update the global general settings",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="status", type="boolean", example="true"),
     *        @OA\Property(property="message", type="string", example="Les paramètres généraux ont bien été mis à jour."),
     *        @OA\Property(property="data", ref=@Model(type=App\Entity\Extra\GeneralSetting::class, groups={"setting"}))
     *     )
     * )
     * @OA\Tag(name="Extra - Settings")
     * @Security(name="Bearer")
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), false);
            $setting = $this->generalSettingManager->updateOrCreate($data);
            $response = (new JsonHelper($setting, 'Paramètres enregistrés avec succès', 'success', 200, []))->serialize();
            return $this->json($response, 200, [], ['groups' => ['setting']]);
        }
        catch (\Exception $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', 500, []))->serialize();
            return $this->json($response, 500, [], ['groups' => ['setting']]);
        }
    }

    /**
     * @Route("/general/history", name="get_general_settings_history", methods={"GET"},
     * options={"description"="Récupérer l'historique des modifications", "permission"="SETTING:READ"})
     * @OA\Response(
     *     response=200,
     *     description="Return the global general settings history",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="status", type="boolean", example="true"),
     *        @OA\Property(property="message", type="string", example="success"),
     *        @OA\Property(property="data", type="array", @OA\Items(ref=@Model(type=App\Entity\Extra\GeneralSettingHistory::class, groups={"setting_history"})))
     *     )
     * )
     * @OA\Tag(name="Extra - Settings")
     * @Security(name="Bearer")
     */
    public function getHistory(): JsonResponse
    {
        try {
            $history = $this->generalSettingManager->getHistory();
            $response = (new JsonHelper($history, null, 'success', 200, []))->serialize();
            return $this->json($response, 200, [], ['groups' => ['setting_history']]);
        }
        catch (\Exception $e) {
            $response = (new JsonHelper(null, $e->getMessage(), 'bad_request', 500, []))->serialize();
            return $this->json($response, 500, [], ['groups' => ['setting_history']]);
        }
    }
}