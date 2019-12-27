<?php

namespace App\Command;

use App\Entity\TicketCost;
use App\Traits\EntityManagerTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StfalconRecalculateTicketSoldCount.
 */
class StfalconRecalculateTicketSoldCountCommand extends ContainerAwareCommand
{
    use EntityManagerTrait;

    /**
     * Set options.
     */
    protected function configure(): void
    {
        $this
            ->setName('stfalcon:recalculate-ticket-sold')
            ->setDescription('Recalculate tickets sold count in blocks');
    }

    /**
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $ticketsCost = $this->em->getRepository(TicketCost::class)->findAll();
        foreach ($ticketsCost as $ticketCost) {
            $saveCount = $ticketCost->getSoldCount();
            $newCount = $ticketCost->recalculateSoldCount();
            if ($saveCount !== $newCount) {
                $output->writeln($ticketCost->__toString().' old:'.$saveCount.' new:'.$newCount);
            }
        }

        $this->em->flush();
    }
}
