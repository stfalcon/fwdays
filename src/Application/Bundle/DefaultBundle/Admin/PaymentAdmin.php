<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
        return [];
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
                    'template' => 'ApplicationDefaultBundle:Admin:list_tickets.html.twig',
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
                    'choices' => Payment::getPaymentTypeChoice(),
                    'required' => false,
                ]
            )
            ->add(
                'status',
                'doctrine_orm_choice',
                ['label' => 'Стутус оплаты'],
                'choice',
                [
                    'choices' => Payment::getPaymentStatusChoice(),
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
                        /** @var $event \Application\Bundle\DefaultBundle\Entity\Event */
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
                        'class' => 'ApplicationDefaultBundle:Event',
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
        $environment = $container->getParameter('kernel.environment');
        $token = $container->get('security.token_storage')->getToken();
        $isSuperAdmin = false;
        if ($token) {
            $user = $token->getUser();
            $isSuperAdmin = $user instanceof User ? (\in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true) || 'dev' === $environment) : false;
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
                ->add('status', ChoiceType::class, [
                    'label' => 'статус оплаты',
                    'choices' => Payment::getPaymentStatusChoice(),
                    'disabled' => !$isSuperAdmin,
                ])
                ->add('gate', ChoiceType::class, [
                    'label' => 'способ оплаты',
                    'choices' => Payment::getPaymentTypeChoice(),
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
