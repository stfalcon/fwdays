<?php

declare(strict_types=1);

namespace App\Service\PaymentProcess;

use App\Entity\Event;
use App\Entity\Payment;

/**
 * PaymentProcessInterface.
 */
interface PaymentProcessInterface
{
    /**
     * @param array $data
     *
     * @return array|null
     */
    public function getResponseOnServiceUrl(array $data): ?array;

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
     * @param array|null $data
     *
     * @return string
     */
    public function processData(?array $data): string;

    /**
     * @return bool
     */
    public function isUseRedirectByStatus(): bool;

    /**
     * @return bool
     */
    public function isAgreeWithConditionsRequired(): bool;

    /**
     * @param array $data
     *
     * @return string|null
     */
    public function getPaymentIdFromData(array $data): ?string;
}
