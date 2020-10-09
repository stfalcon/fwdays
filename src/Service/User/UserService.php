<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEventRegistration;
use App\Helper\MailerHelper;
use App\Traits;
use App\Twig\AppDateTimeExtension;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * UserService.
 */
class UserService
{
    use Traits\TokenStorageTrait;
    use Traits\EntityManagerTrait;
    use Traits\SessionTrait;
    use Traits\TranslatorTrait;

    public const RESULT_THROW_ON_NULL = 'throw_on_null';
    public const RESULT_RETURN_IF_NULL = 'result_return_null';

    private const SESSION_USER_REG_EMAIL_SEND_KEY = 'user_event_reg_send';

    /** @var MailerHelper */
    private $mailerHelper;

    /** @var AppDateTimeExtension */
    private $appDateTimeExtension;

    /** @var Router */
    private $router;

    /**
     * @param MailerHelper         $mailerHelper
     * @param AppDateTimeExtension $appDateTimeExtension
     * @param Router               $router
     */
    public function __construct(MailerHelper $mailerHelper, AppDateTimeExtension $appDateTimeExtension, Router $router)
    {
        $this->mailerHelper = $mailerHelper;
        $this->appDateTimeExtension = $appDateTimeExtension;
        $this->router = $router;
    }

    /**
     * @param string $throw
     *
     * @return User|null
     *
     * @throws AccessDeniedException
     */
    public function getCurrentUser(string $throw = self::RESULT_THROW_ON_NULL): ?User
    {
        $user = null;

        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
        }

        $user = $user instanceof User ? $user : null;

        if (null === $user && self::RESULT_THROW_ON_NULL === $throw) {
            throw new AccessDeniedException();
        }

        return $user;
    }

    /**
     * @return bool
     */
    public function isUserAccess(): bool
    {
        $user = null;

        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
        }

        return $user instanceof User;
    }

    /**
     * @param User                    $user
     * @param Event                   $event
     * @param \DateTimeInterface|null $date
     * @param bool                    $flush
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function registerUserToEvent(User $user, Event $event, ?\DateTimeInterface $date = null, bool $flush = true): bool
    {
        $userEventRegistration = new UserEventRegistration($user, $event, $date);

        if ($user->addUserEventRegistration($userEventRegistration)) {
            $this->em->persist($userEventRegistration);
            if ($flush) {
                $this->em->flush();
            }

            return true;
        }

        return false;
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function unregisterUserFromEvent(User $user, Event $event): bool
    {
        if ($user->removeUserEventRegistration($event)) {
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * @param User  $user
     * @param Event $event
     */
    public function sendRegistrationEmail(User $user, Event $event): void
    {
        $sentEmails = $this->session->get(self::SESSION_USER_REG_EMAIL_SEND_KEY, []);
        if (\in_array($event->getId(), $sentEmails, true) || ($event->isPaidParticipationCost() && $event->getReceivePayments())) {
            return;
        }

        $addGoogleCalendarLinks = $this->appDateTimeExtension->linksForGoogleCalendar($event);
        $eventDate = $this->appDateTimeExtension->eventDate($event, null, true, null, ' ');

        if (!empty($addGoogleCalendarLinks)) {
            $googleTitle = '<li>'.$this->translator->trans('email_event_registration.registration_calendar', ['%add_calendar_links%' => $addGoogleCalendarLinks]).'</li>';
        } else {
            $googleTitle = '';
        }

        if (!empty($event->getTelegramLink())) {
            $telegramTitle = $this->translator->trans('email_event_registration.telegram_link', ['%telegram_link%' => $event->getTelegramLink()]);
        } else {
            $telegramTitle = '';
        }

        $text = $this->translator->trans('email_event_registration.hello', ['%user_name%' => $user->getFullname()]);
        $eventLink = $this->router->generate('event_show', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($event->isFreeParticipationCost() || $event->isFreemiumParticipationCost()) {
            $subject = $this->translator->trans('email_event_registration.subject', ['%event_name%' => $event->getName()]);
            $text .= $this->translator->trans('email_event_registration.registration', ['%event_name%' => $event->getName(), '%event_link%' => $eventLink, '%event_date%' => $eventDate]).
                $this->translator->trans('email_event_registration.registration1')
            ;
            if (!empty($googleTitle) || !empty($telegramTitle)) {
                $telegramTitle = !empty($telegramTitle) ? '<li>'.$telegramTitle.'</li>' : '';
                $text .= $this->translator->trans('email_event_registration.registration2', ['%google%' => $googleTitle, '%telegram%' => $telegramTitle]);
            }
        } else {
            $subject = $this->translator->trans('email_event_registration.pre_subject', ['%event_name%' => $event->getName()]);
            $text .= $this->translator->trans('email_event_registration.pre_registration', ['%event_name%' => $event->getName(), '%event_link%' => $eventLink]);
            if (!empty($telegramTitle)) {
                $text .= $this->translator->trans('email_event_registration.pre_registration2', ['%telegram%' => $telegramTitle]);
            }
        }

        $text .= $this->translator->trans('email_event_registration.footer')
            .$this->translator->trans('email_event_registration.ps');

        if ($this->mailerHelper->sendEasyEmail($subject, 'Email/new_email.html.twig', ['text' => $text, 'user' => $user, 'mail' => null], $user) > 0) {
            $sentEmails[] = $event->getId();
            $this->session->set(self::SESSION_USER_REG_EMAIL_SEND_KEY, $sentEmails);
        }
    }
}
