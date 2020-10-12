<?php

namespace App\Helper;

use App\Entity\Ticket;
use App\Entity\User;
use App\Model\TranslatedMail;
use App\Traits;
use App\Twig\AppDateTimeExtension;
use Endroid\QrCode\Exceptions\ImageFunctionFailedException;
use Endroid\QrCode\Exceptions\ImageFunctionUnknownException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    /** @var PdfGeneratorHelper */
    private $pdfGeneratorHelper;

    /** @var AppDateTimeExtension */
    private $appDateTimeExtension;

    /**
     * @param \Swift_Mailer        $mailer
     * @param PdfGeneratorHelper   $pdfGeneratorHelper
     * @param AppDateTimeExtension $appDateTimeExtension
     */
    public function __construct(\Swift_Mailer $mailer, PdfGeneratorHelper $pdfGeneratorHelper, AppDateTimeExtension $appDateTimeExtension)
    {
        $this->mailer = $mailer;
        $this->pdfGeneratorHelper = $pdfGeneratorHelper;
        $this->appDateTimeExtension = $appDateTimeExtension;
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
        $template = 'Email/new_email.html.twig';
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

        $addGoogleCalendarLinks = $this->appDateTimeExtension->linksForGoogleCalendar($event);
        $eventDate = $this->appDateTimeExtension->eventDate($event, null, true, null, ' ');
        if (!empty($addGoogleCalendarLinks)) {
            $googleTitle = '<li>'.$this->translator->trans('email_event_registration.registration_calendar', ['%add_calendar_links%' => $addGoogleCalendarLinks]).'</li>';
        } else {
            $googleTitle = '';
        }

        $subject = $this->translator->trans('email_with_ticket.subject', ['%event_name%' => $event->getName()]);

        if (!empty($event->getTelegramLink())) {
            $telegramTitle = $this->translator->trans('email_event_registration.telegram_link', ['%telegram_link%' => $event->getTelegramLink()]);
        } else {
            $telegramTitle = '';
        }

        $eventLink = $this->router->generate('event_show', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        $text = $this->translator->trans('email_event_registration.hello', ['%user_name%' => $user->getFullname()]).
            $this->translator->trans('email_with_ticket.text0', ['%event_name%' => $event->getName(), '%event_link%' => $eventLink, '%event_date%' => $eventDate]);

        if (!empty($googleTitle) || !empty($telegramTitle)) {
            $telegramTitle = !empty($telegramTitle) ? '<li>'.$telegramTitle.'</li>' : '';
            $text .= $this->translator->trans('email_event_registration.registration2', ['%google%' => $googleTitle, '%telegram%' => $telegramTitle]);
        }

        $text .=
            $this->translator->trans('email_with_ticket.text1')
            .$this->translator->trans('email_event_registration.footer')
            .$this->translator->trans('email_with_ticket.refund_rules')
            .$this->translator->trans('email_event_registration.ps')
        ;

        $body = $this->renderTwigTemplate(
            'Email/email_with_ticket.html.twig',
            [
                'text' => $text,
                'event' => $event,
                'ticket' => $ticket,
                'user' => $user,
            ]
        );

        $message = $this->createMessage($subject, $user->getEmail(), $body);

        $html = $this->pdfGeneratorHelper->generateHTML($ticket);
        $message->attach(
            new \Swift_Attachment(
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
