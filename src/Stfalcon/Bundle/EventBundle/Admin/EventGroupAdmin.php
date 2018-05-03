<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\EventGroup;

/**
 * Class EventGroupAdmin.
 */
class EventGroupAdmin extends Admin
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
                    'disabled' => is_null($group->getId()),
                    'help' => is_null($group->getId()) ? 'добавление событий возможно только после создания группы' : 'добавьте событие в группу',
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
            ->add('events');
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
}
