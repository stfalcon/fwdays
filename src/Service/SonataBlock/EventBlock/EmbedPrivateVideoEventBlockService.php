<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\EventBlock;
use App\Entity\UserEventRegistration;
use App\Exception\RuntimeException;
use App\Repository\UserEventRegistrationRepository;
use App\Service\Ticket\TicketService;
use App\Service\User\UserService;
use App\Service\VideoAccess\GrandAccessVideoService;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use App\Traits\TicketRepositoryTrait;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * EmbedPrivateVideoEventBlockService.
 */
class EmbedPrivateVideoEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;
    use TicketRepositoryTrait;

    /** @var UserService */
    private $userService;

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

    /** @var UserEventRegistrationRepository */
    private $eventRegistrationRepository;

    /**
     * @param Environment                     $twig
     * @param UserService                     $userService
     * @param TicketService                   $ticketService
     * @param GrandAccessVideoService         $grandAccessVideoService
     * @param UserEventRegistrationRepository $eventRegistrationRepository
     */
    public function __construct(Environment $twig, UserService $userService, TicketService $ticketService, GrandAccessVideoService $grandAccessVideoService, UserEventRegistrationRepository $eventRegistrationRepository)
    {
        parent::__construct($twig);

        $this->userService = $userService;
        $this->ticketService = $ticketService;
        $this->grandAccessVideoService = $grandAccessVideoService;
        $this->eventRegistrationRepository = $eventRegistrationRepository;
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
        $eventBlock = $blockContext->getSetting('event_block');
        if (!$eventBlock instanceof EventBlock) {
            throw new RuntimeException();
        }

        $accessGrand = $this->accessSonataBlockService->isAccessGrand($eventBlock);
        if (!$accessGrand) {
            return new Response();
        }

        $event = $eventBlock->getEvent();

        try {
            $user = $this->userService->getCurrentUser();
            $tickets = $this->ticketRepository->getAllPaidForUserAndEvent($user, $event);
        } catch (AccessDeniedException $e) {
            $user = null;
            $tickets = [];
        }

        $accessGrand = EventBlock::VISIBILITY_ALL !== $eventBlock->getVisibility() || $this->grandAccessVideoService->isAccessGrand($this->grandAccessType, $event, $user, $tickets);

        if (!$accessGrand) {
            return new Response();
        }

        foreach ($tickets as $ticket) {
            $this->ticketService->setTickedUsedIfOnlineEvent($ticket);
        }

        $registration = empty($tickets) ? $this->eventRegistrationRepository->findOneForUserAndEvent($user, $event) : null;

        if ($registration instanceof UserEventRegistration) {
            $this->userService->setUserUsedRegistrationIfOnlineEvent($registration);
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
            'event_block' => null,
            'is_playlist' => false,
        ]);
    }
}
