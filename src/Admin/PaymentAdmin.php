<?php

namespace App\Admin;

use App\Entity\Event;
use App\Entity\Payment;
use App\Service\User\UserService;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
     * @return array
     */
    public function getBatchActions(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFormTheme(): array
    {
        return array_merge(
            parent::getFormTheme(),
            ['Admin/admin.light_theme.html.twig']
        );
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
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
                    'template' => 'Admin/list_tickets.html.twig',
                ]
            )
            ->add('gate', null, ['label' => 'Способ оплаты'])
            ->add('createdAt', null, ['label' => 'Дата создания'])
            ->add('updatedAt', null, ['label' => 'Дата изменения']);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add(
                'gate',
                'doctrine_orm_choice',
                ['label' => 'Способ оплаты'],
                ChoiceType::class,
                [
                    'choices' => Payment::getPaymentTypeChoice(),
                    'required' => false,
                ]
            )
            ->add(
                'status',
                'doctrine_orm_choice',
                ['label' => 'Статус оплаты'],
                ChoiceType::class,
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
                    'field_type' => EntityType::class,
                    'field_options' => [
                        'class' => Event::class,
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
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $container = $this->getConfigurationPool()->getContainer();
        $userService = $container->get(UserService::class);
        $user = $userService->getCurrentUser();
        $environment = $container->getParameter('kernel.environment');
        $isSuperAdmin = \in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true) || 'dev' === $environment;

        $subject = $this->getSubject();

        $formMapper
            ->with('Общие')
                ->add('amount', MoneyType::class, [
                    'currency' => 'UAH',
                    'label' => 'Сума оплаты',
                    'disabled' => $subject->isPaid(),
                ])
                ->add('fwdaysAmount', MoneyType::class, [
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
                ->add('user', TextType::class, ['required' => true, 'label' => 'Пользователь', 'disabled' => true])
                ->add(
                    'tickets',
                    CollectionType::class,
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
