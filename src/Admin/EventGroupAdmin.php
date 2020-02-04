<?php

namespace App\Admin;

use App\Entity\Event;
use App\Entity\EventGroup;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class EventGroupAdmin.
 */
final class EventGroupAdmin extends AbstractAdmin
{
    /** @var ArrayCollection|null */
    private $prevEvents = null;

    /** @var EventRepository */
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
     * @param EventGroup $object
     */
    public function preUpdate($object): void
    {
        /** @var Event $event */
        foreach ($this->prevEvents as $event) {
            /** @var EventGroup $object */
            if (!$object->getEvents()->contains($event)) {
                $event->setGroup(null);
            }
        }
        /** @var Event $event */
        foreach ($object->getEvents() as $event) {
            $event->setGroup($object);
        }
    }

    /**
     * @param EventGroup $object
     */
    public function preRemove($object): void
    {
        foreach ($object->getEvents() as $event) {
            $object->removeEvent($event);
        }
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
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        /** @var EventGroup $group */
        $group = $this->getSubject();
        if ($group->getId()) {
            $this->prevEvents = clone $group->getEvents();
        }

        $formMapper
            ->add('name', null, ['label' => 'Название'])
            ->add(
                'events',
                null,
                [
                    'disabled' => null === $group->getId(),
                    'help' => null === $group->getId() ? 'добавление событий возможно только после создания группы' : 'добавьте событие в группу',
                    'label' => 'События',
                ]
            );
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name')
            ->add(
                'events',
                null,
                [],
                EntityType::class,
                ['choices' => $this->getEvents()]
            );
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('name')
            ->add('events');
    }

    /**
     * @return array
     */
    private function getEvents(): array
    {
        return $this->eventRepository->findBy([], ['id' => Criteria::DESC]);
    }
}
