<?php

namespace Application\Bundle\DefaultBundle\Service\EventBlock;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Repository\ReviewRepository;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReviewsEventBlockService.
 */
class ReviewsEventBlockService extends AbstractBlockService
{
    /** @var ReviewRepository */
    private $reviewRepository;

    /**
     * SpeakersEventBlockService constructor.
     *
     * @param string           $name
     * @param EngineInterface  $templating
     * @param ReviewRepository $reviewRepository
     */
    public function __construct($name, EngineInterface $templating, ReviewRepository $reviewRepository)
    {
        parent::__construct($name, $templating);

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

        $reviews = $this->reviewRepository->findReviewsByEvent($event);

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'reviews' => $reviews,
            'event' => $event,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'ApplicationDefaultBundle:Redesign/Event:event.reviews.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
