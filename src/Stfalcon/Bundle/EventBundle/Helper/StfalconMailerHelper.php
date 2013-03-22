<?php

namespace Stfalcon\Bundle\EventBundle\Helper;


use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Mail;

class  StfalconMailerHelper {

    public static function formatMessage(User $user,Mail $mail){
        $text = $mail->replace(
            array(
                '%fullname%' => $user->getFullname(),
                '%user_id%' => $user->getId(),
            )
        );

        $message = \Swift_Message::newInstance()
            ->setSubject($mail->getTitle())
            // @todo refact
            ->setFrom('orgs@fwdays.com', 'Frameworks Days')
            ->setTo($user->getEmail())
            ->setBody($text, 'text/html');

        return $message;
    }
}