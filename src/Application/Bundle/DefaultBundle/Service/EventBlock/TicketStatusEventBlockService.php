<?php

namespace Application\Bundle\DefaultBundle\Service\EventBlock;

use Application\Bundle\DefaultBundle\Service\TicketService;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TicketStatusEventBlockService.
 */
class TicketStatusEventBlockService extends BaseBlockService
{
    /** @var TicketService */
    private $ticketService;

    /**
     * TicketStatusEventBlockService constructor.
     *
     * @param string          $name
     * @param EngineInterface $templating
     * @param TicketService   $ticketService
     */
    public function __construct($name, EngineInterface $templating, TicketService $ticketService)
    {
        parent::__construct($name, $templating);

        $this->ticketService = $ticketService;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $event = $blockContext->getSetting('event');

        if (!$event instanceof Event) {
            return new NotFoundHttpException();
        }

        $position = $blockContext->getSetting('position');
        $ticketCost = $blockContext->getSetting('ticket_cost');

        $result = $this->ticketService->getTicketHtmlData(
            $event,
            $position,
            $ticketCost
        );

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'event' => $event,
            'result' => $result,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'template' => 'ApplicationDefaultBundle:Redesign/Event:event.ticket.status.html.twig',
            'event' => null,
            'position' => 'price_block',
            'ticket_cost' => null,
            'event_block' => null,
        ]);
    }
}
