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

        $message = \Swift_Message::newInstance()
            ->setSubject($mail->getTitle())
            ->setFrom('orgs@fwdays.com', 'Frameworks Days')
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');

        return $message;
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
