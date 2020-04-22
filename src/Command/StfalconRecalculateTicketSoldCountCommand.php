<?php

namespace App\Command;

use App\Repository\TicketCostRepository;
use App\Traits\EntityManagerTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * StfalconRecalculateTicketSoldCountCommand.
 */
class StfalconRecalculateTicketSoldCountCommand extends Command
{
    use EntityManagerTrait;

    private $ticketCostRepository;

    /**
     * @param TicketCostRepository $ticketCostRepository
     */
    public function __construct(TicketCostRepository $ticketCostRepository)
    {
        parent::__construct();

        $this->ticketCostRepository = $ticketCostRepository;
    }

    /**
     * Set options.
     */
    protected function configure(): void
    {
        $this
            ->setName('stfalcon:recalculate-ticket-sold')
            ->setDescription('Recalculate tickets sold count in blocks');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ticketsCost = $this->ticketCostRepository->findAll();
        foreach ($ticketsCost as $ticketCost) {
            $saveCount = $ticketCost->getSoldCount();
            $newCount = $ticketCost->recalculateSoldCount();
            if ($saveCount !== $newCount) {
                $output->writeln($ticketCost->__toString().' old:'.$saveCount.' new:'.$newCount);
            }
        }

        $this->em->flush();

        return 0;
    }
}
