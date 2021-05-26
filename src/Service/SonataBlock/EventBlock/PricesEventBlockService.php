<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Event;
use App\Entity\EventBlock;
use App\Entity\TicketCost;
use App\Exception\RuntimeException;
use App\Repository\TicketCostRepository;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * PricesEventBlockService.
 */
class PricesEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;

    /** @var TicketCostRepository */
    private $ticketCostRepository;

    /**
     * @param Environment          $twig
     * @param TicketCostRepository $ticketCostRepository
     */
    public function __construct(Environment $twig, TicketCostRepository $ticketCostRepository)
    {
        parent::__construct($twig);

        $this->ticketCostRepository = $ticketCostRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $eventBlock = $blockContext->getSetting('event_block');
        if ($eventBlock instanceof EventBlock) {
            $accessGrand = $this->accessSonataBlockService->isAccessGrand($eventBlock);
            if (!$accessGrand) {
                return new Response();
            }
            $event = $eventBlock->getEvent();
        } else {
            $event = $blockContext->getSetting('event');
        }

        if (!$event instanceof Event) {
            throw new RuntimeException();
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
