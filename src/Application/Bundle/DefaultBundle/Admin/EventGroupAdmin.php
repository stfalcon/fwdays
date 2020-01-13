<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\EventGroup;
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
    private $prevEvents = null;

    /**
     * @param EventGroup $object
     *
     * @return mixed|void
     */
    public function preUpdate($object)
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
    public function preRemove($object)
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
    protected function configureBatchActions($actions)
    {
        unset($actions['delete']);

        return $actions;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
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
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('events',
                null,
                [],
                EntityType::class,
                ['choices' => $this->getEvents()]
            );
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
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
        $eventRepository = $this->getConfigurationPool()->getContainer()->get('doctrine')->getRepository(Event::class);

        return $eventRepository->findBy([], ['id' => Criteria::DESC]);
    }
}
