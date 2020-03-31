<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Repository\TicketRepository;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * YoutubeVideoPaidEventBlockService.
 */
class YoutubePlaylistPaidEventBlockService extends YoutubeVideoPaidEventBlockService
{
    /**
     * ProgramEventBlockService constructor.
     *
     * @param string           $name
     * @param EngineInterface  $templating
     * @param UserService      $userService
     * @param TicketRepository $ticketRepository
     */
    public function __construct($name, EngineInterface $templating, UserService $userService, TicketRepository $ticketRepository)
    {
        parent::__construct($name, $templating, $userService, $ticketRepository, true);
    }
}
