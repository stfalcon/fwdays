<?php

namespace App\Admin;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Traits\TokenStorageTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class PaymentAdmin.
 */
final class PaymentAdmin extends AbstractAdmin
{
    use TokenStorageTrait;

    /** @var EventRepository */
    private $eventRepository;

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
     * @param string          $code
     * @param string          $class
     * @param string          $baseControllerName
     * @param EventRepository $eventRepository
     */
    public function __construct($code, $class, $baseControllerName, EventRepository $eventRepository)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->eventRepository = $eventRepository;
    }

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
                        $queryBuilder->join(sprintf('%s.tickets', $alias), 't')
                            ->join('t.event', 'e')
                            ->andWhere($queryBuilder->expr()->in('e.id', $eventsId))
                            ->orderBy('e.id', Criteria::DESC)
                        ;

                        return true;
                    },
                    'field_type' => EntityType::class,
                    'field_options' => [
                        'class' => Event::class,
                        'choice_label' => 'name',
                        'multiple' => true,
                        'required' => false,
                        'choices' => $this->getEvents(),
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
        $user = $this->getCurrentUser();
        $environment = $container->getParameter('kernel.environment');
        $isSuperAdmin = \in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true) || 'dev' === $environment;
        /** @var Payment $subject */
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

    /**
     * @return array
     */
    private function getEvents(): array
    {
        return $this->eventRepository->findBy([], ['id' => Criteria::DESC]);
    }

    /**
     * @return User|null
     */
    private function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            return null;
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return $user;
    }
}
