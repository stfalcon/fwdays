<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\Query\ResultSetMapping;
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
        $userService = $this->container->get(UserService::class);

        $i = 0;
        do {
            $user = $users[$i];
            foreach ($user->getWantsToVisitEvents() as $event) {
                $userService->registerUserToEvent($user, $event, 'now', false);
            }
            ++$i;
        } while ($userCount > $i);

        $em->flush();
    }

    public function down(Schema $schema) : void
    {
    }
}
