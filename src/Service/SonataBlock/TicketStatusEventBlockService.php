<?php

namespace App\Service\SonataBlock;

use App\Entity\Event;
use App\Service\Ticket\TicketService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * TicketStatusEventBlockService.
 */
class TicketStatusEventBlockService extends AbstractBlockService
{
    /** @var TicketService */
    private $ticketService;

    /**
     * @param Environment   $twig
     * @param TicketService $ticketService
     */
    public function __construct(Environment $twig, TicketService $ticketService)
    {
        parent::__construct($twig);

        $this->ticketService = $ticketService;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $event = $blockContext->getSetting('event');
        $forced = $blockContext->getSetting('forced');

        if (!$event instanceof Event) {
            throw new NotFoundHttpException();
        }

        $position = $blockContext->getSetting('position');
        $ticketCost = $blockContext->getSetting('ticket_cost');

        $result = $this->ticketService->getTicketHtmlData($event, $position, $ticketCost, $forced);

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'event' => $event,
            'result' => $result,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/Event/event.ticket.status.html.twig',
            'event' => null,
            'position' => 'price_block',
            'ticket_cost' => null,
            'event_block' => null,
            'forced' => null,
        ]);
    }
}
