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
     * Constructor
     *
     * @param Twig_Environment $twig
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(Twig_Environment $twig, $em)
    {
        $this->twig = $twig;
        $this->em = $em;
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

    /**
     * Check possible to send the mail to user
     *
     * @param Mail $mail
     * @param User $user
     * @return bool
     */
    public function allowSendMailForUser($mail, $user)
    {

        if (!($user && $mail)) {
            return false;
        }

        if (!$user->isSubscribe()) {

            // участвует ли пользователь в этих событиях
            foreach ($mail->getEvents() as $event) {

                /** @var $eventRepository \Stfalcon\Bundle\EventBundle\Repository\EventRepository */
                $eventRepository = $this->em->getRepository('StfalconEventBundle:Event');

                if ($eventRepository->isActiveEventForUser($event, $user)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

}

