<?php

namespace App\Exception;

/**
 * UnprocessedPaymentStatusException.
 */
class UnprocessedPaymentStatusException extends \Exception
{
    /**
     * @param string          $status
     * @param string          $paymentProcessorName
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(string $status, string $paymentProcessorName, $code = 0, \Exception $previous = null)
    {
        $message = \sprintf('Unprocessed response payment status %s from %s', $status, $paymentProcessorName);

        parent::__construct($message, $code, $previous);
    }
}
