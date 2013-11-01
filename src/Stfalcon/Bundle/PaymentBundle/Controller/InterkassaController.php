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
use Stfalcon\Bundle\PaymentBundle\Service\IntercassaService;

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

        /** @var IntercassaService $intercassa */
        $intercassa = $this->container->get('stfalcon_payment.intercassa.service');

        $data = array(
            'ik_shop_id'      => $config['interkassa']['shop_id'],
            'ik_payment_desc' => $description,
            'ik_sign_hash'    => $intercassa->getSignHash($payment->getId(), $payment->getAmount())
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

        /** @var IntercassaService $intercassa */
        $intercassa = $this->container->get('stfalcon_payment.intercassa.service');

        if ($payment->getStatus() == Payment::STATUS_PENDING
            && $intercassa->checkPaymentStatus($params)
            // @todo временнный фикс бага с Интеркассой. они не проверяют ik_sign_hash (https://redmine.stfalcon.com/issues/10154)
            && $payment->getAmount() == $params['ik_payment_amount']
        ) {
            $payment->setStatus(Payment::STATUS_PAID);

            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();

            $resultMessage = 'Проверка контрольной подписи данных о платеже успешно пройдена!';

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

            /** @var $pdfGen \Stfalcon\Bundle\EventBundle\Helper\PdfGeneratorHelper */
            $pdfGen = $this->get('stfalcon_event.pdf_generator.helper');
            $html = $pdfGen->generateHTML($ticket);
            $outputFile = 'ticket-' . $event->getSlug() . '.pdf';

            $message = \Swift_Message::newInstance()
                ->setSubject($event->getName())
                ->setFrom('orgs@fwdays.com', 'Frameworks Days')
                ->setTo($user->getEmail())
                ->setBody($body, 'text/html')
                ->attach(\Swift_Attachment::newInstance($pdfGen->generatePdfFile($html, $outputFile)));

            $this->get('mailer')->send($message);
        } else {
            $resultMessage = 'Проверка контрольной подписи данных о платеже провалена!';
        }

        return array(
            'message' => $resultMessage
        );
    }
}
