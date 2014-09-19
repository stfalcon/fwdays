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
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * Constructor
     *
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Format message
     *
     * @param User $user
     * @param Mail $mail
     *
     * @return \Swift_Mime_MimePart
     */
    public function formatMessage(User $user, Mail $mail)
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
                'text' => $text,
                'mail' => $mail
            ]
        );

        return $this->createMessage($mail->getTitle(), $user->getEmail(), $body);
    }


    /**
     * @param  string $subject
     * @param  string $to
     * @param  string $body
     * @return \Swift_Message
     */
    public function createMessage($subject, $to, $body)
    {
        return \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('orgs@fwdays.com', 'Frameworks Days')
            ->setTo($to)
            ->setBody($body, 'text/html');
    }

    /**
     * @param string $view
     * @param array $params
     * @return string
     */
    public function renderTwigTemplate($view, $params)
    {
        return $this->twig->loadTemplate($view)->render($params);
    }

}

