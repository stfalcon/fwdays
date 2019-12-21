<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Service\PaymentProcess\AbstractPaymentProcessService;
use App\Service\PaymentProcess\PaymentProcessInterface;
use App\Traits\SessionTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PaymentProcessController.
 */
class PaymentProcessController extends AbstractController
{
    use SessionTrait;

    /** @var array */
    private $itemVariants = ['javascript', 'php', 'frontend', 'highload', 'net.'];
    private $paymentSystem;

    /**
     * @param PaymentProcessInterface $paymentSystem
     */
    public function __construct(PaymentProcessInterface $paymentSystem)
    {
        $this->paymentSystem = $paymentSystem;
    }

    /**
     * @Route("/payment/interaction", name="payment_interaction", methods={"POST"})
     *
     * @param Request $request
     *
     * @return array|Response
     */
    public function interactionAction(Request $request)
    {
        $data = $request->request->all();

        try {
            $transactionStatus = $this->paymentSystem->processData($data);
        } catch (BadRequestHttpException $e) {
            return $this->redirectToRoute('homepage');
        }

        if ($this->paymentSystem->isUseRedirectByStatus()) {
            if (AbstractPaymentProcessService::TRANSACTION_APPROVED_AND_SET_PAID_STATUS === $transactionStatus) {
                return $this->redirectToRoute('payment_success');
            }
            if (AbstractPaymentProcessService::TRANSACTION_STATUS_PENDING === $transactionStatus) {
                return $this->redirectToRoute('payment_pending');
            }
            if (AbstractPaymentProcessService::TRANSACTION_STATUS_FAIL === $transactionStatus) {
                return $this->redirectToRoute('payment_fail');
            }
        } elseif (AbstractPaymentProcessService::TRANSACTION_APPROVED_AND_SET_PAID_STATUS === $transactionStatus) {
            return new Response('SUCCESS', 200);
        }

        return new Response('FAIL transaction status:'.$transactionStatus, 400);
    }

    /**
     * @Route("/payment/service-interaction", name="payment_service_interaction",
     *     methods={"POST"},
     *     options={"expose"=true})
     *
     * @param Request $request
     *
     * @return array|Response
     */
    public function serviceInteractionAction(Request $request)
    {
        $json = $request->getContent();
        $response = \json_decode($json, true);

        try {
            $this->paymentSystem->processData($response);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
        $result = $this->paymentSystem->getResponseOnServiceUrl($response);

        return new JsonResponse($result);
    }

    /**
     * @Route("/payment/success", name="payment_success")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function showSuccessAction(Request $request)
    {
        $paymentId = $this->session->get(AbstractPaymentProcessService::SESSION_PAYMENT_KEY);
        $this->session->remove(AbstractPaymentProcessService::SESSION_PAYMENT_KEY);

        if (null === $paymentId) {
            $data = $request->query->all();
            $paymentId = $this->paymentSystem->getPaymentIdFromData($data);
            if (null === $paymentId) {
                throw new BadRequestHttpException();
            }
        }

        /** @var Payment|null $payment */
        $payment = $this->getDoctrine()->getRepository(Payment::class)->find($paymentId);

        $eventName = '';
        $eventType = '';
        if ($payment) {
            $tickets = $payment->getTickets();
            $eventName = \count($tickets) > 0 ? $tickets[0]->getEvent()->getName() : '';
            $eventType = $this->getItemVariant($eventName);
        }

        return $this->render('PaymentResult/success.html.twig', [
            'payment' => $payment,
            'event_name' => $eventName,
            'event_type' => $eventType,
        ]);
    }

    /**
     * @Route("/payment/fail", name="payment_fail")
     *
     * @return Response
     */
    public function failAction(): Response
    {
        return $this->render('PaymentResult/fail.html.twig');
    }

    /**
     * @Route("/payment/pending", name="payment_pending")
     *
     * @return Response
     */
    public function pendingAction(): Response
    {
        return $this->render('PaymentResult/pending.html.twig');
    }

    /**
     * @param string $eventName
     *
     * @return string
     */
    private function getItemVariant($eventName)
    {
        foreach ($this->itemVariants as $itemVariant) {
            $pattern = '/'.$itemVariant.'/';
            if (preg_match($pattern, strtolower($eventName))) {
                return $itemVariant;
            }
        }

        return $eventName;
    }
}
