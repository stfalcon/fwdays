<?php

namespace Stfalcon\Bundle\EventBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Stfalcon\Bundle\EventBundle\Entity\Mail,
    Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper;

/**
 * Class StfalconMailerCommand
 */
class StfalconMailerCommand extends ContainerAwareCommand
{
    /**
     * Set options
     */
    protected function configure()
    {
        $this
            ->setName('stfalcon:mailer')
            ->setDescription('Send message from queue')
            ->addOption('amount', null, InputOption::VALUE_OPTIONAL, 'Amount of mails which will send per operation. Default 10.');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Routing\RequestContext $context */
        $context = $this->getContainer()->get('router')->getContext();
        $context->setHost('frameworksdays.com');
        $context->setScheme('http');

        $limit = 10;

        if ($input->getOption('amount')) {
            $limit = (int) $input->getOption('amount');
        }

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var $mailer \Swift_Mailer */
        $mailer = $this->getContainer()->get('mailer');
        /** @var $mailerHelper \Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper */
        $mailerHelper = $this->getContainer()->get('stfalcon_event.mailer_helper');
        /** @var $queueRepository \Stfalcon\Bundle\EventBundle\Repository\MailQueueRepository */
        $queueRepository = $em->getRepository('StfalconEventBundle:MailQueue');

        $mailsQueue = $queueRepository->getMessages($limit);

        /** @var $mail Mail */
        foreach ($mailsQueue as $item) {
            $user = $item->getUser();
            $mail = $item->getMail();

            if (!($user && $mail)) {
                $em->remove($item);
                $em->flush();
                continue;
            }

            if ($mailer->send($mailerHelper->formatMessage($user, $mail))) {
                $mail->setSentMessages($mail->getSentMessages() + 1);
                $item->setIsSent(true);

                if ($mail->getSentMessages() == $mail->getTotalMessages()) {
                    $mail->setStart(false);
                }

                $em->persist($mail);
                $em->persist($item);
                $em->flush();
            }
        }
    }
}
