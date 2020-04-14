<?php

namespace App\Helper;

use App\Entity\Ticket;
use App\Entity\User;
use App\Model\TranslatedMail;
use App\Traits;

/**
 * MailerHelper.
 */
class MailerHelper
{
    use Traits\TranslatorTrait;
    use Traits\EntityManagerTrait;
    use Traits\RouterTrait;
    use Traits\TwigTrait;

    private $mailer;
    /** @var NewPdfGeneratorHelper $pdfGeneratorHelper */
    private $pdfGeneratorHelper;

    /**
     * @param \Swift_Mailer $mailer
     * @param NewPdfGeneratorHelper $pdfGeneratorHelper
     */
    public function __construct(\Swift_Mailer $mailer, NewPdfGeneratorHelper $pdfGeneratorHelper)
    {
        $this->mailer = $mailer;
        $this->pdfGeneratorHelper = $pdfGeneratorHelper;
    }

    /**
     * Format message.
     *
     * @param User           $user          User
     * @param TranslatedMail $mail          Mail
     * @param bool           $isTestMessage Test message (needed for admin mails)
     *
     * @return \Swift_Message
     */
    public function formatMessage(User $user, TranslatedMail $mail, $isTestMessage = false): \Swift_Message
    {
        $text = $this->replace(
            $mail->getText(),
            [
                '%fullname%' => $user->getFullname(),
                '%user_id%' => $user->getId(),
            ]
        );
        $template = '@App/Email/new_email.html.twig';
        $params =
            [
                'text' => $text,
                'mail' => $mail,
                'user' => $user,
            ];

        $body = $this->renderTwigTemplate($template, $params);

        $title = $mail->getTitle();

        if ($isTestMessage) {
            $title = 'Тестовая рассылка для админов! '.$title;
        }

        return $this->createMessage($title, $user->getEmail(), $body);
    }

    /**
     * @param Ticket $ticket
     *
     * @return \Swift_Message
     *
     * @throws ImageFunctionFailedException
     * @throws ImageFunctionUnknownException
     */
    public function formatMessageWithTicket(Ticket $ticket)
    {
        $event = $ticket->getEvent();
        $user = $ticket->getUser();

        $body = $this->renderTwigTemplate(
            '@App/Email/email_with_ticket.html.twig',
            [
                'event' => $event,
                'ticket' => $ticket,
                'user' => $user,
            ]
        );

        $message = $this->createMessage($event->getName(), $user->getEmail(), $body);

        $html = $this->pdfGeneratorHelper->generateHTML($ticket);
        $message->attach(
            \Swift_Attachment::newInstance(
                $this->pdfGeneratorHelper->generatePdfFile($ticket, $html),
                $ticket->generatePdfFilename()
            )
        );

        return $message;
    }

    /**
     * Create message.
     *
     * @param string $subject
     * @param string $to
     * @param string $body
     *
     * @return \Swift_Message
     */
    public function createMessage($subject, $to, $body): \Swift_Message
    {
        return (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom('orgs@fwdays.com', 'Fwdays')
            ->setTo($to)
            ->setBody($body, 'text/html')
        ;
    }

    /**
     * Render template.
     *
     * @param string $view
     * @param array  $params
     *
     * @return string
     */
    public function renderTwigTemplate($view, $params): string
    {
        return $this->twig->loadTemplate($view)->render($params);
    }

    /**
     * @param string $emailSubject
     * @param string $view
     * @param array  $params
     * @param User   $user
     *
     * @return bool
     */
    public function sendEasyEmail(string $emailSubject, string $view, array $params, User $user): bool
    {
        $emailBody = $this->renderTwigTemplate($view, $params);
        $emailMessage = $this->createMessage($emailSubject, $user->getEmail(), $emailBody);

        return $this->mailer->send($emailMessage) > 0;
    }

    /**
     * @param User   $user
     * @param string $plainPassword
     *
     * @return bool
     */
    public function sendAutoRegistration(User $user, string $plainPassword): bool
    {
        $body = $this->renderTwigTemplate(
            'Registration/automatically.html.twig',
            [
                'user' => $user,
                'plainPassword' => $plainPassword,
            ]
        );

        $message = $this->createMessage(
            $this->translator->trans('registration.email.subject'),
            $user->getEmail(),
            $body
        );

        return $this->mailer->send($message) > 0;
    }

    /**
     * @param string $text
     * @param array  $data
     *
     * @return mixed|string
     */
    private function replace(string $text, array $data)
    {
        foreach ($data as $key => $value) {
            $text = str_replace($key, $value, $text);
        }

        return $text;
    }
}
