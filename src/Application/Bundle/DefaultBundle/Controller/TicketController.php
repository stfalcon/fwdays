<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TicketController  extends Controller
{
    /**
     * Show event ticket status (for current user)
     *
     * @param Event $event
     * @param string $position
     * @param TicketCost $ticketCost
     *
     * @return Response
     */
    public function statusAction(Event $event, $position = 'card', TicketCost $ticketCost = null)
    {
        /* @var  User $user */
        $user = $this->getUser();

        $result = $this->get('stfalcon_event.ticket.service')->getTicketHtmlData($user, $event, $position, $ticketCost);

        return $this->render('@ApplicationDefault/Redesign/event.ticket.status.html.twig', [
            'class'   => $result['class'],
            'caption' => $result['caption'],
            'href'    => $result['href'],
            'isDiv'   => $result['isDiv'],
            'data'    => $result['data'],
        ]);
    }

    /**
     * Generating ticket with QR-code to event
     *
     * @Route("/event/{event_slug}/ticket", name="event_ticket_download")
     * @Security("has_role('ROLE_USER')")
     * @param string $event_slug
     *
     * @return array|Response
     */
    public function downloadAction($event_slug)
    {
        $event = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $event_slug]);
        /** @var Ticket $ticket */
        $ticket = $this->container->get('stfalcon_event.ticket.service')
            ->findTicketForEventByCurrentUser($event);

        if (!$ticket || !$ticket->isPaid()) {
            return new Response('Вы не оплачивали участие в "' . $event->getName() . '"', 402);
        }

        /** @var $pdfGen \Stfalcon\Bundle\EventBundle\Helper\PdfGeneratorHelper */
        $pdfGen = $this->get('stfalcon_event.pdf_generator.helper');
        $html = $pdfGen->generateHTML($ticket);

        return new Response(
            $pdfGen->generatePdfFile($ticket, $html),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attach; filename="' . $ticket->generatePdfFilename() . '"'
            ]
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
        //bag fix test ticket.feature:27
        // сверяем хеш билета и хеш из урла
        if ($ticket->getHash() != $hash) {
            return new Response('<h1 style="color:red">Невалидный хеш для билета №' . $ticket->getId() . '</h1>', 403);
        }

        //bag fix test ticket.feature:33
        // любопытных пользователей перенаправляем на страницу события
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_VOLUNTEER')) {
            return $this->redirect($this->generateUrl('event_show', ['event_slug' => $ticket->getEvent()->getSlug()]));
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
     * @Security("has_role('ROLE_VOLUNTEER')")
     * @Route("/check/", name="check_ticket_by_number")
     * @Template()
     */
    public function checkByNumAction()
    {
        // @todo це було тимчасове рішення для адміна. треба винести в адмінку
        $ticketId = $this->getRequest()->get('id');

        if (!$ticketId) {
            return array(
                'action' => $this->generateUrl('check_ticket_by_number')
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
                'action'    => $this->generateUrl('check_ticket_by_number'),
                'ticketUrl' => $url
            );
        } else {
            return array(
                'message' => 'Not Found',
                'action'  => $this->generateUrl('check_ticket_by_number')
            );
        }
    }
}
