<?php

namespace Stfalcon\Bundle\PaymentBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Profiler\Profile;

/**
 * Test cases for interkassa controller
 */
class InterkassaControllerTest extends WebTestCase
{
    /**
     * Test statusAction method
     */
    public function testStatusAction()
    {
        $status = $this->getMock('\Stfalcon\PaymentBundle\Service\IntercassaService');
        $status->expects($this->once())
            ->method('checkPaymentStatus')
            ->will($this->returnValue(true));
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('POST', '/payments/interkassa/status', array('ik_payment_id' => '2'));


        if ($profiler = $client->getProfile()) {
            $this->checkEmailSent($profiler, 'PHP Frameworks Day', 'user@fwdays.com');
        }

        $this->assertTrue($this->checkEmailSent($profiler, 'PHP Frameworks Day', 'user@fwdays.com'));
    }

    /**
     * @param Profile     $profiler Symfony profiler
     * @param string      $subject  Subject of the email
     * @param string|null $to       Email of the user
     *
     * @throws \RuntimeException
     * @return string|boolean
     */
    protected function checkEmailSent($profiler, $subject, $to = null)
    {
        $mailer = $profiler->getCollector('swiftmailer');
        if (0 === $mailer->getMessageCount()) {
            throw new \RuntimeException('No emails have been sent.');
        }

        $foundToAddresses = null;
        $foundSubjects = array();
        foreach ($mailer->getMessages() as $message) {
            $foundSubjects[] = $message->getSubject();

            if (trim($subject) === trim($message->getSubject())) {
                $foundToAddresses = implode(', ', array_keys($message->getTo()));

                if (null !== $to) {
                    $toAddresses = $message->getTo();
                    if (array_key_exists($to, $toAddresses)) {
                        // found, and to address matches
                        return true;
                    }
                    // check next message
                    continue;
                } else {
                    // found, and to email isn't checked
                    return true;
                }
                // found
                return true;
            }
        }

        if (!$foundToAddresses) {
            if (!empty($foundSubjects)) {
                throw new \RuntimeException(sprintf('Subject "%s" was not found, but only these subjects: "%s"', $subject, implode('", "', $foundSubjects)));
            }
            // not found
            throw new \RuntimeException(sprintf('No message with subject "%s" found.', $subject));
        }
        throw new \RuntimeException(sprintf('Subject found, but "%s" is not among to-addresses: %s', $to, $foundToAddresses));
    }
}
