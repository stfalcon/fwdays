<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\DefaultBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketController.
 */
class TicketController extends Controller
{
//    /**
//     * Show event ticket status (for current user).
//     *
//     * @param Event      $event
//     * @param string     $position
//     * @param TicketCost $ticketCost
//     *
//     * @return Response
//     */
//    public function statusAction(Event $event, $position = 'card', TicketCost $ticketCost = null)
//    {
//        $result = $this->get('app.ticket.service')->getTicketHtmlData(
//            $event,
//            $position,
//            $ticketCost
//        );
//
//        return $this->render('@ApplicationDefault/Redesign/Event/event.ticket.status.html.twig', $result);
//    }

    /**
     * Generating ticket with QR-code to event.
     *
     * @Route("/event/{eventSlug}/ticket", name="event_ticket_download")
     * @Route("/event/{eventSlug}/ticket/{asHtml}", name="event_ticket_download_html")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $eventSlug
     * @param string $asHtml
     *
     * @return array|Response
     */
    public function downloadAction($eventSlug, $asHtml = null)
    {
        $event = $this->getDoctrine()
            ->getRepository('ApplicationDefaultBundle:Event')->findOneBy(['slug' => $eventSlug]);
        /** @var User $user */
        $user = $this->getUser();
        /** @var Ticket $ticket */
        $ticket = $this->getDoctrine()->getManager()->getRepository('ApplicationDefaultBundle:Ticket')
            ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);

        if (!$ticket || !$ticket->isPaid()) {
            return new Response('Вы не оплачивали участие в "'.$event->getName().'"', 402);
        }

        /** @var $pdfGen \Application\Bundle\DefaultBundle\Helper\NewPdfGeneratorHelper */
        $pdfGen = $this->get('app.helper.new_pdf_generator');

        $html = $pdfGen->generateHTML($ticket);

        if ('html' === $asHtml && 'test' === $this->getParameter('kernel.environment')) {
            return new Response(
                $html,
                200,
                [
                    'Content-Type' => 'application/txt',
                    'Content-Disposition' => sprintf('attach; filename="%s"', $ticket->generatePdfFilename()),
                ]
            );
        }

        return new Response(
            $pdfGen->generatePdfFile($ticket, $html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attach; filename="%s"', $ticket->generatePdfFilename()),
            ]
        );
    }

    /**
     * Check that QR-code is valid, and register ticket.
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
        if ($ticket->getHash() !== $hash) {
            return new Response('<h1 style="color:red">Невалидный хеш для билета №'.$ticket->getId().'</h1>', 403);
        }

        //bag fix test ticket.feature:33
        // любопытных пользователей перенаправляем на страницу события
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_VOLUNTEER')) {
            return $this->redirect($this->generateUrl('event_show', ['eventSlug' => $ticket->getEvent()->getSlug()]));
        }

        // проверяем существует ли оплата
        if ($ticket->getPayment() instanceof Payment) {
            // проверяем оплачен ли билет
            if ($ticket->getPayment()->isPaid()) {
                // проверяем или билет ещё не отмечен как использованный
                if ($ticket->isUsed()) {
                    $timeNow = new \DateTime();
                    $timeDiff = $timeNow->diff($ticket->getUpdatedAt());

                    return new Response('<h1 style="color:orange">Билет №'.$ticket->getId().' был использован '.$timeDiff->format('%i мин. назад').'</h1>', 409);
                }
            } else {
                return new Response('<h1 style="color:orange">Билет №'.$ticket->getId().' не оплачен'.'</h1>');
            }
        } else {
            return new Response('<h1 style="color:orange">Билет №'.$ticket->getId().' оплата не существует'.'</h1>');
        }

        // отмечаем билет как использованный
        $em = $this->getDoctrine()->getManager();
        $ticket->setUsed(true);
        $em->flush();

        return new Response('<h1 style="color:green">Все ок. Билет №'.$ticket->getId().' отмечаем как использованный</h1>');
    }
}
