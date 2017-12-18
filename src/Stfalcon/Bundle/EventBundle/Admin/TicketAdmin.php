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
     * Default Datagrid values.
     *
     * @var array
     */
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
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
            ->addIdentifier('event', null, ['label' => 'Событие'])
            ->add(
                'user',
                'string',
                [
                    'template' => 'StfalconEventBundle:Admin:user_link_field.html.twig',
                    'label' => 'Пользователь',
                ]
            )
            ->add(
                'amount',
                'money',
                [
                    'currency' => 'UAH',
                    'label' => 'Цена',
                ]
            )
            ->add(
                'amountWithoutDiscount',
                'money',
                [
                    'currency' => 'UAH',
                    'label' => 'Цена без скидки',
                ]
            )
            ->add('promoCode', null, ['label' => 'Промокод'])
            ->add('payment', null, ['label' => 'Оплата'])
            ->add('createdAt', null, ['label' => 'Дата создания'])
            ->add('updatedAt', null, ['label' => 'Дата изменения'])
            ->add('used', null, ['label' => 'Использован'])
            ->add('_action', null, [
                'actions' => [
                    'removeTicket' => [
                        'ask_confirmation' => true,
                        'template' => 'StfalconEventBundle:Admin:list_action_remove_ticket.html.twig',
                    ],
                ],
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
                    'currency' => 'UAH',
                    'label' => 'Цена',
                ]
            )
            ->add(
                'amountWithoutDiscount',
                'money',
                [
                    'currency' => 'UAH',
                    'label' => 'Цена без скидки',
                ]
            )
            ->add('promoCode', null, ['label' => 'Промокод'])
            ->add('payment', null, ['label' => 'Оплата'])
            ->add('createdAt', null, ['label' => 'Дата создания'])
            ->add('updatedAt', null, ['label' => 'Дата изменения'])
            ->add('used', null, ['label' => 'Использован']);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event')
            ->add('user')
            ->add('user.email')
            ->add('used')
            ->add(
                'payment.status',
                'doctrine_orm_choice',
                [
                    'field_options' => [
                        'choices' => [
                            'paid' => 'Paid',
                            'pending' => 'Pending',
                        ],
                    ],
                    'field_type' => 'choice',
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
            'used',
        );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('event', null, ['label' => 'Событие'])
            ->add(
                'amount',
                'money',
                [
                    'currency' => 'UAH',
                    'label' => 'Цена',
                ]
            )
            ->add('payment', null, ['label' => 'Оплата'])
            ->add('used', null, ['label' => 'Использован'])
        ;
    }
}
