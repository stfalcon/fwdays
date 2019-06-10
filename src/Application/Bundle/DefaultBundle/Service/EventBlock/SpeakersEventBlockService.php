<?php

namespace Application\Bundle\DefaultBundle\Service\EventBlock;

use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SpeakersEventBlockService.
 */
class SpeakersEventBlockService extends AbstractBlockService
{
    private $translator;
    private $reviewRepository;

    /**
     * SpeakersEventBlockService constructor.
     *
     * @param string           $name
     * @param EngineInterface  $templating
     * @param Translator       $translator
     * @param ReviewRepository $reviewRepository
     */
    public function __construct($name, EngineInterface $templating, Translator $translator, ReviewRepository $reviewRepository)
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
            throw new NotFoundHttpException();
        }

        $speakers = $event->getSpeakers();

        /** @var $speaker \Application\Bundle\DefaultBundle\Entity\Speaker */
        foreach ($speakers as &$speaker) {
            $speaker->setReviews(
                $this->reviewRepository->findReviewsOfSpeakerForEvent($speaker, $event)
            );
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'templateTitle' => $this->translator->trans('speaker.title'),
            'speakers' => $speakers,
            'section_id' => 'speakers-event',
            'with_review' => true,
            'event' => $event,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'ApplicationDefaultBundle:Redesign/Event:event.speakers.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
