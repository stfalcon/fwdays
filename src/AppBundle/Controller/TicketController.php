<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Helper\NewPdfGeneratorHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TicketController.
 */
class TicketController extends Controller
{
    /**
     * Generating ticket with QR-code to event.
     *
     * @Route("/event/{slug}/ticket/{asHtml}", name="event_ticket_download", defaults={"asHtml":null})
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param Event       $event
     * @param string|null $asHtml
     *
     * @return array|Response
     */
    public function downloadAction(Event $event, $asHtml = null): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var Ticket $ticket */
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)
            ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);

        if (!$ticket || !$ticket->isPaid()) {
            return new Response(\sprintf('Вы не оплачивали участие в "%s"', $event->getName()), 402);
        }

        $pdfGen = $this->get(NewPdfGeneratorHelper::class);

        $html = $pdfGen->generateHTML($ticket);

        if ('html' === $asHtml && 'test' === $this->getParameter('kernel.environment')) {
            return new Response(
                $html,
                200,
                [
                    'Content-Type' => 'application/txt',
                    'Content-Disposition' => \sprintf('attach; filename="%s"', $ticket->generatePdfFilename()),
                ]
            );
        }

        return new Response(
            $pdfGen->generatePdfFile($ticket, $html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => \sprintf('attach; filename="%s"', $ticket->generatePdfFilename()),
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
            return $this->redirect($this->generateUrl('event_show', ['slug' => $ticket->getEvent()->getSlug()]));
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
