<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\EventBlock;
use App\Exception\RuntimeException;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use App\Traits\TranslatorTrait;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * DiscussionExpertsEventBlockService.
 */
class DiscussionExpertsEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;
    use TranslatorTrait;

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
        $speakers = $event->getDiscussionExperts();

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'templateTitle' => $this->translator->trans('expert.speaker.title'),
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
            'event_block' => null,
        ]);
    }
}
