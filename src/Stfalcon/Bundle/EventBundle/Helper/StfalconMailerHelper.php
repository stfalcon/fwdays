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
     * @param User $user
     * @param Mail $mail
     *
     * @return \Swift_Mime_MimePart
     */
    public function formatMessage(User $user, Mail $mail)
    {
        $templateContent = $this->twig->loadTemplate('StfalconEventBundle::email.html.twig');

        $text = $mail->replace(
            array(
                '%fullname%' => $user->getFullname(),
                '%user_id%'  => $user->getId(),
            )
        );

        $body = $templateContent->render(array('text' => $text));

        $message = \Swift_Message::newInstance()
            ->setSubject($mail->getTitle())
            ->setFrom('orgs@fwdays.com', 'Frameworks Days')
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');

        return $message;
    }
}
