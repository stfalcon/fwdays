<?php

namespace App\Command;

use App\Entity\TicketCost;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StfalconRecalculateTicketSoldCount.
 */
class StfalconRecalculateTicketSoldCountCommand extends ContainerAwareCommand
{
    /**
     * Set options.
     */
    protected function configure()
    {
        $this
            ->setName('stfalcon:recalculate-ticket-sold')
            ->setDescription('Recalculate tickets sold count in blocks');
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $ticketsCost = $em->getRepository(TicketCost::class)->findAll();
        foreach ($ticketsCost as $ticketCost) {
            $saveCount = $ticketCost->getSoldCount();
            $newCount = $ticketCost->recalculateSoldCount();
            if ($saveCount !== $newCount) {
                $output->writeln($ticketCost->__toString().' old:'.$saveCount.' new:'.$newCount);
            }
        }

        $em->flush();
    }
}
