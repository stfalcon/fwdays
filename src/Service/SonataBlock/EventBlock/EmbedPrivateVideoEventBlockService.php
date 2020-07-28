<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Event;
use App\Entity\EventBlock;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Repository\UserEventRegistrationRepository;
use App\Service\Ticket\TicketService;
use App\Service\User\UserService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * EmbedPrivateVideoEventBlockService.
 */
class EmbedPrivateVideoEventBlockService extends AbstractBlockService
{
    private $userService;
    private $ticketRepository;
    private $userRegistrationRepository;
    /** @var bool */
    private $isPlaylist = false;
    /** @var string */
    private $template = 'Redesign/Event/event.youtube_video_block.html.twig';

    /** @var TicketService */
    private $ticketService;

    /**
     * @param string                          $name
     * @param EngineInterface                 $templating
     * @param UserService                     $userService
     * @param TicketRepository                $ticketRepository
     * @param UserEventRegistrationRepository $userRegistrationRepository
     * @param TicketService                   $ticketService
     */
    public function __construct($name, EngineInterface $templating, UserService $userService, TicketRepository $ticketRepository, UserEventRegistrationRepository $userRegistrationRepository, TicketService $ticketService)
    {
        parent::__construct($name, $templating);

        $this->userService = $userService;
        $this->ticketRepository = $ticketRepository;
        $this->userRegistrationRepository = $userRegistrationRepository;
        $this->ticketService = $ticketService;
    }

    /** @param bool $isPlayList */
    public function setIsPlayList(bool $isPlayList): void
    {
        $this->isPlaylist = $isPlayList;
    }

    /** @param string $template */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
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
            $userRegisteredForFreeEvent = $event->isFreeParticipationCost() && $this->userRegistrationRepository->isUserRegisteredForEvent($user, $event);
        } catch (AccessDeniedException $e) {
            $user = null;
            $ticket = null;
            $userRegisteredForFreeEvent = false;
        }

        if ($ticket instanceof Ticket) {
            $this->ticketService->setTickedUsedIfOnlineEvent($ticket);
        }

        if (($ticket instanceof Ticket && $ticket->isPaid()) ||
            $userRegisteredForFreeEvent ||
            ($user instanceof User && ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')))
        ) {
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
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => $this->template,
            'event' => null,
            'event_block' => null,
            'is_playlist' => false,
        ]);
    }
}
