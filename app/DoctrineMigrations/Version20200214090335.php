<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200214090335 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        /** @var User[] $users */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $users = $em->getRepository(User::class)->findAll();
        $userCount = \count($users);
        if (0 === $userCount) {
            return;
        }

        $events = $em->getRepository(Event::class)->findAll();
        $firstTicketsDate = [];
        $ticketRepository = $em->getRepository(Ticket::class);
        /** @var Event $event */
        foreach ($events as $event) {
            $firstTicketsDate[$event->getId()] = $ticketRepository->getFirstDayOfTicketSales($event);
        }

        $userService = $this->container->get(UserService::class);
        $today = new \DateTime('now');

        $i = 0;
        do {
            $user = $users[$i];
            foreach ($user->getWantsToVisitEvents() as $event) {
                $registrationDate = $event->getDate() < $today ? $event->getDate() : null;

                if (null !== $firstTicketsDate[$event->getId()]) {
                    $registrationDate = $user->getCreatedAt() > $firstTicketsDate[$event->getId()] ? $user->getCreatedAt() : $firstTicketsDate[$event->getId()];

                    /** @var Ticket $ticket */
                    foreach ($user->getTickets() as $ticket) {
                        if ($ticket->getEvent()->isEqualTo($event)) {
                            $registrationDate = $ticket->getCreatedAt();
                            break;
                        }
                    }
                }

                $userService->registerUserToEvent($user, $event, $registrationDate, false);
            }
            ++$i;
        } while ($userCount > $i);

        $em->flush();
    }

    public function down(Schema $schema) : void
    {
    }
}
