<?php

namespace Application\Bundle\DefaultBundle\Service\EventBlock;

use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\EventBlock;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class DescriptionEventBlockService.
 */
class DescriptionEventBlockService extends BaseBlockService
{
    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $event = $blockContext->getSetting('event');

        if (!$event instanceof Event) {
            return new NotFoundHttpException();
        }

        $about = $event->getAbout();
        if (!$about) {
            $eventBlock = $blockContext->getSetting('event_block');

            if ($eventBlock instanceof EventBlock) {
                $about = $eventBlock->getText();
            }
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
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'template' => 'ApplicationDefaultBundle:Redesign/Event:event.description.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
