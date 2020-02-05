<?php

namespace App\Command;

use App\Entity\Mail;
use App\Entity\User;
use App\Helper\MailerHelper;
use App\Repository\MailQueueRepository;
use App\Service\EmailHashValidationService;
use App\Service\MyMailer;
use App\Service\TranslatedMailService;
use App\Traits\EntityManagerTrait;
use App\Traits\LoggerTrait;
use App\Traits\RouterTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * StfalconMailerCommand.
 */
class StfalconMailerCommand extends Command
{
    use EntityManagerTrait;
    use RouterTrait;
    use LoggerTrait;

    private $mailer;
    private $mailerHelper;
    private $queueRepository;
    private $emailHashValidationService;
    private $translatedMailService;

    /**
     * @param MyMailer                   $mailer
     * @param MailerHelper               $mailerHelper
     * @param MailQueueRepository        $queueRepository
     * @param EmailHashValidationService $emailHashValidationService
     * @param TranslatedMailService      $translatedMailService
     */
    public function __construct(MyMailer $mailer, MailerHelper $mailerHelper, MailQueueRepository $queueRepository, EmailHashValidationService $emailHashValidationService, TranslatedMailService $translatedMailService)
    {
        parent::__construct();

        $this->mailer = $mailer;
        $this->mailerHelper = $mailerHelper;
        $this->queueRepository = $queueRepository;
        $this->emailHashValidationService = $emailHashValidationService;
        $this->translatedMailService = $translatedMailService;
    }

    /**
     * Set options.
     */
    protected function configure(): void
    {
        $this
            ->setName('stfalcon:mailer')
            ->setDescription('Send message from queue')
            ->addOption('amount', null, InputOption::VALUE_OPTIONAL, 'Amount of mails which will send per operation. Default 10.')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Site host. Default fwdays.com.');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \Symfony\Component\Routing\RequestContext $context */
        $context = $this->router->getContext();
        $context->setHost('fwdays.com');
        $context->setScheme('http');

        $limit = 10;

        if (\is_string($input->getOption('amount'))) {
            $limit = (int) $input->getOption('amount');
        }

        if (\is_string($input->getOption('host'))) {
            $context->setHost((string) $input->getOption('host'));
        }

        $mailsQueue = $this->queueRepository->getMessages($limit);

        foreach ($mailsQueue as $item) {
            $user = $item->getUser();
            $mail = $item->getMail();

            if (!(
                $user instanceof User &&
                $mail instanceof Mail &&
                $user->isEnabled() &&
                ($user->isSubscribe() || $mail->isIgnoreUnsubscribe()) &&
                $user->isEmailExists() &&
                \filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)
            )) {
                if ($user instanceof User) {
                    $mail->processDecrementUserLocal($user->getEmailLanguage());
                }
                $mail->decTotalMessages();
                $this->em->remove($item);
                $this->em->flush();
                continue;
            }

            try {
                $translatedMails = $this->translatedMailService->getTranslatedMailArray($mail);

                $message = $this->mailerHelper->formatMessage($user, $translatedMails[$user->getEmailLanguage()]);
            } catch (\Exception $e) {
                $this->logger->addError('Mailer:'.$e->getMessage(), ['email' => $user->getEmail()]);

                $mail->decTotalMessages();
                if ($user instanceof User) {
                    $mail->processDecrementUserLocal($user->getEmailLanguage());
                }
                $this->em->remove($item);
                $this->em->flush();
                continue;
            }
            $mailId = $mail->getId();
            $hash = $this->emailHashValidationService->generateHash($user, $mailId);

            $headers = $message->getHeaders();
            $unsubscribeUrl = $this->router->generate(
                'unsubscribe',
                [
                    'hash' => $hash,
                    'id' => $user->getId(),
                    'mailId' => $mailId,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $headers->removeAll('List-Unsubscribe');
            $headers->addTextHeader('List-Unsubscribe', \sprintf('<%s>', $unsubscribeUrl));
            $failed = [];
            if ($this->mailer->send($message, $failed)) {
                $mail->incSentMessage();
                $item->setIsSent(true);
                $this->em->flush();
            } else {
                $this->logger->addError('Mailer send exception', [
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
        $this->em->flush();

        return 0;
    }
}
