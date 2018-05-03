<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stfalcon\Bundle\EventBundle\Entity\Mail;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

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
     * @Template()
     *
     * @return array
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
                $data['discount'] = isset($dt[3]);

                $user = $this->get('fos_user.user_manager')->findUserBy(array('email' => $data['email']));

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
                        echo 'User create Bad credentials!';
                        exit;
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

                    echo "#{$user->getId()} {$user->getFullname()} — Create a new user<br>";
                } else {
                    echo "<b>#{$user->getId()} {$user->getFullname()} — already registered</b><br>";
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

                    $em->persist($ticket);
                }

                if ($ticket->isPaid()) {
                    echo '<b>he has already paid participation in the conference!</b><br>';
                } else {
                    // цена участия (с учетом скидки)
                    $amount = $data['discount'] ? $_POST['amount'] * 0.8 : $_POST['amount'];
                    $ticket->setAmount($amount);
                    $ticket->setHasDiscount($data['discount']);
                    $ticket->setAmountWithoutDiscount($_POST['amount']);

                    $oldPayment = $ticket->getPayment();

                    if ($oldPayment) {
                        $oldPayment->removeTicket($ticket);
                        $em->persist($oldPayment);
                    }
                    echo 'create a new payment<br>';
                    $payment = (new Payment())
                        ->setUser($user)
                        ->setAmount($ticket->getAmount())
                        ->setBaseAmount($ticket->getAmountWithoutDiscount())
                        ->setGate('admin');
                    $payment->addTicket($ticket);
                    $ticket->setPayment($payment);
                    $em->persist($payment);
                    $em->flush();

                    $payment->markedAsPaid();
                    $em->flush();

                    echo 'mark as paid<br>';
                }
            }

            echo 'complete';
            exit;
        }

        return [];
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

        $paidTickets = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Ticket')
            ->getPaidTicketsCount();

        foreach ($paidTickets as $paidTicket) {
            if (isset($usersTicketsCount[$paidTicket[1]])) {
                $usersTicketsCount[$paidTicket[1]]++;
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
}
