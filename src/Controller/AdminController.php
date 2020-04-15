<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventGroup;
use App\Entity\Mail;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\TicketCost;
use App\Entity\User;
use App\Helper\MailerHelper;
use App\Model\UserManager;
use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Service\LocalsRequiredService;
use App\Service\User\UserService;
use App\Traits\EntityManagerTrait;
use Doctrine\Common\Collections\Criteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController.
 */
class AdminController extends AbstractController
{
    use EntityManagerTrait;

    private $userManager;
    private $mailerHelper;
    private $pool;
    private $mailer;
    private $userRepository;
    private $ticketRepository;
    private $eventRepository;
    private $userService;

    /**
     * @param UserManager      $userManager
     * @param MailerHelper     $mailerHelper
     * @param Pool             $pool
     * @param \Swift_Mailer    $mailer
     * @param UserRepository   $userRepository
     * @param TicketRepository $ticketRepository
     * @param EventRepository  $eventRepository
     * @param UserService      $userService
     */
    public function __construct(UserManager $userManager, MailerHelper $mailerHelper, Pool $pool, \Swift_Mailer $mailer, UserRepository $userRepository, TicketRepository $ticketRepository, EventRepository $eventRepository, UserService $userService)
    {
        $this->userManager = $userManager;
        $this->mailerHelper = $mailerHelper;
        $this->pool = $pool;
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
        $this->ticketRepository = $ticketRepository;
        $this->eventRepository = $eventRepository;
        $this->userService = $userService;
    }

    /**
     * @Route("/admin/event/{slug}/users/add", name="adminusersadd")
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Event $event
     *
     * @return Response
     */
    public function addUsersAction(Event $event)
    {
        $em = $this->getDoctrine()->getManager();

        if (isset($_POST['users'])) {
            $users = explode("\r\n", $_POST['users']);

            foreach ($users as $data) {
                // данные с формы
                $dt = \explode(' ', $data);
                if (\count($dt) < 3) {
                    $this->addFlash('sonata_flash_info', 'Не достаточно данных!');
                    continue;
                }
                unset($data);
                $data['name'] = $dt[0];
                $data['surname'] = $dt[1];
                $data['email'] = $dt[2];
                $data['discount'] = isset($dt[3]) && 'D' === \strtoupper($dt[3]);

                $user = $this->userManager->findUserBy(['email' => $data['email']]);

                // создаем нового пользователя
                if (!$user instanceof User) {
                    $user = $this->userManager->createUser();
                    $user->setEmail($data['email'])
                        ->setName($data['name'])
                        ->setSurname($data['surname']);

                    // генерация временного пароля
                    $password = substr(str_shuffle(md5((string) time())), 5, 8);
                    $user->setPlainPassword($password);
                    $user->setEnabled(true);

                    $errors = $this->container->get('validator')->validate($user);
                    if ($errors->count() > 0) {
                        $this->addFlash('sonata_flash_info', $user->getFullname().' — User create Bad credentials!');
                        break;
                    }

                    $this->userManager->updateUser($user);

                    // отправляем сообщение о регистрации
                    $body = $this->mailerHelper->renderTwigTemplate(
                        'Registration:automatically.html.twig',
                        [
                            'user' => $user,
                            'plainPassword' => $password,
                        ]
                    );

                    $message = (new \Swift_Message())
                        ->setSubject('Регистрация на сайте Fwdays')
                        ->setFrom('orgs@fwdays.com', 'Fwdays')
                        ->setTo($user->getEmail())
                        ->setBody($body, 'text/html');

                    $this->mailer->send($message);

                    $this->addFlash('sonata_flash_info', $user->getFullname().' — Create a new user');
                } else {
                    $this->addFlash('sonata_flash_info', $user->getFullname().' — already registered');
                }

                // обновляем информацию о компании
                $user->setCountry('Украина');
                if (isset($_POST['city'])) {
                    $user->setCity($_POST['city']);
                }

                $user->setCompany($_POST['company']);
                $em->persist($user);
                $em->flush();

                // проверяем или у него нет билетов на этот ивент
                /** @var Ticket $ticket */
                $ticket = $em->getRepository(Ticket::class)
                    ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);

                if (!$ticket instanceof Ticket) {
                    $ticket = (new Ticket())
                        ->setEvent($event)
                        ->setUser($user)
                        ->setHideConditions(isset($_POST['hide_conditions']))
                    ;
                    $em->persist($ticket);
                    $this->userService->registerUserToEvent($user, $event, null, false);
                }

                if ($ticket->isPaid()) {
                    $this->addFlash('sonata_flash_info', $user->getFullname().' already paid participation in the conference!');
                } else {
                    // цена участия (с учетом скидки)
                    $priceBlockId = $_POST['block_id'];
                    $amountWithOutDiscount = $_POST['amount'];
                    /** @var TicketCost $ticketCost */
                    foreach ($event->getTicketsCost() as $ticketCost) {
                        if ($ticketCost->getId() === (int) $priceBlockId) {
                            $ticket->setTicketCost($ticketCost);
                            break;
                        }
                    }

                    $amount = $data['discount'] ? $amountWithOutDiscount * 0.8 : $amountWithOutDiscount;
                    $ticket
                        ->setAmount($amount)
                        ->setHasDiscount($data['discount'])
                        ->setAmountWithoutDiscount($amountWithOutDiscount)
                    ;

                    $oldPayment = $ticket->getPayment();

                    if ($oldPayment) {
                        $oldPayment->removeTicket($ticket);
                        $em->persist($oldPayment);
                    }
                    $this->addFlash('sonata_flash_info', 'create a new payment');
                    $payment = (new Payment())
                        ->setUser($user)
                        ->setAmount($ticket->getAmount())
                        ->setBaseAmount($ticket->getAmountWithoutDiscount())
                    ;
                    $payment->addTicket($ticket);
                    $ticket->setPayment($payment);
                    $em->persist($payment);
                    $em->flush();

                    $payment->setPaidWithGate(Payment::ADMIN_GATE);
                    $em->flush();

                    $this->addFlash('sonata_flash_info', 'mark as paid');
                }
            }

            $this->addFlash('sonata_flash_info', 'complete');
        }

