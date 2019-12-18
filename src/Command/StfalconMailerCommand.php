<?php

namespace App\Command;

use App\Entity\Mail;
use App\Entity\MailQueue;
use App\Helper\StfalconMailerHelper;
use App\Service\EmailHashValidationService;
use App\Service\MyMailer;
use App\Service\TranslatedMailService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class StfalconMailerCommand.
 */
class StfalconMailerCommand extends ContainerAwareCommand
{
    /** @var EmailHashValidationService */
    private $emailHashValidationService;

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
            $context->setHost((string) $input->getOption('host'));
        }

        $container = $this->getContainer();

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $container->get('doctrine.orm.entity_manager');
        $mailer = $container->get(MyMailer::class);
        $mailerHelper = $container->get(StfalconMailerHelper::class);
        $queueRepository = $em->getRepository(MailQueue::class);

        $mailsQueue = $queueRepository->getMessages($limit);
        $logger = $container->get('logger');
        $this->emailHashValidationService = $container->get(EmailHashValidationService::class);

        /* @var $mail Mail */
        foreach ($mailsQueue as $item) {
            $user = $item->getUser();
            $mail = $item->getMail();

            if (!(
                $user &&
                $mail &&
                $user->isEnabled() &&
                ($user->isSubscribe() || $mail->isIgnoreUnsubscribe()) &&
                $user->isEmailExists() &&
                filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)
            )) {
                $mail->decTotalMessages();
                $em->remove($item);
                $em->flush();
                continue;
            }

            try {
                $translatedMailService = $container->get(TranslatedMailService::class);
                $translatedMails = $translatedMailService->getTranslatedMailArray($mail);

                $message = $mailerHelper->formatMessage($user, $translatedMails[$user->getEmailLanguage()]);
            } catch (\Exception $e) {
                $logger->addError('Mailer:'.$e->getMessage(), ['email' => $user->getEmail()]);

                $mail->decTotalMessages();
                $em->remove($item);
                $em->flush();
                continue;
            }
            $mailId = $mail->getId();
            $hash = $this->emailHashValidationService->generateHash($user, $mailId);

            $headers = $message->getHeaders();
            $http = $container->get('router')->generate(
                'unsubscribe',
                [
                    'hash' => $hash,
                    'id' => $user->getId(),
                    'mailId' => $mailId,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
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
