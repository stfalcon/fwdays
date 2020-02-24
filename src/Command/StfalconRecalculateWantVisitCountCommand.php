<?php

namespace App\Command;

use App\Repository\EventRepository;
use App\Repository\UserEventRegistrationRepository;
use App\Traits\EntityManagerTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * StfalconRecalculateWantVisitCountCommand.
 */
class StfalconRecalculateWantVisitCountCommand extends Command
{
    use EntityManagerTrait;

    private $eventRepository;
    private $userEventRegistrationRepository;

    /**
     * @param EventRepository                 $eventRepository
     * @param UserEventRegistrationRepository $userEventRegistrationRepository
     */
    public function __construct(EventRepository $eventRepository, UserEventRegistrationRepository $userEventRegistrationRepository)
    {
        parent::__construct();

        $this->eventRepository = $eventRepository;
        $this->userEventRegistrationRepository = $userEventRegistrationRepository;
    }

    /**
     * Set options.
     */
    protected function configure(): void
    {
        $this
            ->setName('stfalcon:recalculate-wanna-visit')
            ->setDescription('Recalculate wanna visit to events count.');
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->eventRepository->findAll();
        foreach ($events as $event) {
            $count = $this->userEventRegistrationRepository->getRegistrationCountByEvent($event);
            $event->setWantsToVisitCount($count);
        }

        $this->em->flush();

        return 0;
    }
}
