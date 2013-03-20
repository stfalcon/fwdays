<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Mail;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

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
                    $url = $this->generateUrl('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
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

                    echo "#{$user->getId()} {$user->getFullname()} — создаем нового пользователя<br>";
                } else {
                    echo "<b>#{$user->getId()} {$user->getFullname()} — уже зарегистрирован</b><br>";
                }

                // обновляем информацию о компании
                $user->setCountry('Украина');
                if (isset($_POST['city'])) {
                    $user->setCity($_POST['city']);
                }

                $user->setCompany($_POST['company']);
                $em->persist($user);

                // проверяем или у него нет билетов на этот ивент
                $ticket = $em->getRepository('StfalconEventBundle:Ticket')
                        ->findOneBy(array('event' => $event->getId(), 'user' => $user->getId()));

                if (!$ticket) {
                    $ticket = new Ticket($event, $user);
                    $em->persist($ticket);
                }

                if ($ticket->isPaid()) {
                    echo "<b>он уже оплатил участие в конференции!</b><br>";
                } else {
                    $payment = $ticket->getPayment();

                    // цена участия (с учетом скидки)
                    $amount = $data['discount'] ? $_POST['amount'] * 0.8 : $_POST['amount'];

                    if ($payment) {
                        echo "<b>платеж уже создан!</b><br>";
                        // обновляем цену
                        $payment->setAmount($amount);
                        $payment->setHasDiscount($data['discount']);
                    } else {
                        echo "создаем новый платеж<br>";
                        $payment = new Payment($user, $amount, $data['discount']);
                    }
                    $payment->setAmountWithoutDiscount($_POST['amount']);

                    // обновляем шлюз и статус платежа
                    $payment->setGate('admin');
                    $payment->setStatus('paid');
                    $em->persist($payment);

                    $ticket->setPayment($payment);
                    $em->persist($ticket);

                    // сохраняем все изменения
                    $em->flush();

                    echo "отмечаем как оплачено<br>";
                }
            }

            echo 'complete';
            exit;
        }

        return array();
    }

}
