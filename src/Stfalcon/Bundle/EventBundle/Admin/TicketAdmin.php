<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Stfalcon\Bundle\EventBundle\Entity\Payment;

/**
 * Class TicketAdmin.
 */
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
            'user.email',
            'user.phone',
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
            ->add('user.email', 'string', ['label' => 'E-Mail'])
            ->add('user.phone', 'string', ['label' => 'Тел.'])
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
            ->add('used', null, ['label' => 'Испол.'])
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
            ->add('user.email', 'string', ['label' => 'E-Mail'])
            ->add('user.phone', 'string', ['label' => 'Тел.'])
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
            ->add('id')
            ->add('event', null, ['label' => 'Событие'])
            ->add('user', null, ['label' => 'Пользователь'])
            ->add('user.email', null, ['label' => 'E-Mail'])
            ->add('user.phone', null, ['label' => 'Тел.'])
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
            )
            ->add(
                'payment.gate',
                'doctrine_orm_choice',
                ['label' => 'Способ оплаты'],
                'choice',
                [
                    'choices' => [
                        'interkassa' => Payment::INTERKASSA_GATE,
                        'wayforpay' => Payment::WAYFORPAY_GATE,
                        'admin' => Payment::ADMIN_GATE,
                        'bonus' => Payment::BONUS_GATE,
                        'promocode' => Payment::PROMOCODE_GATE,
                    ],
                    'required' => false,
                ]
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id', 'text', ['required' => false, 'label' => 'id', 'disabled' => true])
            ->add(
                'createdAt',
                'sonata_type_datetime_picker',
                [
                    'required' => false,
                    'label' => 'Создан',
                    'disabled' => true,
                ]
            )
            ->add('event', 'text', ['required' => true, 'label' => 'Событие', 'disabled' => true])
            ->add(
                'amount',
                'money',
                [
                    'currency' => 'UAH',
                    'label' => 'Цена',
                ]
            )
            ->add('payment', 'text', ['label' => 'Оплата', 'disabled' => true])
            ->add('used', null, ['label' => 'Использован', 'disabled' => true])
        ;
    }
}
