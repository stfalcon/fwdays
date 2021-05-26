<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\EventBlock;
use App\Exception\RuntimeException;
use App\Repository\ReviewRepository;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * ReviewsEventBlockService.
 */
class ReviewsEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;

    /** @var ReviewRepository */
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
        if (!$eventBlock instanceof EventBlock) {
            throw new RuntimeException();
        }

        $accessGrand = $this->accessSonataBlockService->isAccessGrand($eventBlock);
        if (!$accessGrand) {
            return new Response();
        }

        $event = $eventBlock->getEvent();
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
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/Event/event.reviews.html.twig',
            'event_block' => null,
        ]);
    }
}
