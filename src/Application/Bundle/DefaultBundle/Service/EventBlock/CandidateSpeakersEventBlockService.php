<?php

namespace Application\Bundle\DefaultBundle\Service\EventBlock;

use Doctrine\Common\Persistence\ObjectRepository;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CandidateSpeakersEventBlockService.
 */
class CandidateSpeakersEventBlockService extends BaseBlockService
{
    /** @var IdentityTranslator */
    private $translator;

    /** @var ReviewRepository */
    private $reviewRepository;

    /**
     * SpeakersEventBlockService constructor.
     *
     * @param string              $name
     * @param EngineInterface     $templating
     * @param TranslatorInterface $translator
     * @param ObjectRepository    $reviewRepository
     */
    public function __construct($name, EngineInterface $templating, TranslatorInterface $translator, ObjectRepository $reviewRepository)
    {
        parent::__construct($name, $templating);

        $this->translator = $translator;
        $this->reviewRepository = $reviewRepository;
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

        $speakers = $event->getCandidateSpeakers();

        /** @var $speaker \Stfalcon\Bundle\EventBundle\Entity\Speaker */
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
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'template' => 'ApplicationDefaultBundle:Redesign/Event:event.speakers.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
