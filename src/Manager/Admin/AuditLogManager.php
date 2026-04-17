<?php

namespace App\Manager\Admin;

use App\Entity\Admin\AuditLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Admin\User;

class AuditLogManager
{
    private $em;
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * Log an action in the audit log.
     *
     * @param string $module
     * @param string $action
     * @param string $details
     */
    public function log(string $module, string $action, string $details): void
    {
        $user = $this->security->getUser();

        $fullName = 'Système';
        $initials = 'SYS';

        if ($user instanceof User) {
            $fullName = $user->getLibelle() ?: $user->getUsername();

            // Generate initials
            $prenom = $user->getPrenom() ? strtoupper(substr($user->getPrenom(), 0, 1)) : '';
            $nom = $user->getNom() ? strtoupper(substr($user->getNom(), 0, 1)) : '';
            $initials = $prenom . $nom;

            if (empty($initials) && !empty($user->getUsername())) {
                $initials = strtoupper(substr($user->getUsername(), 0, 2));
            }
        }

        $auditLog = new AuditLog();
        $auditLog->setUserFullName($fullName)
            ->setUserInitials($initials)
            ->setModule($module)
            ->setAction($action)
            ->setDetails($details);
        // createdAt is automatically handled by Timestampable

        $this->em->persist($auditLog);
        $this->em->flush();
    }
}