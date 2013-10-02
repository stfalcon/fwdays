<?php

namespace Stfalcon\Bundle\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Stfalcon\Bundle\PaymentBundle\Form\Payments\Interkassa\PayType;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Mail;

/**
 * Class InterkassaController
 */
class InterkassaController extends Controller
{
    /**
     * @param Event   $event   Event
     * @param User    $user    User
     * @param Payment $payment Payment
     *
     * @return array
     *
     * @Template()
     * @Secure(roles="ROLE_USER")
     */
    public function payAction($event, $user, $payment)
    {
        $config = $this->container->getParameter('stfalcon_payment.config');

        $description = 'Оплата участия в конференции ' . $event->getName()
                       . '. Плательщик ' . $user->getFullname() . ' (#' . $user->getId() . ')';

        $data = array(
            'ik_shop_id'      => $config['interkassa']['shop_id'],
            'ik_payment_desc' => $description,
            'ik_sign_hash'    => $this->_getSignHash($payment->getId(), $payment->getAmount())
        );

        return array(
            'data'    => $data,
            'event'   => $event,
            'payment' => $payment
        );
    }

    /**
     * Принимает ответ от шлюза
     *
     * @return array
     *
     * @Route("/payments/interkassa/status")
     * @Template()
     */
    public function statusAction()
    {
//        $params = $this->getRequest()->request->all();
        $params = $_POST;
        /** @var \Stfalcon\Bundle\PaymentBundle\Entity\Payment $payment */
        $payment = $this->getDoctrine()
            ->getRepository('StfalconPaymentBundle:Payment')
            ->findOneBy(array('id' => $params['ik_payment_id']));

        $dateFormatter = new \IntlDateFormatter(
            'ru-RU',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            date_default_timezone_get(),
            \IntlDateFormatter::GREGORIAN,
            'd MMMM Y'
        );

        if ($payment->getStatus() == Payment::STATUS_PENDING
            && $this->_checkPaymentStatus($params)
            && $payment->getAmount() != $params['ik_payment_amount']
        ) {
            $resultMessage = 'Проверка контрольной подписи данных о платеже успешно пройдена!';

            $payment->setStatus(Payment::STATUS_PAID);

            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();

            // Render and send email
            /** @var $ticket \Stfalcon\Bundle\EventBundle\Entity\Ticket */
            $ticket = $this->getDoctrine()->getRepository('StfalconEventBundle:Ticket')->findOneBy(array(
                'payment' => $payment
            ));

            $user  = $ticket->getUser();
            $event = $ticket->getEvent();

            $twig = $this->get('twig');

            $successPaymentTemplateContent = $twig->loadTemplate('StfalconEventBundle:Interkassa:success_payment.html.twig')
                ->render(array(
                    'event_slug' => $event->getSlug()
                ));

            $mail = new Mail();
            $mail->setEvent($event);
            $mail->setText($successPaymentTemplateContent);

            // Get base template for email
            $emailTemplateContent = $twig->loadTemplate('StfalconEventBundle::email.html.twig');

            $text = $mail->replace(
                array(
                    '%fullname%' => $user->getFullName(),
                    '%event%'    => $event->getName(),
                    '%date%'     => $dateFormatter->format($event->getDate()),
                    '%place%'    => $event->getPlace(),
                )
            );

            $body = $emailTemplateContent->render(array(
                'text'               => $text,
                'mail'               => $mail,
                'add_bottom_padding' => true
            ));

            $message = \Swift_Message::newInstance()
                ->setSubject($event->getName())
                ->setFrom('orgs@fwdays.com', 'Frameworks Days')
                ->setTo($user->getEmail())
                ->setBody($body, 'text/html');

            $this->get('mailer')->send($message);
        } else {
            $resultMessage = 'Проверка контрольной подписи данных о платеже провалена!';
        }

        return array(
            'message' => $resultMessage
        );
    }

    /**
     * Проверяет валидность и статус платежа
     *
     * @param array $params Array of parameters
     *
     * @return boolean
     */
    private function _checkPaymentStatus($params)
    {
        if (!array_key_exists('ik_shop_id', $params) ||
            !array_key_exists('ik_payment_amount', $params) ||
            !array_key_exists('ik_payment_id', $params) ||
            !array_key_exists('ik_paysystem_alias', $params) ||
            !array_key_exists('ik_baggage_fields', $params) ||
            !array_key_exists('ik_payment_state', $params) ||
            !array_key_exists('ik_trans_id', $params) ||
            !array_key_exists('ik_currency_exch', $params) ||
            !array_key_exists('ik_fees_payer', $params)) {
            return false;
        }

        $config = $this->container->getParameter('stfalcon_payment.config');

        $crc = md5(
            $params['ik_shop_id'] . ':' .
            $params['ik_payment_amount'] . ':' .
            $params['ik_payment_id'] . ':' .
            $params['ik_paysystem_alias'] . ':' .
            $params['ik_baggage_fields'] . ':' .
            $params['ik_payment_state'] . ':' .
            $params['ik_trans_id'] . ':' .
            $params['ik_currency_exch'] . ':' .
            $params['ik_fees_payer'] . ':' .
            $config['interkassa']['secret']
        );

        $paymentIsSuccess = ('success' == $params['ik_payment_state']);

        if (strtoupper($params['ik_sign_hash']) === strtoupper($crc) && $paymentIsSuccess) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * CRC-подпись для запроса на шлюз
     *
     * @param int   $paymentId Payment ID
     * @param float $sum       Sum
     *
     * @return string
     */
    protected function _getSignHash($paymentId, $sum)
    {
        $config = $this->container->getParameter('stfalcon_payment.config');

        $params['ik_shop_id']         = $config['interkassa']['shop_id'];
        $params['ik_payment_amount']  = $sum;
        $params['ik_payment_id']      = $paymentId;
        $params['ik_paysystem_alias'] = '';
        $params['ik_baggage_fields']  = '';

        $hash = md5(
            $params['ik_shop_id'] . ':' .
            $params['ik_payment_amount'] . ':' .
            $params['ik_payment_id'] . ':' .
            $params['ik_paysystem_alias'] . ':' .
            $params['ik_baggage_fields'] . ':' .
            $config['interkassa']['secret']
        );

        return $hash;
    }
}
