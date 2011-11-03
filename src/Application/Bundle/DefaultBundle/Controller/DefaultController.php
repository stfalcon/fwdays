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
    // @todo удалить этот метод. одноразовый код
        if (isset($_POST['users'])) {
            $data = explode("\r\n", $_POST['users']);

            $mail = new Mail();
            $mail->setTitle($event->getName() . ' -- подтверждение участия');
            $mail->setText('Доброго времени суток, %fullname%.

Напоминаем, что конференция состоится 12 ноября 2011 года, в конференц
зале отеля "Казацкий" (г. Киев, ул. Михайловская 1/3, рядом с Площадью
Независимости).

Регистрация участников начнется в 10 часов утра, а на 11:00
запланировано начало первого доклада. При себе необходимо
иметь любой документ удостоверяющий Вашу личность (паспорт,
водительское удостоверение, студенческий билет).

До встречи на конференции!

---
С уважением,
Орг. Комитет "Zend Framework Day"');

            foreach ($data as $d) {
                $dt = explode(' ', $d);
                unset($d);
                $d['name'] = $dt[0] . ' ' . $dt[1];
                $d['email'] = $dt[2];

                $user = $this->get('fos_user.user_manager')->findUserBy(array('email' => $d['email']));

                $em = $this->getDoctrine()->getEntityManager();
                if ($user) {
                    // проверяем или у него нет билетов на этот ивент
                    $ticket = $em->getRepository('StfalconEventBundle:Ticket')
                            ->findOneBy(array('event' => $event->getId(), 'user' => $user->getId()));
                    echo "#{$user->getId()} {$user->getFullname()} уже зарегистрирован ---<br>";
                } else {
                    $user = $this->get('fos_user.user_manager')->createUser();
                    $user->setEmail($d['email']);
                    $user->setFullname($d['name']);
                    $password = substr(str_shuffle(md5(time())), 5, 8);
                    $user->setPlainPassword($password);

                    $user->setEnabled(false);
//                    $this->get('fos_user.mailer')->sendConfirmationEmailMessage($user);

                    $this->get('fos_user.user_manager')->updateUser($user);

$url = $this->generateUrl('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
$text = "Приветствуем " . $user->getFullname() ."!

Вы были автоматически зарегистрированы на сайте Frameworks Days.
Для подтверждения вашего e-mail и активации аккаунта пройдите по ссылке " . $url . "

Ваш временный пароль: " . $password . "
Его можно сменить на странице " . $this->generateUrl('fos_user_change_password', array(), true) . "


---
С уважением,
Орг. Комитет \"Zend Framework Day\"";

$message = \Swift_Message::newInstance()
    ->setSubject("Регистрация на сайте Frameworks Days")
    // @todo refact
    ->setFrom('orgs@fwdays.com', 'Frameworks Days')
    ->setTo($user->getEmail())
    ->setBody($text);

// @todo каждый вызов отнимает память
$this->get('mailer')->send($message);

                    $ticket = null;
                }

                if (!$ticket) {
                    $ticket = new Ticket($event, $user);
                    $em->persist($ticket);
                    //                $em->flush();
                }

                if ($ticket->isPaid()) {
                    echo "#{$user->getId()} {$user->getFullname()} уже оплатил участие в конференции!!<br>";
                } else {
                    if (!($payment = $ticket->getPayment())) {
                        $payment = new Payment($user, 150);
                        $em->persist($payment);
                        $ticket->setPayment($payment);
                        $em->persist($ticket);
                        //                    $em->flush();
                    }
                    $payment->setGate('admin');
                    $payment->setStatus('paid');
                    $em->persist($payment);
                    $em->flush();

                    $text = $mail->replace(
                        array(
                            '%fullname%' => $user->getFullname(),
                            '%user_id%' => $user->getId(),
                        )
                    );

                    $message = \Swift_Message::newInstance()
                        ->setSubject($mail->getTitle())
                        // @todo refact
                        ->setFrom('orgs@fwdays.com', 'Frameworks Days')
                        ->setTo($user->getEmail())
                        ->setBody($text);

                    // @todo каждый вызов отнимает память
                    $this->get('mailer')->send($message);

                }
            }

            echo 'complete';
            exit;
        }

        return array();
    }

}
