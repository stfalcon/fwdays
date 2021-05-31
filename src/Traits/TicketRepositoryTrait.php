<?php

declare(strict_types=1);

namespace App\Traits;

use App\Repository\TicketRepository;

/**
 * TicketRepositoryTrait.
 */
trait TicketRepositoryTrait
{
    /** @var TicketRepository */
    protected $ticketRepository;

    /**
     * @param TicketRepository $ticketRepository
     *
     * @required
     */
    public function setTicketRepository(TicketRepository $ticketRepository): void
    {
        $this->ticketRepository = $ticketRepository;
    }
}
