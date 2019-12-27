<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * WayForPayLogAdmin.
 */
final class WayForPayLogAdmin extends AbstractAdmin
{
    /**
     * Default Datagrid values.
     *
     * @var array
     */
    protected $datagridValues =
        [
            '_page' => 1,
            '_sort_order' => 'DESC',
            '_sort_by' => 'date',
        ];

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection): void
    {
        // remove all route except named ones
        $collection->clearExcept(['list', 'show']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $filter): void
    {
        $filter->add('id')
            ->add('date', null, ['label' => 'Дата'])
            ->add('payment', null, ['label' => 'Платіж'])
            ->add('status', null, ['label' => 'Статус'])
            ->add('responseAsArray', 'array', ['label' => 'Дані відповіді сервера'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('id')
            ->add('date', null, ['label' => 'Дата'])
            ->add('payment.id', null, ['label' => 'Платіж'])
            ->add('status', null, ['label' => 'Статус'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('id')
            ->add('date', null, ['label' => 'Дата'])
            ->add('payment', null, ['label' => 'Платіж'])
            ->add('status', null, ['label' => 'Статус'])
            ->add('fwdaysResponse', null, ['label' => 'Відповіть fwdays'])
        ;
    }
}
