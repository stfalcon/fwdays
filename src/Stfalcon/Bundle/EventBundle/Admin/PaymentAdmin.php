<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Application\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Stfalcon\Bundle\EventBundle\Entity\Payment;

/**
 * Class PaymentAdmin.
 */
final class PaymentAdmin extends AbstractAdmin
{
    /**
     * Default Datagrid values.
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
     * @return array
     */
    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            ['@ApplicationDefault/Admin/admin.light_theme.html.twig']
        );
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
            ->add('user', null, ['label' => 'Пользователь'])
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
                        'interkassa' => Payment::INTERKASSA_GATE,
                        'wayforpay' => Payment::WAYFORPAY_GATE,
                        'admin' => Payment::ADMIN_GATE,
                        'bonus' => Payment::BONUS_GATE,
                        'promocode' => Payment::PROMOCODE_GATE,
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'events',
                'doctrine_orm_callback',
                [
                    'label' => 'Событие',
                    'callback' => function($queryBuilder, $alias, $field, $value) {
                        $eventsId = [];
                        /** @var $event \Stfalcon\Bundle\EventBundle\Entity\Event */
                        foreach ($value['value'] as $event) {
                            $eventsId[] = $event->getId();
                        }

                        if (empty($eventsId)) {
                            return;
                        }

                        /* @var $queryBuilder QueryBuilder */
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
        $container = $this->getConfigurationPool()->getContainer();
        $token = $container->get('security.token_storage')->getToken();
        $isSuperAdmin = false;
        if ($token) {
            $user = $token->getUser();
            $isSuperAdmin = $user instanceof User ? in_array('ROLE_SUPER_ADMIN', $user->getRoles()) : false;
        }

        $subject = $this->getSubject();

        $formMapper
            ->with('Общие')
                ->add('amount', 'money', [
                    'currency' => 'UAH',
                    'label' => 'Сума оплаты',
                    'disabled' => $subject->isPaid(),
                ])
                ->add('fwdaysAmount', 'money', [
                    'currency' => 'UAH',
                    'required' => false,
                    'label' => 'Сума реферальных',
                    'disabled' => $subject->isPaid(),
                ])
                ->add('status', 'choice', [
                    'label' => 'статус оплаты',
                    'choices' => [
                        'pending' => 'ожидание',
                        'paid' => 'оплачено',
                        'returned' => 'возвращенно',
                    ],
                    'disabled' => !$isSuperAdmin,
                ])
                ->add('gate', 'choice', [
                    'label' => 'способ оплаты',
                    'choices' => [
                        'interkassa' => Payment::INTERKASSA_GATE,
                        'wayforpay' => Payment::WAYFORPAY_GATE,
                        'admin' => Payment::ADMIN_GATE,
                        'bonus' => Payment::BONUS_GATE,
                        'promocode' => Payment::PROMOCODE_GATE,
                    ],
                    'disabled' => !$isSuperAdmin,
                ])
                ->add('user', 'text', ['required' => true, 'label' => 'Пользователь', 'disabled' => true])
                ->add(
                    'tickets',
                    'sonata_type_collection',
                    [
                        'by_reference' => false,
                        'disabled' => true,
                        'type_options' => [
                            'delete' => false,
                        ],
                    ],
                    [
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'id',
                    ]
                )
            ->end();
    }
}
