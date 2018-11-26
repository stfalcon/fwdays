<?php

namespace Stfalcon\Bundle\EventBundle\Helper;

use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Mail;
use Twig_Environment;

/**
 * Class StfalconMailerHelper.
 */
class StfalconMailerHelper
{
    /** @var Twig_Environment */
    protected $twig;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
    protected $router;

    /** @var \Swift_Mailer */
    protected $mailer;

    /**
     * Constructor.
     *
     * @param Twig_Environment                               $twig
     * @param \Doctrine\ORM\EntityManager                    $em
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     * @param \Swift_Mailer                                  $mailer
     */
    public function __construct(Twig_Environment $twig, $em, $router, $mailer)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->router = $router;
        $this->mailer = $mailer;
    }

    /**
     * Format message.
     *
     * @param User $user          User
     * @param Mail $mail          Mail
     * @param bool $isTestMessage Test message (needed for admin mails)
     * @param bool $withTicket
     *
     * @return \Swift_Mime_MimePart
     */
    public function formatMessage(User $user, Mail $mail, $isTestMessage = false, $withTicket = false)
    {
        if ($withTicket) {
            $event = isset($mail->getEvents()[0]) ? $mail->getEvents()[0] : null;
            $params = ['event' => $event];
            $template = '@ApplicationDefault/Email/email_with_ticket.html.twig';
        } else {
            $text = $mail->replace(
                [
                    '%fullname%' => $user->getFullname(),
                    '%user_id%' => $user->getId(),
                ]
            );
            $template = '@ApplicationDefault/Email/new_email.html.twig';
            $params =
                [
                    'text' => $text,
                    'mail' => $mail,
                    'user' => $user,
                ];
        }

        $body = $this->renderTwigTemplate($template, $params);

        $title = $mail->getTitle();

        if ($isTestMessage) {
            $title = 'Тестовая рассылка для админов! '.$title;
        }

        return $this->createMessage($title, $user->getEmail(), $body);
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
    public function createMessage($subject, $to, $body)
    {
        return \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('orgs@fwdays.com', 'Fwdays')
            ->setTo($to)
            ->setBody($body, 'text/html');
    }

    /**
     * Render template.
     *
     * @param string $view
     * @param array  $params
     *
     * @return string
     */
    public function renderTwigTemplate($view, $params)
    {
        return $this->twig->loadTemplate($view)->render($params);
    }

    /**
     * @param string $emailSubject
     * @param string $view
     * @param array  $params
     * @param User   $user
     */
    public function sendEasyEmail($emailSubject, $view, $params, $user)
    {
        $emailBody = $this->renderTwigTemplate($view, $params);

        $emailMessage = $this->createMessage($emailSubject, $user->getEmail(), $emailBody);

        $this->mailer->send($emailMessage);
    }
}
