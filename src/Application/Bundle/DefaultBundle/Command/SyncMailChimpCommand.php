<?php

namespace Application\Bundle\DefaultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

class SyncMailChimpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('mailchimp:sync')
             ->setDescription('MailChimp Synchronization with local env Synchronization')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var $mailChimp \MZ\MailChimpBundle\Services\MailChimp
         */
        $mailChimp = $this->getContainer()->get('MailChimp');
        $list = $mailChimp->getList();
        $list->setDoubleOptin(false);
        $list->setUpdateExisting(true);

        // SYNCHRONIZE EVENTS
        $Events = $this->getContainer()->get('doctrine')->getEntityManager()
            ->getRepository('StfalconEventBundle:Event')
            ->findAll();

        $i = 0;
        foreach ($Events as $Event) {
            $i++;
            $list->listInterestGroupingAdd(
                $Event->getSlug(), 'hidden',
                array(
                    Payment::STATUS_PAID,
                    Payment::STATUS_PENDING)
            );
            $output->write('.');
        }
        $output->writeln('|');
        $log = '<info>' . $i . ' - Events sync </info>';
        $output->writeln($log);

        // SYNCHRONIZE USERS
        $Users = $this->getContainer()->get('doctrine')->getEntityManager()
            ->getRepository('ApplicationUserBundle:User')
            ->findByEnabled(1);

        $i = 0;
        foreach ($Users as $User) {
            $i++;
            $mergeVars = array(
                'FNAME' => $User->getFullname(),
                'SUBSCRIBE' => $User->isSubscribe(),
            );

            $list->setMerge($mergeVars);
            $list->setEmail($User->getEmail());
            $list->Subscribe();
            $output->write('.');
        }
        $output->writeln('|');
        $log = '<info>'. $i . ' - Users sync </info>';
        $output->writeln($log);

        // SYNCHRONIZE TICKETS
        $Tickets = $this->getContainer()->get('doctrine')->getEntityManager()
            ->getRepository('StfalconEventBundle:Ticket')
            ->findAll();
        $i = 0;
        foreach ($Tickets as $Ticket) {
            $i++;
            $mergeVars = array('GROUPINGS' => array(
                array(
                    'name' => $Ticket->getEvent()->getSlug(),
                    'groups' => $Ticket->isPaid() ? Payment::STATUS_PAID : Payment::STATUS_PENDING
                )));
            $list->setMerge($mergeVars);
            $list->MergeVars();
            $list->setEmail($Ticket->getUser()->getEmail());
            $list->updateMember();
            $output->write('.');
        }
        $log = '<info>' . $i . ' - Tickets sync </info>';
        $output->writeln('|');
        $output->writeln($log);
    }
}