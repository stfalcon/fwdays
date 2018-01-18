<?php

namespace Stfalcon\Bundle\EventBundle\Helper;

use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Mail;
use Twig_Environment;

/**
 * Class StfalconMailerHelper
 */
class StfalconMailerHelper
{

    /**
     * @var Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var \Doctrine\ORM\EntityManager $em
     */
    protected $em;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    protected $mailer;
    /**
     * Constructor
     *
     * @param Twig_Environment $twig
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     * @param $mailer
     */
    public function __construct(Twig_Environment $twig, $em, $router, $mailer)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->router = $router;
        $this->mailer = $mailer;
    }

    /**
     * Format message
     *
     * @param User $user          User
     * @param Mail $mail          Mail
     * @param bool $isTestMessage Test message (needed for admin mails)
     *
     * @return \Swift_Mime_MimePart
     */
    public function formatMessage(User $user, Mail $mail, $isTestMessage = false)
    {
        $text = $mail->replace(
            array(
                '%fullname%' => $user->getFullname(),
                '%user_id%' => $user->getId(),
            )
        );

        $body = $this->renderTwigTemplate(
            'StfalconEventBundle::email.html.twig',
            [
                'text'            => $text,
                'mail'            => $mail,
                'user'            => $user,
            ]
        );

        $title = $mail->getTitle();
        // Модифицируем заголовок, если рассылка является для админов, т.е. тестовой
        if ($isTestMessage) {
            // Тестовая рассылка
            $title = 'Тестовая рассылка для админов! '.$title;
        }

        return $this->createMessage($title, $user->getEmail(), $body);
    }


    /**
     * Create message
     *
     * @param  string $subject
     * @param  string $to
     * @param  string $body
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
     * Render template
     *
     * @param string $view
     * @param array $params
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
