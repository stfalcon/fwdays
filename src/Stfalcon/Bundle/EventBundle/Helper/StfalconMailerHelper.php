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


    /**
     * Constructor
     *
     * @param Twig_Environment $twig
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     */
    public function __construct(Twig_Environment $twig, $em, $router)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->router = $router;
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

        $unsubscribeLink  = $this->router->generate('unsubscribe',
            [
                'hash'   => $user->getSalt(),
                'userId' => $user->getId()
            ], true);


        $body = $this->renderTwigTemplate(
            'StfalconEventBundle::email.html.twig',
            [
                'text'            => $text,
                'mail'            => $mail,
                'unsubscribeLink' => $unsubscribeLink
            ]
        );

        return $this->createMessage($mail->getTitle(), $user->getEmail(), $body);
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
            ->setFrom('orgs@fwdays.com', 'Frameworks Days')
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

}

