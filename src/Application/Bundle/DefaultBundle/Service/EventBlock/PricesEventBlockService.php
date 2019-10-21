<?php

namespace Application\Bundle\DefaultBundle\Service\EventBlock;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Repository\TicketCostRepository;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PricesEventBlockService.
 */
class PricesEventBlockService extends AbstractBlockService
{
    /** @var TicketCostRepository */
    private $ticketCostRepository;

    /**
     * PricesEventBlockService constructor.
     *
     * @param string               $name
     * @param EngineInterface      $templating
     * @param TicketCostRepository $ticketCostRepository
     */
    public function __construct($name, EngineInterface $templating, TicketCostRepository $ticketCostRepository)
    {
        parent::__construct($name, $templating);

        $this->ticketCostRepository = $ticketCostRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $event = $blockContext->getSetting('event');

        if (!$event instanceof Event) {
            throw new NotFoundHttpException();
        }

        $eventCurrentCost = $this->ticketCostRepository->getEventCurrentCost($event);
        $ticketCosts = $this->ticketCostRepository->getEventTicketsCost($event);

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'ticketCosts' => $ticketCosts,
            'currentPrice' => $eventCurrentCost,
            'event' => $event,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'ApplicationDefaultBundle:Redesign/Event:event_price.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
