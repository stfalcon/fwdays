<?php

namespace App\Service\Ticket;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\PromoCode;
use App\Entity\Ticket;
use App\Entity\TicketCost;
use App\Entity\User;
use App\Model\DownloadCertificateData;
use App\Model\DownloadTicketData;
use App\Model\EventStateData;
use App\Repository\PaymentRepository;
use App\Repository\TicketCostRepository;
use App\Repository\TicketRepository;
use App\Service\Discount\DiscountService;
use App\Service\User\UserService;
use App\Traits\EntityManagerTrait;
use App\Traits\RouterTrait;
use App\Traits\TranslatorTrait;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * TicketService.
 */
class TicketService
{
    use EntityManagerTrait;
    use TranslatorTrait;
    use RouterTrait;

    public const CAN_BUY_TICKET = 'can buy ticket';
    public const CAN_DOWNLOAD_TICKET = 'can download ticket';
    public const CAN_DOWNLOAD_CERTIFICATE = 'can download certificate';
    public const TICKETS_SOLD_OUT = 'all tickets sold out';
    public const EVENT_REGISTRATION_OPEN = 'event registration open';
    public const WAIT_FOR_PAYMENT_RECEIVE = 'wait for payment receive';
    public const EVENT_DONE = 'event done';
    public const EVENT_REGISTRATION_CLOSE = 'event registration close';
    public const EVENT_DEFAULT_STATE = 'event default state';

    public const STATES =
            [
                'row' => [
                        self::EVENT_DONE => 'event-row__status',
                        self::EVENT_DEFAULT_STATE => 'event-row__btn btn btn--primary btn--sm',
                    ],
                'card' => [
                        self::CAN_DOWNLOAD_TICKET => 'event-card__download',
                        self::EVENT_DONE => 'event-card__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--sm event-card__btn',
                    ],
                'event_header' => [
                        self::CAN_DOWNLOAD_TICKET => 'event-card__download',
                        self::CAN_DOWNLOAD_CERTIFICATE => 'event-card__download',
                        self::EVENT_DONE => 'event-header__status',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg event-header__btn',
                    ],
                'event_event_fix_header' => [
                        self::EVENT_DONE => 'fix-event-header__status',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg fix-event-header__btn',
                    ],
                'event_event_fix_header_mob' => [
                        self::EVENT_DONE => 'fix-event-header__status fix-event-header__status--mob',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg fix-event-header__btn fix-event-header__btn--mob',
                    ],
                'report_event_fix_header' => [
                        self::EVENT_DONE => 'fix-event-header__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg fix-event-header__btn',
                ],
                'report_event_fix_header_mob' => [
                        self::EVENT_DONE => 'fix-event-header__status fix-event-header__status--mob',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg fix-event-header__btn fix-event-header__btn--mob',
                ],
                'event_action_mob' => [
                        self::CAN_DOWNLOAD_TICKET => 'event-action-mob__download',
                        self::EVENT_DONE => 'event-action-mob__status',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg event-action-mob__btn',
                    ],
                'price_block_mob' => [
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg cost__buy cost__buy--mob',
                    ],
                'price_block' => [
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg event-cost__btn',
                    ],
            ];

    private $userService;
    private $ticketRepository;

    /** @var DiscountService */
    private $discountService;

    /** @var EventStateInterface[] */
    private $eventStates = [];

    /**
     * @param UserService      $userService
     * @param TicketRepository $ticketRepository
     * @param DiscountService  $discountService
     */
    public function __construct(UserService $userService, TicketRepository $ticketRepository, DiscountService $discountService)
    {
        $this->userService = $userService;
        $this->ticketRepository = $ticketRepository;
        $this->discountService = $discountService;
    }

    /**
     * Check discount for ticket.
     *
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function isMustBeDiscount($ticket)
    {
        $event = $ticket->getEvent();
        $user = $ticket->getUser();

        if (!$event->getUseDiscounts()) {
            return false;
        }
        /** @var PaymentRepository $repository */
        $repository = $this->em->getRepository(Payment::class);

        $paidPayments = $repository->findPaidPaymentsForUser($user);

