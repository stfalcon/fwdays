<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Payment;

/**
 * Event controller
 */
class EventController extends BaseController
{
    /**
     * List of active and past events
     *
     * @return array
     *
     * @Route("/events", name="events")
     * @Template()
     */
    public function indexAction()
    {
        $session = $this->get('session');

        /** @var \Application\Bundle\UserBundle\Entity\User $user */
        $user = $this->getUser();

        // Пока в сессие есть переменная just_registered будем показывать два флеш-сообения. Одно об успешной регистрации
        // второе - о том, что юзер может добавить событие.
        // Так как после подтверждения имейла нас перекидывает на страницу событий, мы должны показать эти сообщения.
        // Но после первого редиректа на страницу событий вылетает модальное окно с вопросом о разрешение использовать
        // свои данные партнерам сайта. В этот момент флеш-месседжи выводятся, но модальное окно их частично перекрывает
        // и не весь текст можно прочитать + внимание сосредоточено на окне а не на флеш-сообщениях.
        // Поэтому нужно еще раз показать эти флеш-сообщения на странице событий, но уже после того,
        // как пользователь даст ответ в модальном окне и опять будет перенаправлен на страницу событий.
        if ($session->has('just_registered')) {
            $message = $this->get('translator')->trans(
                'registration.confirmed',
                array('%username%' => $user->getFullname()),
                'FOSUserBundle'
            );
            $session->getFlashBag()->add('sonata_flash_success', $message);
            $session->getFlashBag()->add('sonata_flash_info', 'choose_event');
        }

        // Когда пользователь дал ответ и второй раз попадает на страницу событий, то allowShareContacts у него не null,
        // и мы удаляем переменную из сессий, после этого при перезагрузке страницы события -
        // флеш-сообщения уже показываться не будут
        if (null !== $user->isAllowShareContacts()) {
            $session->remove('just_registered');
        }

        $activeEvents = $this->getDoctrine()->getManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => true ), array('date' => 'DESC'));

        $pastEvents = $this->getDoctrine()->getManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => false ), array('date' => 'DESC'));

        return array(
            'activeEvents' => $activeEvents,
            'pastEvents' => $pastEvents
        );
    }

    /**
     * Finds and displays a Event entity.
     *
     * @param string $event_slug
     *
     * @return array
     *
     * @Route("/event/{event_slug}", name="event_show")
     * @Template()
     */
    public function showAction($event_slug)
    {
        $referralService = $this->get('stfalcon_event.referral.service');
        $referralService->handleRequest($this->getRequest());

        $event = $this->getEventBySlug($event_slug);

        return ['event' => $event];
    }

    /**
     * Show only active events for current user
     *
     * @return array
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/events/my", name="events_my")
     * @Template()
     */
    public function myAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconEventBundle:Ticket');
        $tickets = $ticketRepository->findTicketsOfActiveEventsForUser($user);

        return array('tickets' => $tickets);
    }
}
