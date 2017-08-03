<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Symfony\Component\HttpFoundation\Response;
use Stfalcon\Bundle\EventBundle\Entity\Mail;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction() {
        return array();
    }

    /**
     * @Route("/admin/event/{slug}/users/add", name="adminusersadd")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function addUsersAction(Event $event) {
        // @todo удалить этот метод. одноразовый харкод
        $em = $this->getDoctrine()->getManager();

        if (isset($_POST['users'])) {
            $users = explode("\r\n", $_POST['users']);

            foreach ($users as $data) {
                // данные с формы
                $dt = explode(' ', $data);
                unset($data);
                $data['name'] = $dt[0] . ' ' . $dt[1];
                $data['email'] = $dt[2];
                $data['discount'] = isset($dt[3]);

                $user = $this->get('fos_user.user_manager')->findUserBy(array('email' => $data['email']));

                // создаем нового пользователя
                if (!$user) {
                    $user = $this->get('fos_user.user_manager')->createUser();
                    $user->setEmail($data['email']);
                    $user->setFullname($data['name']);

                    // генерация временного пароля
                    $password = substr(str_shuffle(md5(time())), 5, 8);
                    $user->setPlainPassword($password);
                    $user->setEnabled(true);

                    $this->get('fos_user.user_manager')->updateUser($user);

                    // отправляем сообщение о регистрации
                    $text = "Приветствуем " . $user->getFullname() ."!

Вы были автоматически зарегистрированы на сайте Frameworks Days.

Ваш временный пароль: " . $password . "
Его можно сменить на странице " . $this->generateUrl('fos_user_change_password', array(), true) . "


---
С уважением,
Команда Frameworks Days";

                    $message = \Swift_Message::newInstance()
                        ->setSubject("Регистрация на сайте Frameworks Days")
                        // @todo refact
                        ->setFrom('orgs@fwdays.com', 'Frameworks Days')
                        ->setTo($user->getEmail())
                        ->setBody($text);

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
                    echo "<b>he has already paid participation in the conference!</b><br>";
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
                    echo "create a new payment<br>";
                    $payment = new Payment();

                    $payment->setUser($user);
                    $payment->addTicket($ticket);
                    $em->persist($payment);
                    $em->flush();

                    // обновляем шлюз и статус платежа
                    $payment->setGate('admin');
                    $payment->markedAsPaid();

                    // сохраняем все изменения
                    $em->flush();

                    echo "mark as paid<br>";
                }
            }

            echo 'complete';
            exit;
        }

        return [];
    }

    /**
     * Widget share contacts
     *
     * @return Response
     */
    public function widgetShareContactsAction()
    {
        /**
         * @var User $user
         */
        if (null !== ($user = $this->getUser())) {

            if ((null === $user->isAllowShareContacts()) && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                return $this->render('ApplicationDefaultBundle:Default:shareContacts.html.twig');
            }
        }

        return new Response();
    }

    /**
     * @Route("/share-contacts/{reply}", name="share_contacts")
     *
     * @param string $reply
     *
     * @Secure(roles="ROLE_USER")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function shareContactsAction($reply = 'no')
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if ('yes' == $reply) {
            $user->setAllowShareContacts(true);
        } else {
            $user->setAllowShareContacts(false);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $url = $this->getRequest()->headers->get("referer");

        return new RedirectResponse($url);
    }
}
