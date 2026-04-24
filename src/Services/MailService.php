<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param mixed $to
     * @param array $context
     * @param string $subject
     * @param int $priority (Peut recevoir 1|2|3|4|5)
     * @param string $path
     * @param array $attachments Array of absolute file paths
     * @return void
     */
    public function email($to, array $context, string $subject, int $priority = 3, string $path, array $attachments = [])
    {
        $sender = $_ENV['MAILER_SENDER'] ?? 'noreply@gbatcar.com';

        try {
            if ($to && $this->checkEmail($to)) {
                $mail = (new TemplatedEmail())
                    ->from($sender)
                    ->to($to)
                    ->priority($priority)
                    ->subject($subject)
                    ->context($context)
                    ->htmlTemplate($path);

                foreach ($attachments as $filePath) {
                    if (file_exists($filePath)) {
                        $mail->attachFromPath($filePath);
                    }
                }

                $this->mailer->send($mail);
            }
        }
        catch (TransportExceptionInterface $e) {
            if (($_ENV['APP_ENV'] ?? 'dev') === 'dev') {
                throw $e;
            }
        // Log or handle error here
        }
    }

    /**
     * Vérifie si l'adresse email est valide et dispose d'un MX record
     * 
     * @param string $email
     * @return bool
     */
    public function checkEmail($email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (($_ENV['APP_ENV'] ?? 'dev') === 'dev') {
            return true;
        }

        list($user, $domain) = explode('@', $email);
        return checkdnsrr($domain, 'MX');
    }
}