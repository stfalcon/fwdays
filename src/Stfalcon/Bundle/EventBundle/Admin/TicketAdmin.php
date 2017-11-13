<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class TicketAdmin extends Admin
{
    /**
     * Default Datagrid values
     *
     * @var array
     */
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt'
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->add('remove_paid_ticket_from_payment', $this->getRouterIdParameter().'/remove_paid_ticket_from_payment');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('event')
            ->add(
                'user',
                'string',
                [
                    'template' => 'StfalconEventBundle:Admin:user_link_field.html.twig'
                ]
            )
            ->add(
                'amount',
                'money',
                [
                    'currency' => 'UAH'
                ]
            )
            ->add(
                'amountWithoutDiscount',
                'money',
                [
                    'currency' => 'UAH'
                ]
            )
            ->add('promoCode')
            ->add('payment')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('used')
            ->add('_action', null, [
                'actions' => [
                    'removeTicket' => [
                        'ask_confirmation' => true,
                        'template' => 'StfalconEventBundle:Admin:list_action_remove_ticket.html.twig',
                    ],
                ]
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $filter)
    {
        $filter->add('id')
            ->add('event')
            ->add('user.fullname', null, ['label' => 'Fullname'])
            ->add(
                'amount',
                'money',
                [
                    'currency' => 'UAH'
                ]
            )
            ->add(
                'amountWithoutDiscount',
                'money',
                [
                    'currency' => 'UAH'
                ]
            )
            ->add('promoCode')
            ->add('payment')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('used');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event')
            ->add('used')
            ->add(
                'payment.status',
                'doctrine_orm_choice',
                [
                    'field_options' => [
                        'choices' => [
                            'paid' => 'Paid',
                            'pending' => 'Pending'
                        ],
                    ],
                    'field_type' => 'choice'
                ]
            );
    }

    public function getExportFields()
    {
        return array(
            'id',
            'event',
            'user.fullname',
            'amount',
            'amountWithoutDiscount',
            'payment',
            'createdAt',
            'updatedAt',
            'used'
        );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('event')
            ->add(
                'amount',
                'money',
                [
                    'currency' => 'UAH'
                ]
            )
            ->add('payment')
            ->add('used')
        ;
    }
}
