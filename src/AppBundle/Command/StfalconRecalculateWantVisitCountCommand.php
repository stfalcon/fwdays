<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\UserEventRegistration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * StfalconRecalculateWantVisitCountCommand.
 */
class StfalconRecalculateWantVisitCountCommand extends ContainerAwareCommand
{
    /**
     * Set options.
     */
    protected function configure()
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
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $events = $em->getRepository(Event::class)->findAll();
        $userEventRegistrationRepository = $em->getRepository(UserEventRegistration::class);
        foreach ($events as $event) {
            $count = $userEventRegistrationRepository->getRegistrationCountByEvent($event);
            $event->setWantsToVisitCount($count);
        }

        $em->flush();
    }
}
