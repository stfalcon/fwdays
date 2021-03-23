<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\TicketCost;
use App\Entity\User;

/**
 * EventStateData.
 */
class EventStateData
{
    /** @var Event */
    private $event;

    /** @var Payment|null */
    private $pendingPayment;

    /** @var string|null */
    private $mob = null;

    /** @var Ticket|null */
    private $ticket;

    /** @var string */
    private $position;

    /** @var TicketCost|null */
    private $ticketCost;

    /** @var User|null */
    private $user;

    /** @var string|null */
    private $forced;

    /**
     * @param Event           $event
     * @param string          $position
     * @param TicketCost|null $ticketCost
     * @param string|null     $forced
     */
    public function __construct(Event $event, string $position, ?TicketCost $ticketCost, ?string $forced)
    {
        $this->event = $event;
        $this->position = $position;
        $this->ticketCost = $ticketCost;
        $this->mob = \in_array($position, ['event_fix_header_mob', 'price_block_mob']) ? '_mob' : null;
        $this->forced = $forced;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     *
     * @return $this
     */
    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Payment|null
     */
    public function getPendingPayment(): ?Payment
    {
        return $this->pendingPayment;
    }

    /**
     * @param Payment|null $pendingPayment
     *
     * @return $this
     */
    public function setPendingPayment(?Payment $pendingPayment): self
    {
        $this->pendingPayment = $pendingPayment;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMob(): ?string
    {
        return $this->mob;
    }

    /**
     * @param string|null $mob
     *
     * @return $this
     */
    public function setMob(?string $mob): self
    {
        $this->mob = $mob;

        return $this;
    }

    /**
     * @return Ticket|null
     */
    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    /**
     * @param Ticket|null $ticket
     *
     * @return $this
     */
    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     *
     * @return $this
     */
    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return TicketCost|null
     */
    public function getTicketCost(): ?TicketCost
    {
        return $this->ticketCost;
    }

    /**
     * @param TicketCost|null $ticketCost
     *
     * @return $this
     */
    public function setTicketCost(?TicketCost $ticketCost): self
    {
        $this->ticketCost = $ticketCost;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return bool
     */
    public function canDownloadTicket(): bool
    {
        return $this->event->isActiveAndFuture() && $this->ticketIsPaid();
    }

    /**
     * @param string|null $type
     *
     * @return bool
     */
    public function canDownloadCertificate(?string $type): bool
    {
        $approveTypes = \explode('|', TicketCost::CERTIFICATED_TYPES);

        return
            \in_array($type, $approveTypes, true) &&
            $this->ticketIsPaid() &&
            null !== $this->event->findCertificateFileForType($type)
        ;
    }

    /**
     * @return string|null
     */
    public function getForced(): ?string
    {
        return $this->forced;
    }

    /**
     * @param string|null $forced
     *
     * @return $this
     */
    public function setForced(?string $forced)
    {
        $this->forced = $forced;

        return $this;
    }

    /**
     * @return bool
     */
    private function ticketIsPaid(): bool
    {
        return $this->ticket instanceof Ticket && $this->ticket->isPaid();
    }
}
