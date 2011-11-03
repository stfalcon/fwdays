<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/admin/event/{slug}/users/add", name="adminusersadd")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function addUsersAction(Event $event)
    {
//        $data = $_POST['users'];
        // @todo разбить на строки и на значения
        $data = array(array('name' => 'Stepan Tanasiychuk', 'email' => 'stepan.tanasiychuk@gmail.com'),
            array('name' => 'Victor Paladiychuk', 'email' => 'victor_gugo@stfalcon.com'));

        foreach ($data as $d) {
            $user = $this->get('fos_user.user_manager')->findUserBy(array('email' => $d['email']));

            if ($user) {
                // проверяем или у него нет билетов на этот ивент
                $em = $this->getDoctrine()->getEntityManager();

                $ticket = $em->getRepository('StfalconEventBundle:Ticket')
                    ->findOneBy(array('event' => $event->getId(), 'user' => $user->getId()));
            } else {
                $user = $this->get('fos_user.user_manager')->createUser();
                $user->setEmail($d['email']);
                $user->setFullname($d['name']);
                $user->setPassword(substr(str_shuffle(md5(time())), 5, 8));

                $user->setEnabled(false);
                $this->get('fos_user.mailer')->sendConfirmationEmailMessage($user);
                $this->get('fos_user.user_manager')->updateUser($user);
                
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
            }
        }
            exit;

        return array();
    }

}
