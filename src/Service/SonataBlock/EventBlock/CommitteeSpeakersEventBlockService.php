<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Event;
use App\Entity\EventBlock;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use App\Traits\TranslatorTrait;
use http\Exception\RuntimeException;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommitteeSpeakersEventBlockService.
 */
class CommitteeSpeakersEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;
    use TranslatorTrait;

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
            'template' => 'Redesign/Event/event.speakers.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
