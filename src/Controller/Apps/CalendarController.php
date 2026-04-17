<?php

namespace App\Controller\Apps;

use App\Entity\Apps\CalendarEvent;
use App\Repository\Apps\CalendarEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Manager\Admin\AuditLogManager;
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
    public function getEvents(CalendarEventRepository $repo)
    {
        $events = $repo->findAll();
        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'id' => (string)$event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getStartDate() ? $event->getStartDate()->format('Y-m-d\TH:i:s') : null,
                'end' => $event->getEndDate() ? $event->getEndDate()->format('Y-m-d\TH:i:s') : null,
                'allDay' => $event->getAllDay(),
                'backgroundColor' => $event->getBackgroundColor(),
                'borderColor' => $event->getBorderColor(),
            ];
        }

        return $this->json(['data' => $data], 200);
    }

    /**
     * @Route("", name="calendar_events_new", methods={"POST"}, 
     * options={"description"="Créer un nouvel événement", "permission"="CALENDAR:NEW"})
     */
    public function createEvent(Request $request, EntityManagerInterface $em, AuditLogManager $auditLogManager)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title']) || empty($data['start'])) {
            return $this->json(['message' => 'Missing required fields (title, start)'], 400);
        }

        $event = new CalendarEvent();
        $event->setTitle($data['title']);
        $event->setStartDate(new \DateTime($data['start']));

        if (!empty($data['end'])) {
            $event->setEndDate(new \DateTime($data['end']));
        }

        if (isset($data['allDay'])) {
            $event->setAllDay((bool)$data['allDay']);
        }

        if (!empty($data['backgroundColor'])) {
            $event->setBackgroundColor($data['backgroundColor']);
        }

        if (!empty($data['borderColor'])) {
            $event->setBorderColor($data['borderColor']);
        }

        $em->persist($event);
        $em->flush();

        $auditLogManager->log(
            'Calendrier',
            'Création',
            sprintf('Nouvel événement "%s" programmé pour le %s', $event->getTitle(), $event->getStartDate()->format('d/m/Y H:i:s'))
        );

        return $this->json([
            'message' => 'Event Created',
            'id' => (string)$event->getId()
        ], 201);
    }

    /**
     * @Route("/{id}", name="calendar_events_update", methods={"PUT"}, 
     * options={"description"="Modifier un événement", "permission"="CALENDAR:EDIT"})
     */
    public function updateEvent(int $id, Request $request, CalendarEventRepository $repo, EntityManagerInterface $em, AuditLogManager $auditLogManager)
    {
        $event = $repo->find($id);
        if (!$event) {
            return $this->json(['message' => 'Event not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!empty($data['title'])) {
            $event->setTitle($data['title']);
        }

        if (!empty($data['start'])) {
            $event->setStartDate(new \DateTime($data['start']));
        }

        if (!empty($data['end'])) {
            $event->setEndDate(new \DateTime($data['end']));
        }
        elseif (array_key_exists('end', $data) && empty($data['end'])) {
            $event->setEndDate(null);
        }

        if (isset($data['allDay'])) {
            $event->setAllDay((bool)$data['allDay']);
        }

        $em->flush();

        $auditLogManager->log(
            'Calendrier',
            'Modification',
            sprintf('Modification de l\'événement : %s', $event->getTitle())
        );

        return $this->json(['message' => 'Event Updated'], 200);
    }

    /**
     * @Route("/{id}", name="calendar_events_delete", methods={"DELETE"}, 
     * options={"description"="Supprimer un événement", "permission"="CALENDAR:DELETE"})
     */
    public function deleteEvent(int $id, CalendarEventRepository $repo, EntityManagerInterface $em, AuditLogManager $auditLogManager)
    {
        $event = $repo->find($id);
        if (!$event) {
            return $this->json(['message' => 'Event not found'], 404);
        }

        $title = $event->getTitle();

        $em->remove($event);
        $em->flush();

        $auditLogManager->log(
            'Calendrier',
            'Suppression',
            sprintf('Suppression de l\'événement "%s"', $title)
        );

        return $this->json(['message' => 'Event Deleted'], 200);
    }
}