<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\EventBlock;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use http\Exception\RuntimeException;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DescriptionEventBlockService.
 */
class DescriptionEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;

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
        $about = $event->getAbout();

        if (!$about) {
            $about = $eventBlock->getText();
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'event' => $event,
            'about' => $about,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/Event/event.description.html.twig',
            'event_block' => null,
        ]);
    }
}