        return \count($paidPayments) > 0;
    }

    /**
     * Set Ticket Amount with recalculate discount.
     *
     * @param Ticket     $ticket
     * @param float      $amount
     * @param bool       $isMustBeDiscount
     * @param TicketCost $currentTicketCost
     */
    public function setTicketAmount($ticket, $amount, $isMustBeDiscount, $currentTicketCost): void
    {
        $ticket->setAmountWithoutDiscount($amount);
        $ticket->setAmount($amount);
        $ticket->setTicketCost($currentTicketCost);
        /** -1 flag means you need to discount in the configuration */
        $discount = $isMustBeDiscount ? -1 : 0;
        $this->setTicketBestDiscount($ticket, $ticket->getPromoCode(), $discount);
    }

    /**
     * Set the best (from promo code or standard discount) discount for ticket.
     *
     * @param Ticket         $ticket
     * @param PromoCode|null $promoCode
     * @param float|int      $discount
     *
     * @return string
     */
    public function setTicketBestDiscount(Ticket $ticket, ?PromoCode $promoCode, $discount = -1): string
    {
        if (-1 === $discount) {
            $discount = $this->discountService->getFloatDiscount();
        }

        if ($promoCode instanceof PromoCode && $promoCode->getDiscountAmount() / 100 > $discount) {
            $this->setTicketPromoCode($ticket, $promoCode);
            $result = PromoCode::PROMOCODE_APPLIED;
        } else {
            $ticket->setPromoCode(null);
            $this->setTicketDiscount($ticket, $discount);
            $result = PromoCode::PROMOCODE_LOW_THAN_DISCOUNT;
        }

        return $result;
    }

    /**
     * Set Ticket promo-code.
     *
     * @param Ticket    $ticket
     * @param PromoCode $promoCode
     *
     * @return Ticket
     */
    public function setTicketPromoCode($ticket, $promoCode)
    {
        $oldPromoCode = $ticket->getPromoCode();
        $isNewPromoCode = !$oldPromoCode instanceof PromoCode || $oldPromoCode->getId() !== $promoCode->getId();
        if ($isNewPromoCode) {
            $ticket->setPromoCode($promoCode);
            $promoCode->incTmpUsedCount();
        }
        $this->setTicketDiscount($ticket, $promoCode->getDiscountAmount() / 100);

        return $ticket;
    }

    /**
     * Set ticket discount.
     *
     * @param Ticket $ticket
     * @param float  $discount
     *
     * @return Ticket
     */
    public function setTicketDiscount($ticket, $discount)
    {
        $amountWithDiscount = $ticket->getAmountWithoutDiscount() - ($ticket->getAmountWithoutDiscount() * $discount);
        $ticket
            ->setAmount($amountWithDiscount)
            ->setHasDiscount($ticket->getAmount() !== $ticket->getAmountWithoutDiscount());
        $this->em->flush();

        return $ticket;
    }

    /**
     * Create ticket for User and Event.
     *
     * @param Event $event
     * @param User  $user
     *
     * @return Ticket
     */
    public function createTicket(Event $event, User $user): Ticket
    {
        $ticket = (new Ticket())
            ->setEvent($event)
            ->setUser($user)
            ->setAmountWithoutDiscount($event->getCost())
            ->setAmount($event->getCost());
        $this->em->persist($ticket);
        $this->em->flush($ticket);

        return $ticket;
    }

    /**
     * @param EventStateInterface $eventState
     */
    public function addEventState(EventStateInterface $eventState): void
    {
        $this->eventStates[] = $eventState;
    }

    /**
     * @param Event           $event
     * @param string          $position
     * @param TicketCost|null $ticketCost
     * @param string|null     $forced
     *
     * @return array
     */
    public function getTicketHtmlData(Event $event, string $position, ?TicketCost $ticketCost, ?string $forced): array
    {
        $eventStateData = $this->createEventData($event, $position, $ticketCost, $forced);
        $downloadTicketData = $this->getDownloadTicketData($eventStateData);
        $downloadCertificateData = $this->getDownloadCertificateData($eventStateData);

        foreach ($this->eventStates as $eventStateProcessor) {
            if ($eventStateProcessor->support($eventStateData)) {
                return
                    [
                        'ticket' => $downloadTicketData->getTwigDate(),
                        'certificate' => $downloadCertificateData->getTwigDate(),
                        'href' => $eventStateProcessor->getHref($eventStateData),
                        'class' => $eventStateProcessor->getClass($eventStateData),
                        'caption' => $eventStateProcessor->getCaption($eventStateData),
                        'isDiv' => $eventStateProcessor->isDiv(),
                        'data' => $event->getSlug(),
                        'id' => \sprintf('%s-%s', $position, $event->getSlug()),
                    ];
            }
        }

        throw new UnprocessableEntityHttpException();
    }

    /**
     * @param Event       $event
     * @param string|null $type
     *
     * @return TicketCost|null
     */
    public function getCurrentEventTicketCost(Event $event, ?string $type)
    {
        /** @var TicketCostRepository $ticketCostRepository */
        $ticketCostRepository = $this->em->getRepository(TicketCost::class);
        $eventCosts = $ticketCostRepository->getEventEnabledTicketsCost($event, $type);

        $currentTicketCost = null;

        /** @var TicketCost $cost */
        foreach ($eventCosts as $cost) {
            if ($cost->isHaveTemporaryCount()) {
                $currentTicketCost = $cost;
                break;
            }
        }

        return $currentTicketCost;
    }

    /**
     * @param User|null   $user
     * @param Event       $event
     * @param string|null $type
     *
     * @return bool
     */
    public function isUserHasPaidTicketForEvent(?User $user, Event $event, ?string $type): bool
    {
        if (!$user instanceof User) {
            return false;
        }
        $ticket = $this->ticketRepository->findOneForEventAndUser($event, $user, $type);

        return $ticket instanceof Ticket && $ticket->isPaid();
    }

    /**
     * @param User|null $user
     * @param Ticket    $ticket
     */
    public function setNewUserToTicket(?User $user, Ticket $ticket): void
    {
        $oldUser = $ticket->getUser();
        if ($user instanceof User && !$user->isEqualTo($oldUser)) {
            $user->addTicket($ticket);
        }
    }

    /**
     * @param Ticket $ticket
     */
    public function setTickedUsedIfOnlineEvent(Ticket $ticket): void
    {
        $event = $ticket->getEvent();

        if (!$ticket->isPaid() || $ticket->isUsed() || !$event instanceof Event || !$event->isActive() || $event->isAdminOnly() || !$event->isOnline()) {
            return;
        }

        $this->userService->setUsedIfInEventDateRange($event, $ticket);
    }

    /**
     * @param Event           $event
     * @param string          $position
     * @param TicketCost|null $ticketCost
     * @param string|null     $forced
     *
     * @return EventStateData
     */
    private function createEventData(Event $event, string $position, ?TicketCost $ticketCost, ?string $forced): EventStateData
    {
        $ticket = null;
        $payment = null;
        $user = $this->userService->getCurrentUser(UserService::RESULT_RETURN_IF_NULL);
        if ($user instanceof User) {
            /** @var PaymentRepository $paymentRepository */
            $paymentRepository = $this->em->getRepository(Payment::class);
            $payment = $paymentRepository->findPendingPaymentByUserAndEvent($user, $event);
            /** @var Ticket|null $ticket */
            $ticket = $this->em->getRepository(Ticket::class)
                ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);
        }

        return (new EventStateData($event, $position, $ticketCost, $forced))
            ->setPendingPayment($payment)
            ->setTicket($ticket)
            ->setUser($user)
        ;
    }

    /**
     * @param EventStateData $eventStateData
     *
     * @return DownloadCertificateData
     */
    private function getDownloadCertificateData(EventStateData $eventStateData): DownloadCertificateData
    {
        $ticketCaption = null;
        $downloadUrl = null;

        $ticketCost = $eventStateData->getTicket() instanceof Ticket ? $eventStateData->getTicket()->getTicketCost() : null;
        $type = $ticketCost instanceof TicketCost ? $ticketCost->getType() : null;

        $position = $eventStateData->getPosition();
        $ticketClass = self::STATES[$position][self::CAN_DOWNLOAD_CERTIFICATE] ?? null;

        if (null !== $ticketClass && $eventStateData->canDownloadCertificate($type)) {
            $ticketCaption = $this->translator->trans('ticket.status.certificate');
            $downloadUrl = $this->router->generate('event_certificate_download', ['slug' => $eventStateData->getEvent()->getSlug(), 'type' => $type]);
        }

        return new DownloadCertificateData($ticketCaption, $ticketClass, $downloadUrl);
    }

    /**
     * @param EventStateData $eventStateData
     *
     * @return DownloadTicketData
     */
    private function getDownloadTicketData(EventStateData $eventStateData): DownloadTicketData
    {
        $ticketClass = null;
        $ticketCaption = null;
        $downloadUrl = null;
        $ticketCost = $eventStateData->getTicketCost();
        $type = $ticketCost instanceof TicketCost ? $ticketCost->getType() : null;

        if ($eventStateData->canDownloadTicket()) {
            $ticketCaption = $this->translator->trans('ticket.status.download');
            $position = $eventStateData->getPosition();
            $ticketClass = self::STATES[$position][self::CAN_DOWNLOAD_TICKET] ?? null;
            if (!empty($ticketClass)) {
                $downloadUrl = $this->router->generate('event_ticket_download', ['slug' => $eventStateData->getEvent()->getSlug(), 'type' => $type]);
            }
        }

        return new DownloadTicketData($ticketCaption, $ticketClass, $downloadUrl);
    }
}
