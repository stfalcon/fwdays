<?php

namespace App\Admin;

use App\Entity\Payment;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class TicketAdmin.
 */
final class TicketAdmin extends AbstractAdmin
{
    /** @var EventRepository */
    private $eventRepository;

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
    public function getExportFields(): array
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
    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->remove('create');
        $collection->add('remove_paid_ticket_from_payment', $this->getRouterIdParameter().'/remove_paid_ticket_from_payment');
        $collection->add('download', $this->getRouterIdParameter().'/download');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('event', null, ['label' => 'Событие'])
            ->add(
                'user',
                'string',
                [
                    'template' => 'Admin/user_link_field.html.twig',
                    'label' => 'Пользователь',
                ]
            )
            ->add('user.email', 'string', ['label' => 'E-Mail'])
            ->add('user.phone', 'string', ['label' => 'Тел.'])
            ->add(
                'amount',
                MoneyType::class,
                [
                    'currency' => 'UAH',
                    'label' => 'Цена',
                ]
            )
            ->add(
                'amountWithoutDiscount',
                MoneyType::class,
                [
                    'currency' => 'UAH',
                    'label' => 'Цена без скидки',
                ]
            )
            ->add('promoCode', null, ['label' => 'Промокод'])
            ->add('ticketCost.type', null, ['label' => 'Тип'])
            ->add('payment', null, ['label' => 'Оплата'])
            ->add('createdAt', null, ['label' => 'Дата создания'])
            ->add('updatedAt', null, ['label' => 'Дата изменения'])
            ->add('used', null, ['label' => 'Испол.'])
            ->add('_action', null, [
                'label' => 'Действие',
                'actions' => [
                    'removeTicket' => [
                        'ask_confirmation' => true,
                        'template' => 'Admin/list_action_remove_ticket.html.twig',
                    ],
                ],
            ])
        ;
    }

    /**
     * @param ShowMapper $filter
     */
    protected function configureShowFields(ShowMapper $filter): void
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
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('event', null, ['label' => 'Событие'], EntityType::class, ['choices' => $this->getEvents()])
            ->add('user.fullname', null, ['label' => 'Имя пользователя'])
            ->add('user.email', null, ['label' => 'E-Mail пользователя'])
            ->add('user.phone', null, ['label' => 'Номер телефона пользователя'])
            ->add('used', null, ['label' => 'Использован'])
            ->add(
                'payment.status',
                'doctrine_orm_choice',
                [
                    'label' => 'Статус оплаты',
                    'field_options' => [
                        'choices' => Payment::getPaymentStatusChoice(),
                    ],
                    'field_type' => ChoiceType::class,
                ]
            )
            ->add(
                'payment.gate',
                'doctrine_orm_choice',
                [
                    'label' => 'Способ оплаты',
                    'field_options' => [
                        'choices' => Payment::getPaymentTypeChoice(),
                    ],
                    'field_type' => ChoiceType::class,
                ]
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('id', TextType::class, ['required' => false, 'label' => 'id', 'disabled' => true])
            ->add(
                'createdAt',
                DateTimePickerType::class,
                [
                    'required' => false,
                    'label' => 'Создан',
                    'disabled' => true,
                ]
            )
            ->add('event', TextType::class, ['required' => true, 'label' => 'Событие', 'disabled' => true])
            ->add(
                'amount',
                MoneyType::class,
                [
                    'currency' => 'UAH',
                    'label' => 'Цена',
                ]
            )
            ->add('payment', TextType::class, ['label' => 'Оплата', 'disabled' => true])
            ->add('used', null, ['label' => 'Использован', 'disabled' => true])
        ;
    }

    /**
     * @return array
     */
    private function getEvents(): array
    {
        return $this->eventRepository->findBy([], ['id' => Criteria::DESC]);
    }
}
