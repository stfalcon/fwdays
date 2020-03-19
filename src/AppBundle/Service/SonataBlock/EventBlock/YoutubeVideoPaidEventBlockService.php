<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Event;
use App\Entity\EventBlock;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Service\User\UserService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * YoutubeVideoPaidEventBlockService.
 */
class YoutubeVideoPaidEventBlockService extends AbstractBlockService
{
    private $userService;
    private $ticketRepository;
    private $isPlaylist;

    /**
     * ProgramEventBlockService constructor.
     *
     * @param string           $name
     * @param EngineInterface  $templating
     * @param UserService      $userService
     * @param TicketRepository $ticketRepository
     * @param bool             $isPlaylist
     */
    public function __construct($name, EngineInterface $templating, UserService $userService, TicketRepository $ticketRepository, bool $isPlaylist = false)
    {
        parent::__construct($name, $templating);

        $this->userService = $userService;
        $this->ticketRepository = $ticketRepository;
        $this->isPlaylist = $isPlaylist;
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

        $eventBlock = $blockContext->getSetting('event_block');
        if (!$eventBlock instanceof EventBlock) {
            throw new NotFoundHttpException();
        }

        try {
            $user = $this->userService->getCurrentUser();
            $ticket = $this->ticketRepository->findOneBy(['user' => $user->getId(), 'event' => $event->getId()]);
        } catch (AccessDeniedException $e) {
            $user = null;
            $ticket = null;
        }

        if ($ticket instanceof Ticket && $ticket->isPaid() || ($user instanceof User && $user->hasRole('ROLE_ADMIN'))) {
            return $this->renderResponse($blockContext->getTemplate(), [
                'block' => $blockContext->getBlock(),
                'event_block' => $eventBlock,
                'is_playlist' => $this->isPlaylist,
            ], $response);
        }

        return new Response();
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'AppBundle:Redesign/Event:event.youtube_video_block.html.twig',
            'event' => null,
            'event_block' => null,
            'is_playlist' => false,
        ]);
    }
}
