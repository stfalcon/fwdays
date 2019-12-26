<?php

namespace Application\Bundle\DefaultBundle\Helper;

use Application\Bundle\DefaultBundle\Entity\Mail;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Model\TranslatedMail;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class StfalconMailerHelper.
 */
class StfalconMailerHelper
{
    protected $twig;
    protected $em;
    protected $router;
    protected $mailer;
    private $translator;

    /**
     * @param Environment         $twig
     * @param EntityManager       $em
     * @param Router              $router
     * @param \Swift_Mailer       $mailer
     * @param TranslatorInterface $translator
     */
    public function __construct(Environment $twig, EntityManager $em, Router $router, \Swift_Mailer $mailer, TranslatorInterface $translator)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**
     * Format message.
     *
     * @param User           $user          User
     * @param TranslatedMail $mail          Mail
     * @param bool           $isTestMessage Test message (needed for admin mails)
     * @param bool           $withTicket
     *
     * @return \Swift_Message
     */
    public function formatMessage(User $user, TranslatedMail $mail, $isTestMessage = false, $withTicket = false): \Swift_Message
    {
        if ($withTicket) {
            $event = $mail->getEvents()[0] ?? null;
            $params = ['event' => $event];
            $template = '@ApplicationDefault/Email/email_with_ticket.html.twig';
        } else {
            $text = $this->replace(
                $mail->getText(),
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
    public function createMessage($subject, $to, $body): \Swift_Message
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
            'ApplicationDefaultBundle:Registration:automatically.html.twig',
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
