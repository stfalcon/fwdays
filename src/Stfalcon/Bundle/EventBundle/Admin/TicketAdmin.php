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
    protected $datagridValues =
        [
            '_page' => 1,
            '_sort_order' => 'DESC',
            '_sort_by' => 'id',
        ];

    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'id',
            'event',
            'user.fullname',
            'amount',
            'amountWithoutDiscount',
            'payment',
            'createdAt',
            'updatedAt',
            'used',
        ];
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->add('remove_paid_ticket_from_payment', $this->getRouterIdParameter().'/remove_paid_ticket_from_payment');
    }

    /**
     * @param ListMapper $listMapper
     */
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
                'label' => 'Действие',
                'actions' => [
                    'removeTicket' => [
                        'ask_confirmation' => true,
                        'template' => 'StfalconEventBundle:Admin:list_action_remove_ticket.html.twig',
                    ],
                ],
            ])
        ;
    }

    /**
     * @param ShowMapper $filter
     */
    protected function configureShowFields(ShowMapper $filter)
    {
        $filter->add('id')
            ->add('event', null, ['label' => 'Событие'])
            ->add('user.fullname', null, ['label' => 'Имя пользователя'])
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

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event', null, ['label' => 'Событие'])
            ->add('user', null, ['label' => 'Пользователь'])
            ->add('user.email', null, ['label' => 'Почта'])
            ->add('used', null, ['label' => 'Использован'])
            ->add(
                'payment.status',
                'doctrine_orm_choice',
                [
                    'label' => 'Статус оплаты',
                    'field_options' => [
                        'choices' => [
                            'paid' => 'оплачено',
                            'pending' => 'ожидание',
                            'returned' => 'возращен',
                        ],
                    ],
                    'field_type' => 'choice',
                ]
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('event', null, ['required' => true, 'label' => 'Событие'])
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
