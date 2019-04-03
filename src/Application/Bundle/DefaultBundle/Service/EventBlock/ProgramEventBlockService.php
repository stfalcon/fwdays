<?php

namespace Application\Bundle\DefaultBundle\Service\EventBlock;

use Application\Bundle\DefaultBundle\Service\EventService;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProgramEventBlockService.
 */
class ProgramEventBlockService extends AbstractBlockService
{
    /** @var EventService */
    private $eventService;

    /**
     * ProgramEventBlockService constructor.
     *
     * @param string          $name
     * @param EngineInterface $templating
     * @param EventService    $eventService
     */
    public function __construct($name, EngineInterface $templating, EventService $eventService)
    {
        parent::__construct($name, $templating);

        $this->eventService = $eventService;
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
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'ApplicationDefaultBundle:Redesign/Event:event.program.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
