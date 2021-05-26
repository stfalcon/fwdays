<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Event;
use App\Entity\EventBlock;
use App\Repository\ReviewRepository;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use App\Traits\TranslatorTrait;
use http\Exception\RuntimeException;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * CandidateSpeakersEventBlockService.
 */
class CandidateSpeakersEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;
    use TranslatorTrait;

    private $reviewRepository;

    /**
     * @param Environment      $twig
     * @param ReviewRepository $reviewRepository
     */
    public function __construct(Environment $twig, ReviewRepository $reviewRepository)
    {
        parent::__construct($twig);

        $this->reviewRepository = $reviewRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
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
        $speakers = $event->getCandidateSpeakers();

        foreach ($speakers as &$speaker) {
            $speaker->setReviews(
                $this->reviewRepository->findReviewsOfSpeakerForEvent($speaker, $event)
            );
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'templateTitle' => $this->translator->trans('candidate.speaker.title'),
            'speakers' => $speakers,
            'section_id' => 'candidate-speakers-event',
            'with_review' => true,
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
