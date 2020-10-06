<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Event;
use App\Entity\EventBlock;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Service\Ticket\TicketService;
use App\Service\User\UserService;
use App\Service\VideoAccess\GrandAccessVideoService;
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

    /** @var TicketRepository */
    private $ticketRepository;

    /** @var bool */
    private $isPlaylist = false;
    /** @var string */
    private $template = 'Redesign/Event/event.youtube_video_block.html.twig';

    /** @var TicketService */
    private $ticketService;

    /** @var string */
    private $grandAccessType = '';

    /** @var GrandAccessVideoService */
    private $grandAccessVideoService;

    /**
     * @param string                  $name
     * @param EngineInterface         $templating
     * @param UserService             $userService
     * @param TicketRepository        $ticketRepository
     * @param TicketService           $ticketService
     * @param GrandAccessVideoService $grandAccessVideoService
     */
    public function __construct($name, EngineInterface $templating, UserService $userService, TicketRepository $ticketRepository, TicketService $ticketService, GrandAccessVideoService $grandAccessVideoService)
    {
        parent::__construct($name, $templating);

        $this->userService = $userService;
        $this->ticketRepository = $ticketRepository;
        $this->ticketService = $ticketService;
        $this->grandAccessVideoService = $grandAccessVideoService;
    }

    /** @param bool $isPlayList */
    public function setIsPlayList(bool $isPlayList): void
    {
        $this->isPlaylist = $isPlayList;
    }

    /**
     * @param string $grandAccessType
     *
     * @return $this
     */
    public function setGrandAccessType(string $grandAccessType): self
    {
        $this->grandAccessType = $grandAccessType;

        return $this;
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
            $tickets = $this->ticketRepository->getAllPaidForUserAndEvent($user, $event);
        } catch (AccessDeniedException $e) {
            $user = null;
            $tickets = [];
        }

        foreach ($tickets as $ticket) {
            $this->ticketService->setTickedUsedIfOnlineEvent($ticket);
        }

        $accessGrand = $this->grandAccessVideoService->isAccessGrand($this->grandAccessType, $event, $user, $tickets);

        if (!$accessGrand) {
            return new Response();
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'event_block' => $eventBlock,
            'is_playlist' => $this->isPlaylist,
        ], $response);
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
