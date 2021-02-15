<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Event;
use App\Entity\TicketCost;
use App\Repository\TicketCostRepository;
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

        $ticketCosts = $this->ticketCostRepository->getAllTicketsCostForEvent($event);

        $isOldPrice = false;
        $ticketBenefits = [];
        $eventCurrentCost = null;

        if (!empty($ticketCosts) && null === $ticketCosts[0]->getType()) {
            $eventCurrentCost = $this->ticketCostRepository->getEventLowestCost($event);
            $isOldPrice = true;
        } else {
            foreach (TicketCost::getTypes() as $type) {
                $eventCurrentCost[$type] = $this->ticketCostRepository->getEventCurrentCost($event, $type);
            }
            $tmpArray = [];
            foreach ($ticketCosts as $ticketCost) {
                $tmpArray[$ticketCost->getType()][] = $ticketCost;
            }
            $ticketCosts = $tmpArray;
            foreach ($event->getTicketBenefits() as $ticketBenefit) {
                $ticketBenefits[$ticketBenefit->getType()] = $ticketBenefit->getBenefits();
            }
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'ticketCosts' => $ticketCosts,
            'currentPrice' => $eventCurrentCost,
            'event' => $event,
            'is_old_price' => $isOldPrice,
            'ticket_benefits' => $ticketBenefits,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/Event/Price/event_price.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
