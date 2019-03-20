<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Stfalcon\Bundle\EventBundle\Admin\AbstractClass\AbstractNewsAdmin;

/**
 * Class EventNewsAdmin.
 */
class EventNewsAdmin extends AbstractNewsAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper = parent::configureListFields($listMapper);
        $listMapper
            ->add('event')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper = parent::configureFormFields($formMapper);
        $formMapper
            ->with('General')
                ->add('event', 'entity', array(
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                ))
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event');
    }
}
