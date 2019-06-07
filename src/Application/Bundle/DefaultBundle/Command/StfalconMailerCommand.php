<?php

namespace Application\Bundle\DefaultBundle\Command;

use Application\Bundle\DefaultBundle\Service\MyMailer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Application\Bundle\DefaultBundle\Entity\Mail;

/**
 * Class StfalconMailerCommand.
 */
class StfalconMailerCommand extends ContainerAwareCommand
{
    /**
     * Set options.
     */
    protected function configure()
    {
        $this
            ->setName('stfalcon:mailer')
            ->setDescription('Send message from queue')
            ->addOption('amount', null, InputOption::VALUE_OPTIONAL, 'Amount of mails which will send per operation. Default 10.')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Site host. Default fwdays.com.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Routing\RequestContext $context */
        $context = $this->getContainer()->get('router')->getContext();
        $context->setHost('fwdays.com');
        $context->setScheme('http');

        $limit = 10;

        if ($input->getOption('amount')) {
            $limit = (int) $input->getOption('amount');
        }

        if ($input->getOption('host')) {
            $context->setHost($input->getOption('host'));
        }

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var $mailer MyMailer */
        $mailer = $this->getContainer()->get('app.my_mailer.service');
        /** @var $mailerHelper \Application\Bundle\DefaultBundle\Helper\StfalconMailerHelper */
        $mailerHelper = $this->getContainer()->get('application.mailer_helper');
        /** @var $queueRepository \Application\Bundle\DefaultBundle\Repository\MailQueueRepository */
        $queueRepository = $em->getRepository('ApplicationDefaultBundle:MailQueue');

        $mailsQueue = $queueRepository->getMessages($limit);
        $logger = $this->getContainer()->get('logger');
        /* @var $mail Mail */
        foreach ($mailsQueue as $item) {
            $user = $item->getUser();
            $mail = $item->getMail();

            if (!(
                $user &&
                $mail &&
                $user->isEnabled() &&
                $user->isSubscribe() &&
                $user->isEmailExists() &&
                filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)
            )) {
                $mail->decTotalMessages();
                $em->remove($item);
                $em->flush();
                continue;
            }

            try {
                $message = $mailerHelper->formatMessage($user, $mail);
            } catch (\Exception $e) {
                $logger->addError('Mailer:'.$e->getMessage(), ['email' => $user->getEmail()]);

                $mail->decTotalMessages();
                $em->remove($item);
                $em->flush();
                continue;
            }

            $headers = $message->getHeaders();
            $http = $this->getContainer()->get('router')->generate(
                'unsubscribe',
                [
                    'hash' => $user->getSalt(),
                    'userId' => $user->getId(),
                    'mailId' => $mail->getId(),
                ],
                true
            );

            $headers->removeAll('List-Unsubscribe');
            $headers->addTextHeader('List-Unsubscribe', '<'.$http.'>');
            $failed = [];
            if ($mailer->send($message, $failed)) {
                $mail->incSentMessage();
                $item->setIsSent(true);
                $em->flush();
            } else {
                $logger->addError('Mailer send exception', [
                    'mail_id' => $mail->getId(),
                    'user_id' => $user->getId(),
                    'error_swift_message' => isset($failed['error_swift_message']) ? $failed['error_swift_message'] : '',
                    'error_swift_code' => isset($failed['error_swift_code']) ? $failed['error_swift_code'] : '',
                    'error_swift_trace' => isset($failed['error_swift_trace']) ? $failed['error_swift_trace'] : '',
                    'error_exception_message' => isset($failed['error_exception_message']) ? $failed['error_exception_message'] : '',
                    'error_exception_code' => isset($failed['error_exception_code']) ? $failed['error_exception_code'] : '',
                    'error_exception_trace' => isset($failed['error_exception_trace']) ? $failed['error_exception_trace'] : '',
                ]);
            }
        }
        $em->flush();
    }
}