        $priceBlocks = $event->getTicketsCost();

        return $this->render(
            'Admin/addUsers.html.twig',
            [
                'admin_pool' => $this->pool,
                'event' => $event,
                'price_blocks' => $priceBlocks,
                'event_slug' => $event->getSlug(),
            ]
        );
    }

    /**
     * Show Statistic.
     *
     * @Route("/admin/statistic", name="admin_statistic_all")
     *
     * @throws \Doctrine\ORM\Query\QueryException
     *
     * @return Response
     */
    public function showStatisticAction()
    {
        $totalUsersCount = $this->userRepository->getTotalUserCount();
        $subscribedUsersCount = $this->userRepository->getSubscribedUserCount();

        //Кол-во людей которые не купили билеты никогда
        //Кол-во людей которые купили билеты на одну \ две \ три \ четыре\ пять \ и так далее любых конференций
        $usersTicketsCount = [];
        $paidTickets = $this->ticketRepository->getPaidTicketsCount();
        foreach ($paidTickets as $paidTicket) {
            if (isset($usersTicketsCount[$paidTicket[1]])) {
                ++$usersTicketsCount[$paidTicket[1]];
            } else {
                $usersTicketsCount[$paidTicket[1]] = 1;
            }
        }

        $haveTickets = 0;
        foreach ($usersTicketsCount as $item) {
            $haveTickets += $item;
        }
        $usersTicketsCount[0] = $totalUsersCount - $haveTickets;
        \ksort($usersTicketsCount);
        $ticketsByEventGroup = $this->ticketRepository->getTicketsCountByEventGroup();
        $countsByGroup = [];

        foreach ($ticketsByEventGroup as $key => $item) {
            if (isset($countsByGroup[$item['name']][$item[1]])) {
                ++$countsByGroup[$item['name']][$item[1]];
            } else {
                $countsByGroup[$item['name']][$item[1]] = 1;
            }
        }
        foreach ($countsByGroup as $key => $item) {
            \ksort($item);
            $countsByGroup[$key] = $item;
        }
        $countRefusedProvideData = $this->userRepository->getProvideDataUserCount(false);
        $countAgreedProvideData = $this->userRepository->getProvideDataUserCount(true);

        $event = $this->eventRepository->findOneBy([], ['date' => Criteria::DESC]);
        $eventStatisticSlug = $event instanceof Event ? $event->getSlug() : '';

        $usersWithUkLocale = $this->userRepository->getUserCountByEmailLanguage(LocalsRequiredService::UK_EMAIL_LANGUAGE);
        $usersWithEnLocale = $this->userRepository->getUserCountByEmailLanguage(LocalsRequiredService::EN_EMAIL_LANGUAGE);

        return $this->render('Statistic/statistic.html.twig', [
            'admin_pool' => $this->pool,
            'data' => [
                'countRefusedProvideData' => $countRefusedProvideData,
                'countAgreedProvideData' => $countAgreedProvideData,
                'countNotAnswered' => $totalUsersCount - ($countAgreedProvideData + $countRefusedProvideData),
                'countUseReferralProgram' => $this->userRepository->getUserHasReferalCount(),
                'totalUsersCount' => $totalUsersCount,
                'enabledUsersCount' => $this->userRepository->getEnabledUserCount(),
                'subscribedUsersCount' => $subscribedUsersCount,
                'unSubscribedUsersCount' => $totalUsersCount - $subscribedUsersCount,
                'haveTicketsCount' => $haveTickets,
                'usersTicketsCount' => $usersTicketsCount,
                'countsByGroup' => $countsByGroup,
                'event_statistic_slug' => $eventStatisticSlug,
                'usersWithUkLocale' => \sprintf('%s (%s%%)', $usersWithUkLocale, \round($usersWithUkLocale * 100 / $totalUsersCount, 2)),
                'usersWithEnLocale' => \sprintf('%s (%s%%)', $usersWithEnLocale, \round($usersWithEnLocale * 100 / $totalUsersCount, 2)),
            ],
        ]);
    }

    /**
     * Start mail action.
     *
     * @Route("/mail/{id}/start/{value}", name="admin_start_mail")
     *
     * @param Request $request
     * @param Mail    $mail
     * @param int     $value
     *
     * @return JsonResponse
     */
    public function startMailAction(Request $request, Mail $mail, $value): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $mail->setStart((bool) $value);
        $this->persistAndFlush($mail);

        return new JsonResponse([
            'status' => true,
            'value' => $value,
        ]);
    }

    /**
     * @param Event $event
     *
     * @Route("/admin/event_statistic/{slug}", name="admin_event_statistic")
     * @Route("/admin/event_statistic", name="admin_event_without_slug_statistic")
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function showEventStatisticAction(Event $event): Response
    {
        $events = $this->eventRepository->findBy([], ['date' => Criteria::DESC]);
        $eventStatisticHtml = $this->getEventStatistic($event);

        return $this->render('Statistic/event_statistic_page.html.twig', [
            'admin_pool' => $this->pool,
            'events' => $events,
            'event' => $event,
            'event_statistic_html' => $eventStatisticHtml,
            'current_event_slug' => $event->getSlug(),
        ]);
    }

    /**
     * @Route("/admin/events_statistic/{checkedEvents}", name="admin_events_statistic")
     * @Route("/admin/events_statistic", name="admin_events_statistic_all")
     *
     * @param string $checkedEvents
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function showEventsStatisticAction(string $checkedEvents = ''): Response
    {
        $events = $this->ticketRepository->getEventWithTicketsCount();
        if (empty($checkedEvents)) {
            $checkedEventsArr = null;
        } else {
            $checkedEventsArr = \explode(';', $checkedEvents);
            \array_pop($checkedEventsArr);
            $checkedEventsArr = \array_flip($checkedEventsArr);
        }
        foreach ($events as $key => $event) {
            if (empty($checkedEventsArr)) {
                $events[$key]['checked'] = (int) $event['cnt'] > 90;
            } else {
                $events[$key]['checked'] = isset($checkedEventsArr[$event['id']]);
            }
            $events[$key]['slug'] = $event['slug'].' ('.$event['cnt'].')';
        }

        $tableHtml = $this->getEventsTable($events);

        return $this->render('Statistic/events_statistic_page.html.twig', [
            'admin_pool' => $this->pool,
            'events' => $events,
            'table_html' => $tableHtml,
        ]);
    }

    /**
     * @Route("/admin/users_not_buy_tickets", name="admin_user_tickets")
     *
     * @param Request $request
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response|StreamedResponse
     */
    public function usersNotBuyTicketAction(Request $request)
    {
        $checkEventId = $request->request->getInt('check_event');
        $checkType = $request->request->get('check_type', 'event');
        $hasTicketObjectId = 'event' === $checkType ? $request->request->getInt('has_ticket_event')
            : $request->request->getInt('has_ticket_group');

        $events = $this->eventRepository->findBy([], ['date' => Criteria::DESC]);
        $groups = $this->getDoctrine()->getRepository(EventGroup::class)
            ->findAll();

        if ($checkEventId > 0 && $hasTicketObjectId > 0) {
            $users = $this->userRepository->getUsersNotBuyTicket($checkEventId, $hasTicketObjectId, $checkType);
            if (\count($users)) {
                return $this->getCsvResponse($users);
            }
        }

        return $this->render('Statistic/user_ticket_statistic.html.twig', [
            'admin_pool' => $this->pool,
            'events' => $events,
            'groups' => $groups,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return string
     */
    private function getEventStatistic(Event $event)
    {
        $wannaVisitEvent = $event->getWantsToVisitCount();
        $ticketBlocks = $event->getTicketsCost();
        $totalTicketCount = 0;
        $totalSoldTicketCount = 0;

        $ticketsAmountSumByBlock = [];
        /** @var TicketCost $ticketBlock */
        foreach ($ticketBlocks as $ticketBlock) {
            $blockSold = $ticketBlock->recalculateSoldCount();
            $totalTicketCount += $ticketBlock->getCount();
            $totalSoldTicketCount += $blockSold;
            $ticketsAmountSumByBlock[$ticketBlock->getId()] = $this->ticketRepository->getAmountSumByBlock($ticketBlock);
        }

        $ticketsWithoutCostsCount = (int) $this->ticketRepository->getEventTicketsWithoutTicketCostCount($event);
        $totalSoldTicketCount += $ticketsWithoutCostsCount;
        $totalTicketCount += $ticketsWithoutCostsCount;

        return $this->renderView('Statistic/event_statistic.html.twig', [
            'wannaVisitEvent' => $wannaVisitEvent,
            'ticketBlocks' => $ticketBlocks,
            'totalTicketCount' => $totalTicketCount,
            'totalSoldTicketCount' => $totalSoldTicketCount,
            'totalTicketsWithoutCostsCount' => $ticketsWithoutCostsCount,
            'ticketsAmountSumByBlock' => $ticketsAmountSumByBlock,
        ]);
    }

    /**
     * @param array $events
     *
     * @return string
     */
    private function getEventsTable($events)
    {
        $minGreen = 127;
        $maxGreen = 255;
        $deltaGreen = $maxGreen - $minGreen;

        foreach ($events as $key => $event) {
            if (!$event['checked']) {
                continue;
            }
            foreach ($events as $subEvent) {
                if (!$subEvent['checked']) {
                    continue;
                }
                if ($event !== $subEvent) {
                    $result['cnt'] = $this->ticketRepository->getUserVisitsEventCount($event['id'], $subEvent['id']);
                    if ($subEvent['cnt'] > 0) {
                        $result['percent'] = \round($result['cnt'] * 100 / $subEvent['cnt'], 2);
                    } else {
                        $result['percent'] = 0;
                    }
                    $result['text'] = $result['cnt'].'&nbsp;('.$result['percent'].'&nbsp;%)';

                    $green = $maxGreen - \round($deltaGreen * $result['percent'] / 100);
                    $otherColor = (int) \round($green / ($maxGreen / $green));
                    $otherColor = \dechex($otherColor);
                    $result['color'] = '#'.$otherColor.\dechex((int) $green).$otherColor;
                } else {
                    $result = [
                        'cnt' => 0,
                        'percent' => 0,
                        'text' => '',
                        'color' => '#FFFFFF',
                    ];
                }
                $events[$key]['events'][$subEvent['slug']] = $result;
            }
        }

        return $this->renderView('Statistic/events_statistic_table.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * @param array  $users
     * @param string $filename
     *
     * @return Response
     */
    private function getCsvResponse($users, $filename = 'users.csv')
    {
        \array_unshift($users, ['Fullname', 'email']);

        $headers = [
            'Content-Disposition' => \sprintf('attachment; filename="%s"', $filename),
            'Content-Type' => 'text/csv',
        ];
        $callback = function () use ($users) {
            $usersFile = \fopen('php://output', 'w');
            if (false !== $usersFile) {
                foreach ($users as $fields) {
                    \fputcsv($usersFile, $fields);
                }
            }

            return $usersFile;
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
