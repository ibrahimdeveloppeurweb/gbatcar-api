<?php

namespace App\Events;

use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $em;
    private $encoder;
    private $userRepository;
    public function __construct(
        EntityManagerInterface $em,
        JWTEncoderInterface $encoder, 
        UserRepository $userRepository
    )
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
    }

    public function attachDataToToken(AuthenticationSuccessEvent $event)
    {
        $array = $event->getData();
        /** @var User $user */
        $user = $event->getUser();
        $user = $this->userRepository->findOneBy(['username' => $user->getUsername()]);
        if (!$user instanceof UserInterface) {
            return;
        }

        $data = [
            'data' => [
                'token' => $array['token'],
                'isFirstUser' => method_exists($user, 'getIsFirst') ? $user->getIsFirst() : null,
                'role' => $user->getRoles()[0],
                'agencyKey' => $user->getAgency() !== null ? $user->getAgency()->getUuid() : null,
                'refreshToken' => '',

            ],
            'status' => 'success',
            'code' => 200,
            'message' => 'Connexion reussie'
        ];
        $event->setData($data);
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => ['attachDataToToken', 10]
        ];
    }
}
