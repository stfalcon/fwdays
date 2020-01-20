<?php

namespace App\Service;

use Swift_Transport;

/**
 * Class MyMailer.
 */
class MyMailer extends \Swift_Mailer
{
    /**
     * MyMailer constructor.
     *
     * @param Swift_Transport $transport
     */
    public function __construct(Swift_Transport $transport)
    {
        parent::__construct($transport);
    }

    /**
     * @param \Swift_Mime_Message $message
     * @param array               $failedRecipients An array of failures by-reference
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $failedRecipients = (array) $failedRecipients;

        if (!$this->getTransport()->isStarted()) {
            $this->getTransport()->start();
        }

        $sent = 0;

        try {
            $sent = $this->getTransport()->send($message, $failedRecipients);
        } catch (\Swift_RfcComplianceException $e) {
            foreach ($message->getTo() as $address => $name) {
                $failedRecipients[] = $address;
            }
            $failedRecipients['error_swift_message'] = $e->getMessage();
            $failedRecipients['error_swift_code'] = $e->getCode();
            $failedRecipients['error_swift_trace'] = $e->getTraceAsString();
        } catch (\Exception $e) {
            $failedRecipients['error_exception_message'] = $e->getMessage();
            $failedRecipients['error_exception_code'] = $e->getCode();
            $failedRecipients['error_exception_trace'] = $e->getTraceAsString();
        }

        return $sent;
    }
}
