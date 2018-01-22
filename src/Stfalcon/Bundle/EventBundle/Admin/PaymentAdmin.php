<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Knp\Bundle\MenuBundle\MenuItem;
use Stfalcon\Bundle\EventBundle\Entity\Payment;

/**
 * Class PaymentAdmin
 */
class PaymentAdmin extends Admin
{
    /**
     * Default Datagrid values
     *
     * @var array
     */
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
    ];

    /**
     * @return array|void
     */
    public function getBatchActions()
    {
        $actions = [];
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('amount', null, ['label' => 'Сума оплаты'])
            ->add('fwdaysAmount', null, ['label' => 'Сума реферальных'])
            ->add('refundedAmount', null, ['label' => 'Сума возвратов'])
            ->add('status', null, ['label' => 'Статус платежа'])
            ->add('user', null, ['label' => 'Статус платежа'])
            ->add(
                'tickets',
                'string',
                [
                    'label' => 'Билеты',
                    'route' => [
                        'name' => 'show',
                    ],
                    'template' => 'StfalconEventBundle:Admin:list_tickets.html.twig',
                ]
            )
            ->add('gate', null, ['label' => 'Способ оплаты'])
            ->add('createdAt', null, ['label' => 'Дата создания'])
            ->add('updatedAt', null, ['label' => 'Дата изменения']);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add(
                'gate',
                'doctrine_orm_choice',
                ['label' => 'Способ оплаты'],
                'choice',
                [
                    'choices' => [
                        'interkassa' => 'interkassa',
                        'admin' => 'admin',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'events',
                'doctrine_orm_callback',
                [
                    'label' => 'Событие',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        $eventsId = [];
                        /** @var $event \Stfalcon\Bundle\EventBundle\Entity\Event */
                        foreach ($value['value'] as $event) {
                            $eventsId[] = $event->getId();
                        }

                        if (empty($eventsId)) {
                            return;
                        }

                        /** @var $queryBuilder QueryBuilder */
                        $queryBuilder->join(sprintf('%s.tickets', $alias), 't');
                        $queryBuilder->join('t.event', 'e');
                        $queryBuilder->andWhere($queryBuilder->expr()->in('e.id', $eventsId));

                        return true;
                    },
                    'field_type' => 'entity',
                    'field_options' => [
                        'class' => 'StfalconEventBundle:Event',
                        'choice_label' => 'name',
                        'multiple' => true,
                        'required' => false,
                    ],
                ]
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Общие')
                ->add('amount', 'money', ['currency' => 'UAH', 'label' => 'Сума оплаты'])
                ->add('fwdaysAmount', 'money', [
                    'currency' => 'UAH',
                    'required' => false,
                    'label' => 'Сума реферальных',
                ])
                ->add('status', 'choice', [
                    'label' => 'статус оплаты',
                    'choices'   => [
                        'pending'   => 'ожидание',
                        'paid' => 'оплачено',
                        'returned' => 'возвращенно',
                    ],
                ])
                ->add('gate', 'choice', [
                    'label' => 'способ оплаты',
                    'choices' => [
                        'interkassa' => 'interkassa',
                        'admin' => 'admin',
                        'fwdays-amount' => 'fwdays-amount',
                    ],
                ])
                ->add('user', null, ['required' => true, 'label' => 'Пользователь'])
                ->add('tickets', null, ['by_reference' => false, 'label' => 'Билеты'])
            ->end();
    }
}
