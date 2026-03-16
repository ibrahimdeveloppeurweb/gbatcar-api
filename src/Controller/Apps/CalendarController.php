<?php

namespace App\Controller\Apps;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/apps/calendar")
 */
class CalendarController extends AbstractController
{
    /**
     * @Route("", name="calendar_events_list", methods={"GET"}, 
     * options={"description"="Accès aux Événements du Calendrier", "permission"="CALENDAR:LIST"})
     */
    public function getEvents(Request $request)
    {
        return $this->json(['data' => []], 200);
    }

    /**
     * @Route("", name="calendar_events_new", methods={"POST"}, 
     * options={"description"="Créer un nouvel événement", "permission"="CALENDAR:NEW"})
     */
    public function createEvent(Request $request)
    {
        return $this->json(['message' => 'Created'], 201);
    }
}
