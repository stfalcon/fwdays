<?php

namespace Application\Bundle\DefaultBundle\Service\EventBlock;

use Application\Bundle\DefaultBundle\Entity\Event;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CommitteeSpeakersEventBlockService.
 */
class CommitteeSpeakersEventBlockService extends AbstractBlockService
{
    private $translator;

    /**
     * SpeakersEventBlockService constructor.
     *
     * @param string              $name
     * @param EngineInterface     $templating
     * @param TranslatorInterface $translator
     */
    public function __construct($name, EngineInterface $templating, TranslatorInterface $translator)
    {
        parent::__construct($name, $templating);

        $this->translator = $translator;
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

        $speakers = $event->getCommitteeSpeakers();

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'templateTitle' => $this->translator->trans('committee.speaker.title'),
            'speakers' => $speakers,
            'section_id' => 'committee-speakers-event',
            'with_review' => false,
            'event' => $event,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'ApplicationDefaultBundle:Redesign/Event:event.speakers.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
