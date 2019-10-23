<?php

declare(strict_types=1);

namespace Application\Bundle\DefaultBundle\Service\PaymentProcess;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Payment;

/**
 * PaymentProcessInterface.
 */
interface PaymentProcessInterface
{
    /**
     * @param array $response
     *
     * @return array|null
     */
    public function getResponseOnServiceUrl(array $response): ?array;

    /**
     * @param Payment $payment
     * @param Event   $event
     *
     * @return array
     */
    public function getData(Payment $payment, Event $event): array;

    /**
     * @return string
     */
    public function getFormAction(): string;

    /**
     * @param array|null $response
     *
     * @return string
     */
    public function processResponse(?array $response): string;

    /**
     * @return bool
     */
    public function isUseRedirectByStatus(): bool;

    /**
     * @return bool
     */
    public function isAgreeWithConditionsRequired(): bool;
}
