<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class TicketCostAdmin extends Admin
{
    protected function configureDatagridFilters(DatagridMapper $datagrid)
    {
        $datagrid
            ->add('name')
            ->add('event')
            ->add('amount')
            ->add('count')
            ->add('soldCount')
            ->add('enabled')
            ->add('unlimited')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->addIdentifier('name')
            ->add('event')
            ->add('amount')
            ->add('count')
            ->add('soldCount')
            ->add('enabled')
            ->add('unlimited');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('event')
            ->add('amount')
            ->add('count')
            ->add('soldCount')
            ->add('enabled')
            ->add('unlimited');
    }
}