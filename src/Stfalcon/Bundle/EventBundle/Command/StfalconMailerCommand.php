<?php

namespace Stfalcon\Bundle\EventBundle\Command;

use Application\Bundle\DefaultBundle\Service\MyMailer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Stfalcon\Bundle\EventBundle\Entity\Mail;

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
     * @return int|null|void
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
        /** @var $mailerHelper \Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper */
        $mailerHelper = $this->getContainer()->get('stfalcon_event.mailer_helper');
        /** @var $queueRepository \Stfalcon\Bundle\EventBundle\Repository\MailQueueRepository */
        $queueRepository = $em->getRepository('StfalconEventBundle:MailQueue');

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
                if ($user && $mail) {
                    $logger->addError('Mailer:gate1', [
                        'mail_id' => $mail->getId(),
                        'user_id' => $user->getId(),
                        'is_Enabled' => $user->isEnabled(),
                        'is_Subscribe' => $user->isSubscribe(),
                        'is_EmailExists' => $user->isEmailExists(),
                        'email-filter' => filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL),
                    ]);
                } else {
                    $logger->addError('Mailer:gate2 - no user or mail');
                }

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
                $logger->addError('Mailer:gate3', [
                    'mail_id' => $mail->getId(),
                    'user_id' => $user->getId(),
                    'error_message' => $failed['error'],
                ]);
            }
        }
        $em->flush();
    }
}
