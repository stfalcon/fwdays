<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Event;
use App\Entity\EventBlock;
use App\Exception\RuntimeException;
use App\Service\EventService;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class VenueEventBlockService.
 */
class VenueEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;

    /** @var EventService */
    private $eventService;

    /**
     * @param Environment  $twig
     * @param EventService $eventService
     */
    public function __construct(Environment $twig, EventService $eventService)
    {
        parent::__construct($twig);

        $this->eventService = $eventService;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $eventBlock = $blockContext->getSetting('event_block');
        if (!$eventBlock instanceof EventBlock) {
            throw new RuntimeException();
        }

        $accessGrand = $this->accessSonataBlockService->isAccessGrand($eventBlock);
        if (!$accessGrand) {
            return new Response();
        }

        $event = $eventBlock->getEvent();

        if (!$event instanceof Event) {
            throw new NotFoundHttpException();
        }

        $pages = $this->eventService->getEventPages($event);
        $venuePage = isset($pages['venuePage']) ? $pages['venuePage'] : null;

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'venue_page' => $venuePage,
            'event' => $event,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/Event/event.venue.html.twig',
            'event_block' => null,
        ]);
    }
}
