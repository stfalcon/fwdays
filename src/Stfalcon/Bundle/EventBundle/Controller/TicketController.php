<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Response,
    JMS\SecurityExtraBundle\Annotation\Secure;

use Stfalcon\Bundle\EventBundle\Entity\Ticket,
    Stfalcon\Bundle\EventBundle\Entity\Event,
    Stfalcon\Bundle\EventBundle\Entity\Payment;

/**
 * Ticket controller
 */
class TicketController extends BaseController
{
    /**
     * Take part in the event. Create new ticket for user
     *
     * @param string $event_slug
     *
     * @return RedirectResponse
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/take-part", name="event_takePart")
     * @Template()
     */
    public function takePartAction($event_slug)
    {
        $em     = $this->getDoctrine()->getManager();
        $event  = $this->getEventBySlug($event_slug);
        $user = $this->get('security.context')->getToken()->getUser();

        // ищем билет на этот ивент
        $ticket = $this->container->get('stfalcon_event.ticket.service')
            ->findTicketForEventByCurrentUser($event);
        // если билета нет, тогда создаем новый
        if (is_null($ticket)) {
            $this->container->get('stfalcon_event.ticket.service')
                ->createTicket($event, $user);
        }

        // переносим на страницу билетов пользователя к хешу /evenets/my#zend-framework-day-2011
        return new RedirectResponse($this->generateUrl('events_my') . '#' . $event->getSlug());
    }

    /**
     * Show event ticket status (for current user)
     *
     * @param Event $event
     *
     * @return array
     *
     * @Template()
     */
    public function statusAction(Event $event)
    {
        $ticket = $this->container->get('stfalcon_event.ticket.service')
                ->findTicketForEventByCurrentUser($event);

        return array(
            'event'  => $event,
            'ticket' => $ticket
        );
    }

    /**
     * Generating ticket with QR-code to event
     *
     * @param string $event_slug
     *
     * @return array
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/ticket", name="event_ticket_download")
     */
    public function downloadAction($event_slug)
    {
        $event  = $this->getEventBySlug($event_slug);
        $ticket = $this->container->get('stfalcon_event.ticket.service')
                ->findTicketForEventByCurrentUser($event);

        if (!$ticket || !$ticket->isPaid()) {
            return new Response('Вы не оплачивали участие в "' . $event->getName() . '"', 402);
        }

        /** @var $pdfGen \Stfalcon\Bundle\EventBundle\Helper\PdfGeneratorHelper */
        $pdfGen = $this->get('stfalcon_event.pdf_generator.helper');
        return new Response(
            $pdfGen->generatePdfFile($ticket),
            200,
            array(
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attach; filename="' . $ticket->generatePdfFilename() . '"'
            )
        );
    }

    /**
     * Check that QR-code is valid, and register ticket
     *
     * @param Ticket $ticket Ticket
     * @param string $hash   Hash
     *
     * @return Response
     *
     * @Route("/ticket/{ticket}/check/{hash}", name="event_ticket_registration")
     */
    public function registrationAction(Ticket $ticket, $hash)
    {
        // любопытных пользователей перенаправляем на страницу события
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('event_show', array('event_slug' => $ticket->getEvent()->getSlug())));
        }
        
        // сверяем хеш билета и хеш из урла
        if ($ticket->getHash() != $hash) {
            return new Response('<h1 style="color:red">Невалидный хеш для билета №' . $ticket->getId() .'</h1>', 403);
        }

        // проверяем существует ли оплата
        if ($ticket->getPayment() instanceof Payment) {
            // проверяем оплачен ли билет
            if ($ticket->getPayment()->isPaid()) {
                // проверяем или билет ещё не отмечен как использованный
                if ($ticket->isUsed()) {
                    $timeNow = new \DateTime();
                    $timeDiff = $timeNow->diff($ticket->getUpdatedAt());

                    return new Response('<h1 style="color:orange">Билет №' . $ticket->getId() . ' был использован ' . $timeDiff->format('%i мин. назад') . '</h1>', 409);
                }
            } else {
                return new Response('<h1 style="color:orange">Билет №' . $ticket->getId() . ' не оплачен' . '</h1>');
            }
        } else {
            return new Response('<h1 style="color:orange">Билет №' . $ticket->getId() . ' оплата не существует' . '</h1>');
        }

        // отмечаем билет как использованный
        $em = $this->getDoctrine()->getManager();
        $ticket->setUsed(true);
        $em->flush();

        return new Response('<h1 style="color:green">Все ок. Билет №' . $ticket->getId() .' отмечаем как использованный</h1>');
    }

    /**
     * Check that ticket number is valid
     *
     * @return array
     *
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/check/", name="check")
     * @Template()
     */
    public function checkByNumAction()
    {
        // @todo це було тимчасове рішення для адміна. треба винести в адмінку
        $ticketId = $this->getRequest()->get('id');

        if (!$ticketId) {
            return array(
                'action' => $this->generateUrl('check')
            );
        }

        $ticket = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket')
            ->findOneBy(array('id' => $ticketId));

        if (is_object($ticket)) {
            $url = $this->generateUrl(
                'event_ticket_registration',
                array(
                    'ticket' => $ticket->getId(),
                    'hash'   => $ticket->getHash()
                ),
                true
            );

            return array(
                'action'    => $this->generateUrl('check'),
                'ticketUrl' => $url
            );
        } else {
            return array(
                'message' => 'Not Found',
                'action'  => $this->generateUrl('check')
            );
        }
    }

}
