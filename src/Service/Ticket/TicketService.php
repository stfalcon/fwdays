<?php

namespace App\Service\Ticket;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\PromoCode;
use App\Entity\Ticket;
use App\Entity\TicketCost;
use App\Entity\User;
use App\Model\DownloadTicketData;
use App\Model\EventStateData;
use App\Repository\PaymentRepository;
use App\Repository\TicketCostRepository;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * TicketService.
 */
class TicketService
{
    public const CAN_BUY_TICKET = 'can buy ticket';
    public const CAN_DOWNLOAD_TICKET = 'can download ticket';
    public const TICKETS_SOLD_OUT = 'all tickets sold out';
    public const CAN_WANNA_VISIT = 'can wanna visit';
    public const WAIT_FOR_PAYMENT_RECEIVE = 'wit for payment receive';
    public const EVENT_DONE = 'event done';
    public const EVENT_DEFAULT_STATE = 'event default state';

    public const STATES =
            [
                'row' => [
                        self::CAN_DOWNLOAD_TICKET => '',
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
                        self::EVENT_DONE => 'event-header__status',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg event-header__btn',
                    ],
                'event_fix_header' => [
                        self::CAN_DOWNLOAD_TICKET => '',
                        self::EVENT_DONE => 'fix-event-header__status',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg fix-event-header__btn',
                    ],
                'event_fix_header_mob' => [
                        self::CAN_DOWNLOAD_TICKET => '',
                        self::EVENT_DONE => 'fix-event-header__status fix-event-header__status--mob',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg fix-event-header__btn fix-event-header__btn--mob',
                    ],
                'event_action_mob' => [
                        self::CAN_DOWNLOAD_TICKET => 'event-action-mob__download',
                        self::EVENT_DONE => 'event-action-mob__status',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg event-action-mob__btn',
                    ],
                'price_block_mob' => [
                        self::CAN_DOWNLOAD_TICKET => '',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg cost__buy cost__buy--mob',
                    ],
                'price_block' => [
                        self::CAN_DOWNLOAD_TICKET => '',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg cost__buy',
                    ],
            ];

    private $em;
    private $paymentsConfig;
    private $translator;
    private $router;
    private $userService;

    /** @var EventStateInterface[] */
    private $eventStates = [];

    /**
     * TicketService constructor.
     *
     * @param EntityManager       $em
     * @param array               $paymentsConfig
     * @param TranslatorInterface $translator
     * @param RouterInterface     $router
     * @param UserService         $userService
     */
    public function __construct($em, $paymentsConfig, $translator, $router, UserService $userService)
    {
        $this->em = $em;
        $this->paymentsConfig = $paymentsConfig;
        $this->translator = $translator;
        $this->router = $router;
        $this->userService = $userService;
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
     * @param Ticket    $ticket
     * @param PromoCode $promoCode
     * @param float|int $discount
     *
     * @return string
     */
    public function setTicketBestDiscount($ticket, $promoCode, $discount = -1): string
    {
        if (-1 === $discount) {
            $discount = (float) $this->paymentsConfig['discount'];
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
        $ticket->setPromoCode($promoCode);
        $this->setTicketDiscount($ticket, $promoCode->getDiscountAmount() / 100);
        $promoCode->incTmpUsedCount();

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
        $this->em->flush();

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
     *
     * @return array
     */
    public function getTicketHtmlData(Event $event, string $position, ?TicketCost $ticketCost): array
    {
        $eventStateData = $this->createEventData($event, $position, $ticketCost);
        $downloadTicketData = $this->getDownloadTicketData($eventStateData);

        foreach ($this->eventStates as $eventStateProcessor) {
            if ($eventStateProcessor->support($eventStateData)) {
                return
                    [
                        'ticket_caption' => $downloadTicketData->getCaption(),
                        'ticket_class' => $downloadTicketData->getClass(),
                        'download_url' => $downloadTicketData->getUrl(),
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
     * @param Event $event
     *
     * @return TicketCost|null
     */
    public function getCurrentEventTicketCost($event)
    {
        /** @var TicketCostRepository $ticketCostRepository */
        $ticketCostRepository = $this->em->getRepository(TicketCost::class);
        $eventCosts = $ticketCostRepository->getEventEnabledTicketsCost($event);

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
     * @param Event $event
     *
     * @return int
     */
    public function getEventFreeTicketCount($event)
    {
        /** @var TicketCostRepository $ticketCostRepository */
        $ticketCostRepository = $this->em->getRepository(TicketCost::class);
        $eventCosts = $ticketCostRepository->getEventEnabledTicketsCost($event);
        $count = 0;
        /** @var TicketCost $cost */
        foreach ($eventCosts as $cost) {
            if (!$cost->isUnlimited()) {
                $count += $cost->getCount() - $cost->getSoldCount();
            }
        }

        return $count;
    }

    /**
     * @param User|null $user
     * @param Event     $event
     *
     * @return bool
     */
    public function isUserHasPaidTicketForEvent(?User $user, Event $event): bool
    {
        if (!$user instanceof User) {
            return false;
        }
        /** @var Ticket|null $ticket */
        $ticket = $this->em->getRepository(Ticket::class)
            ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);

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
     * @param Event           $event
     * @param string          $position
     * @param TicketCost|null $ticketCost
     *
     * @return EventStateData
     */
    private function createEventData(Event $event, string $position, ?TicketCost $ticketCost): EventStateData
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

        return (new EventStateData($event, $position, $ticketCost))
            ->setPendingPayment($payment)
            ->setTicket($ticket)
            ->setUser($user)
        ;
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

        if ($eventStateData->canDownloadTicket()) {
            $ticketCaption = $this->translator->trans('ticket.status.download');
            $position = $eventStateData->getPosition();
            $ticketClass = self::STATES[$position][self::CAN_DOWNLOAD_TICKET] ?? self::STATES[$position][self::EVENT_DEFAULT_STATE];
            if (!empty($ticketClass)) {
                $downloadUrl = $this->router->generate('event_ticket_download', ['slug' => $eventStateData->getEvent()->getSlug()]);
            }
        }

        return new DownloadTicketData($ticketCaption, $ticketClass, $downloadUrl);
    }
}
