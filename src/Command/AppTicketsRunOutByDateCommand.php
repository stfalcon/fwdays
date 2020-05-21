<?php

namespace App\Command;

use App\Repository\TicketCostRepository;
use App\Traits\EntityManagerTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * AppTicketsRunOutByDateCommand.
 */
class AppTicketsRunOutByDateCommand extends AbstractBaseCommand
{
    use EntityManagerTrait;

    protected static $defaultName = 'app:tickets-run-out';

    /** @var TicketCostRepository */
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
        parent::configure();

        $this->setDescription('Add "run out" label for tickets price by date');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Set ticket prices with run out label ...');
        $this->currentDateTime->modify('+10 days');

        $ticketCosts = $this->ticketCostRepository->getNotRunOutEnabledTicketCostWithEndDateLessThanDate($this->currentDateTime);

        $count = \count($ticketCosts);
        if ($count > 0) {
            foreach ($ticketCosts as $ticketCost) {
                $ticketCost->setTicketsRunOut(true);
            }
            $io->writeln(\sprintf('Processed: %s', \count($ticketCosts)));
            $this->em->flush();
        }

        $io->success('DONE');

        return 0;
    }
}
