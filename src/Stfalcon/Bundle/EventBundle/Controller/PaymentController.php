<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// @todo перенести в EventBundle
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

// @todo тут не повинно бути цього
use Stfalcon\Bundle\EventBundle\Entity\Mail;

/**
 * Контроллер оплаты и статусов платежей
 */
class PaymentController extends Controller {

    /**
     * Здесь мы получаем уведомления о статусе платежа и отмечаем платеж как успешный (или не отмечаем)
     * Также рассылаем письма и билеты всем, кто был привязан к платежу
     *
     * @Route("/payment/interaction", name="payment_interaction")
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function interactionAction(Request $request) {
        /** @var Payment $payment */
        $payment = $this->getDoctrine()
            ->getRepository('StfalconPaymentBundle:Payment')
            ->findOneBy(array('id' => $request->get('ik_pm_no')));

        if (!$payment) {
            throw new Exception('Платеж №' . $request->get('ik_pm_no') . ' не найден!');
        }

        // @var InterkassaService $interkassa
        $interkassa = $this->container->get('stfalcon_event.interkassa.service');
        if ($payment->isPending() && 1) { //$interkassa->checkPayment($payment, $request)) {
            $payment->markedAsPaid();
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            /** @var Ticket  $ticket */
            foreach ($payment->getTickets() as $ticket) {
                // розсилка квитків
                // @todo тут має смикатись сервіс який розсилає мильники про успішну оплату квитків + пдф в аттачі
                $user  = $ticket->getUser();
                $event = $ticket->getEvent();

                $twig = $this->get('twig');

                // @todo ачуметь.. екшн в одному бандлі. вьюшка в іншому
                $successPaymentTemplateContent = $twig->loadTemplate('StfalconEventBundle:Interkassa:success_payment.html.twig')
                    ->render(array(
                        'event_slug' => $event->getSlug()
                    ));

                $mail = new Mail();
                $mail->addEvent($event);
                $mail->setText($successPaymentTemplateContent);

                // Get base template for email
                $emailTemplateContent = $twig->loadTemplate('StfalconEventBundle::email.html.twig');

                $dateFormatter = new \IntlDateFormatter(
                    'ru-RU',
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::NONE,
                    date_default_timezone_get(),
                    \IntlDateFormatter::GREGORIAN,
                    'd MMMM Y'
                );

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
                    ->attach(\Swift_Attachment::newInstance($pdfGen->generatePdfFile($html, $outputFile), $outputFile));

                $this->get('mailer')->send($message);
            }

            return new Response('SUCCESS', 200);
        }

        return new Response('FAIL', 400);
    }

    /**
     * Платеж проведен успешно. Показываем пользователю соответствующее сообщение.
     *
     * @Route("/payment/success", name="payment_success")
     * @Template()
     *
     * @return array
     */
    public function successAction()
    {
        return array();
    }

    /**
     * Возникла ошибка при проведении платежа. Показываем пользователю соответствующее сообщение.
     *
     * @Route("/payment/fail", name="payment_fail")
     * @Template()
     *
     * @return array
     */
    public function failAction()
    {
        return array();
    }

    /**
     * Оплата не завершена. Ожидаем ответ шлюза
     *
     * @param Request $request
     *
     * @Route("/payment/pending", name="payment_pending")
     * @Template()
     *
     * @return array
     */
    public function pendingAction(Request $request)
    {
        /** @var Payment $payment */
        $payment = $this->getDoctrine()
            ->getRepository('StfalconPaymentBundle:Payment')
            ->findOneBy(array('id' => $request->get('ik_pm_no')));

        if (!$payment) {
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $event = $em->getRepository('StfalconEventBundle:Event')->find(6);
            $paymentRepository = $em->getRepository('StfalconPaymentBundle:Payment');
            $payment = $paymentRepository->findPaymentByUserAndEvent($user, $event);
            if (!$payment) {
                return $this->forward('StfalconEventBundle:Payment:fail');
            }
        }

        if ($payment->isPaid()) {
            return $this->forward('StfalconEventBundle:Payment:success');
        }

        return array();
    }

}