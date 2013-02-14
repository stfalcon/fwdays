<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class TicketAdmin extends Admin
{

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('event')
            ->add('user.fullname', null, array('label' => 'Fullname'))
            ->add('payment')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('used')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event')
            ->add('used')
            ->add('payment.status', 'doctrine_orm_choice',
                array(
                    'field_options' => array(
                        'choices' => array(
                            'paid'    => 'Paid',
                            'pending' => 'Pending'
                        ),
                    ),
                    'field_type' => 'choice'
                )
            );
    }

    public function getExportFields()
    {
        return array(
            'id',
            'event',
            'user.fullname',
            'payment',
            'createdAt',
            'updatedAt',
            'used'
        );
    }
}
