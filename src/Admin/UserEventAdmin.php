<?php

namespace App\Admin;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * UserEventAdmin.
 */
final class UserEventAdmin extends AbstractAdmin
{
    private $eventRepository;

    /**
     * @param string          $code
     * @param string          $class
     * @param string          $baseControllerName
     * @param EventRepository $eventRepository
     */
    public function __construct($code, $class, $baseControllerName, EventRepository $eventRepository)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->eventRepository = $eventRepository;
    }

    /**
     * Allows you to customize batch actions.
     *
     * @param array $actions List of actions
     *
     * @return array
     */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('user', null, ['label' => 'Почта'])
            ->add('user.fullname', null, ['label' => 'Имя'])
            ->add('user.phone', null, ['label' => 'Номер телефона'])
            ->add(
                'event',
                null,
                ['label' => 'Событие'],
                EntityType::class,
                ['choices' => $this->getEvents()]
            )
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('user', null, ['label' => 'Почта'])
            ->add('user.fullname', null, ['label' => 'Имя'])
            ->add('user.phone', null, ['label' => 'Номер телефона'])
            ->add('event')
        ;
    }

    /**
     * @return array
     */
    private function getEvents(): array
    {
//        $eventRepository = $this->getConfigurationPool()->getContainer()->get('doctrine')->getRepository(Event::class);

        return $this->eventRepository->findBy([], ['id' => Criteria::DESC]);
    }
}
