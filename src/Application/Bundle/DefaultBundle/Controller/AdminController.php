<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stfalcon\Bundle\EventBundle\Entity\Mail;

/**
 * Class AdminController.
 */
class AdminController extends Controller
{
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
        // @todo удалить этот метод. одноразовый харкод
        $em = $this->getDoctrine()->getManager();

        if (isset($_POST['users'])) {
            $users = explode("\r\n", $_POST['users']);

            foreach ($users as $data) {
                // данные с формы
                $dt = explode(' ', $data);
                unset($data);
                $data['name'] = $dt[0];
                $data['surname'] = $dt[1];
                $data['email'] = $dt[2];
                $data['discount'] = isset($dt[3]) && 'D' === strtoupper($dt[3]);

                $user = $this->get('fos_user.user_manager')->findUserBy(['email' => $data['email']]);

                // создаем нового пользователя
                if (!$user) {
                    /** @var User $user */
                    $user = $this->get('fos_user.user_manager')->createUser();
                    $user->setEmail($data['email'])
                        ->setName($data['name'])
                        ->setSurname($data['surname']);

                    // генерация временного пароля
                    $password = substr(str_shuffle(md5(time())), 5, 8);
                    $user->setPlainPassword($password);
                    $user->setEnabled(true);

                    $errors = $this->container->get('validator')->validate($user);
                    if ($errors->count() > 0) {
                        $this->addFlash('sonata_flash_info', $user->getFullname().' — User create Bad credentials!');
                        break;
                    }

                    $this->get('fos_user.user_manager')->updateUser($user);

                    // отправляем сообщение о регистрации
                    $body = $this->container->get('stfalcon_event.mailer_helper')->renderTwigTemplate(
                        'ApplicationUserBundle:Registration:automatically.html.twig',
                        [
                            'user' => $user,
                            'plainPassword' => $password,
                        ]
                    );

                    $message = \Swift_Message::newInstance()
                        ->setSubject('Регистрация на сайте Frameworks Days')
                        // @todo refact
                        ->setFrom('orgs@fwdays.com', 'Fwdays')
                        ->setTo($user->getEmail())
                        ->setBody($body, 'text/html');

                    // @todo каждый вызов отнимает память
                    $this->get('mailer')->send($message);

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
                $ticket = $em->getRepository('StfalconEventBundle:Ticket')
                    ->findOneBy(array('event' => $event->getId(), 'user' => $user->getId()));

                if (!$ticket) {
                    $ticket = new Ticket();
                    $ticket->setEvent($event);
                    $ticket->setUser($user);
                    $user->addWantsToVisitEvents($event);
                    $em->persist($ticket);
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
                    $ticket->setAmount($amount);
                    $ticket->setHasDiscount($data['discount']);
                    $ticket->setAmountWithoutDiscount($amountWithOutDiscount);

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
            '@ApplicationDefault/Admin/addUsers.html.twig',
            [
                'admin_pool' => $this->get('sonata.admin.pool'),
                'event' => $event,
                'price_blocks' => $priceBlocks,
                'event_slug' => $event->getSlug(),
            ]
        );
    }

    /**
     * Widget share contacts.
     *
     * @return Response
     */
    public function widgetShareContactsAction()
    {
        if (null !== ($user = $this->getUser())) {
            if ((null === $user->isAllowShareContacts()) && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                return $this->render('ApplicationDefaultBundle:Default:shareContacts.html.twig');
            }
        }

        return new Response();
    }

    /**
     * Show Statistic.
     *
     * @Route("/admin/statistic", name="admin_statistic_all")
     *
     * @return Response
     *
     * @Method({"GET", "POST"})
     */
    public function showStatisticAction()
    {
        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository('ApplicationUserBundle:User');

        $totalUsersCount = $repo->getCountBaseQueryBuilder()->getQuery()->getSingleScalarResult();

        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where('u.enabled = :enabled')
        ->setParameter('enabled', 1);
        $enabledUsersCount = $qb->getQuery()->getSingleScalarResult();

        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where('u.subscribe = :subscribed')
            ->setParameter('subscribed', 1);
        $subscribedUsersCount = $qb->getQuery()->getSingleScalarResult();

        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where('u.subscribe = :subscribed')
            ->setParameter('subscribed', 0);
        $unSubscribedUsersCount = $qb->getQuery()->getSingleScalarResult();

        //Кол-во людей которые не купили билеты никогда
        //Кол-во людей которые купили билеты на одну \ две \ три \ четыре\ пять \ и так далее любых конференций

        $usersTicketsCount = [];

        $ticketRepository = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Ticket');

        $paidTickets = $ticketRepository->getPaidTicketsCount();

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
        ksort($usersTicketsCount);

        $ticketsByEventGroup = $ticketRepository->getTicketsCountByEventGroup();

        $countsByGroup = [];

        foreach ($ticketsByEventGroup as $key => $item) {
            if (isset($countsByGroup[$item['name']][$item[1]])) {
                ++$countsByGroup[$item['name']][$item[1]];
            } else {
                $countsByGroup[$item['name']][$item[1]] = 1;
            }
        }
        foreach ($countsByGroup as $key => $item) {
            ksort($item);
            $countsByGroup[$key] = $item;
        }
        //сколько людей отказалось предоставлять свои данные партнерам
        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where('u.allowShareContacts = :allowShareContacts');
        $qb->setParameter('allowShareContacts', 0);
        $countRefusedProvideData = $qb->getQuery()->getSingleScalarResult();

        //сколько согласилось
        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where('u.allowShareContacts = :allowShareContacts');
        $qb->setParameter('allowShareContacts', 1);
        $countAgreedProvideData = $qb->getQuery()->getSingleScalarResult();

        //сколько еще не ответило
        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where($qb->expr()->isNull('u.allowShareContacts'));
        $countNotAnswered = $qb->getQuery()->getSingleScalarResult();

        //сколько было переходов
        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where($qb->expr()->isNotNull('u.userReferral'));
        $countUseReferralProgram = $qb->getQuery()->getSingleScalarResult();

        $event = $this
            ->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findOneBy([], ['date' => 'DESC']);

        $eventStatisticSlug = '';
        if ($event instanceof Event) {
            $eventStatisticSlug = $event->getSlug();
        }

        return $this->render('@ApplicationDefault/Statistic/statistic.html.twig', [
            'admin_pool' => $this->get('sonata.admin.pool'),
            'data' => [
                'countRefusedProvideData' => $countRefusedProvideData,
                'countAgreedProvideData' => $countAgreedProvideData,
                'countNotAnswered' => $countNotAnswered,
                'countUseReferralProgram' => $countUseReferralProgram,
                'totalUsersCount' => $totalUsersCount,
                'enabledUsersCount' => $enabledUsersCount,
                'subscribedUsersCount' => $subscribedUsersCount,
                'unSubscribedUsersCount' => $unSubscribedUsersCount,
                'haveTicketsCount' => $haveTickets,
                'usersTicketsCount' => $usersTicketsCount,
                'countsByGroup' => $countsByGroup,
                'event_statistic_slug' => $eventStatisticSlug,
            ],
        ]);
    }

    /**
     * Start mail action.
     *
     * @Route("/mail/{id}/start/{value}", name="admin_start_mail")
     *
     * @param Request $request Request
     * @param Mail    $mail    Mail
     * @param int     $value   Value
     *
     * @return JsonResponse
     */
    public function startMailAction(Request $request, Mail $mail, $value)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();

        $mail->setStart((bool) $value);
        $em->persist($mail);
        $em->flush();

        return new JsonResponse([
            'status' => true,
            'value' => $value,
        ]);
    }

    /**
     * @ParamConverter("event", options={"mapping": {"slug": "slug"}})
     *
     * @param Event $event
     *
     * @Route("/admin/event_statistic/{slug}", name="admin_event_statistic")
     * @Route("/admin/event_statistic", name="admin_event_without_slug_statistic")
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function showEventStatisticAction(Event $event)
    {
        $events = $this
            ->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy([], ['date' => 'DESC']);

        $eventStatisticHtml = $this->getEventStatistic($event);

        return $this->render('@ApplicationDefault/Statistic/event_statistic_page.html.twig', [
            'admin_pool' => $this->get('sonata.admin.pool'),
            'events' => $events,
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
    public function showEventsStatisticAction($checkedEvents = '')
    {
        $ticketRepository = $this->getDoctrine()->getRepository('StfalconEventBundle:Ticket');

        $events = $ticketRepository->getEventWithTicketsCount();
        if (empty($checkedEvents)) {
            $checkedEventsArr = null;
        } else {
            $checkedEventsArr = explode(';', $checkedEvents);
            array_pop($checkedEventsArr);
            $checkedEventsArr = array_flip($checkedEventsArr);
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

        return $this->render('@ApplicationDefault/Statistic/events_statistic_page.html.twig', [
            'admin_pool' => $this->get('sonata.admin.pool'),
            'events' => $events,
            'table_html' => $tableHtml,
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
        /** @var TicketCost $ticketBlock */
        foreach ($ticketBlocks as $ticketBlock) {
            $blockSold = $ticketBlock->recalculateSoldCount();
            $totalTicketCount += $ticketBlock->getCount();
            $totalSoldTicketCount += $blockSold;
        }

        $ticketsWithoutCostsCount = (int) $this->getDoctrine()->getRepository('StfalconEventBundle:Ticket')->getEventTicketsWithoutTicketCostCount($event);
        $totalSoldTicketCount += $ticketsWithoutCostsCount;
        $totalTicketCount += $ticketsWithoutCostsCount;

        $html = $this->renderView('@ApplicationDefault/Statistic/event_statistic.html.twig', [
            'wannaVisitEvent' => $wannaVisitEvent,
            'ticketBlocks' => $ticketBlocks,
            'totalTicketCount' => $totalTicketCount,
            'totalSoldTicketCount' => $totalSoldTicketCount,
            'totalTicketsWithoutCostsCount' => $ticketsWithoutCostsCount,
        ]);

        return $html;
    }

    /**
     * @param array $events
     *
     * @return string
     */
    private function getEventsTable($events)
    {
        $ticketRepository = $this->getDoctrine()->getRepository('StfalconEventBundle:Ticket');

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
                    $result['cnt'] = $ticketRepository->getUserVisitsEventCount($event['id'], $subEvent['id']);
                    if ($subEvent['cnt'] > 0) {
                        $result['percent'] = round($result['cnt'] * 100 / $subEvent['cnt'], 2);
                    } else {
                        $result['percent'] = 0;
                    }
                    $result['text'] = $result['cnt'].'&nbsp;('.$result['percent'].'&nbsp;%)';

                    $green = $maxGreen - round($deltaGreen * $result['percent'] / 100);
                    $div = $maxGreen / $green;
                    $otherColor = dechex(round($green / $div));
                    $result['color'] = '#'.$otherColor.dechex($green).$otherColor;
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

        $html = $this->renderView('@ApplicationDefault/Statistic/events_statistic_table.html.twig', [
            'events' => $events,
        ]);

        return $html;
    }
}
