<?php

namespace App\Command;

use App\Repository\TicketCostRepository;
use App\Traits\EntityManagerTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * AppDisablePriceWithDateCommand.
 */
class AppDisablePriceWithDateCommand extends AbstractBaseCommand
{
    use EntityManagerTrait;

    protected static $defaultName = 'app:disable_price_with_less_end_date';

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

        $this->setDescription('Disable ticket price with end date less than current');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Set ticket prices to disable ...');
        $ticketCosts = $this->ticketCostRepository->getEnabledTicketCostWithEndDateLessThanDate($this->currentDateTime);

        $count = \count($ticketCosts);
        if ($count > 0) {
            foreach ($ticketCosts as $ticketCost) {
                $ticketCost->setEnabled(false);
            }
            $io->writeln(\sprintf('Processed: %s', \count($ticketCosts)));
            $this->em->flush();
        }

        $io->success('DONE');

        return 0;
    }
}
