<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\EventBlock;
use App\Exception\RuntimeException;
use App\Service\EventService;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * ProgramEventBlockService.
 */
class ProgramEventBlockService extends AbstractBlockService
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

        $pages = $this->eventService->getEventPages($event);
        $programPage = isset($pages['programPage']) ? $pages['programPage'] : null;

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'program_page' => $programPage,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/Event/event.program.html.twig',
            'event_block' => null,
        ]);
    }
}
