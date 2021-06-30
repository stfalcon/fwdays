<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\Event;
use App\Entity\PromoCode;
use App\Entity\TicketCost;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Traits\LocalsRequiredServiceTrait;
use App\Traits\TokenStorageTrait;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class PromoCodeAdmin.
 */
class PromoCodeAdmin extends AbstractTranslateAdmin
{
    use TokenStorageTrait;
    use LocalsRequiredServiceTrait;

    /** @var EventRepository */
    private $eventRepository;

    /** @var array */
    private $activeEvents = [];

    /**
     * @var array
     */
    protected $datagridValues =
        [
            '_page' => 1,
            '_per_page' => 32,
            '_sort_order' => Criteria::DESC,
            '_sort_by' => 'id',
        ];

    /**
     * @param string          $code
     * @param class-string    $class
     * @param string          $baseControllerName
     * @param EventRepository $eventRepository
     */
    public function __construct($code, $class, $baseControllerName, EventRepository $eventRepository)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->eventRepository = $eventRepository;
    }

    /**
     * @param object $object
     */
    public function preRemove($object): void
    {
        $user = $this->getCurrentUser();

        if (!$user instanceof User || !\in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccess($action, $object = null): bool
    {
        $result = parent::hasAccess($action, $object);
        if ('delete' === $action) {
            $user = $this->getCurrentUser();
            if ($user instanceof User && !\in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
                return false;
            }
        }

        return $result;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('title', null, ['label' => 'Название'])
            ->add('discountAmount', null, ['label' => 'Скидка (%)'])
            ->add('code', null, ['label' => 'Код'])
            ->add('event', 'string', ['label' => 'Событие'])
            ->add('usedCount', null, ['label' => 'Использований'])
            ->add('Limit', null, ['label' => 'Лимит'])
            ->add('tickerCostType', null, ['label' => 'Тип билета'])
            ->add('endDate', null, ['label' => 'Дата окончания'])
            ->add('createdBy', 'string', ['label' => 'Создал'])
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $localOptions = $this->localsRequiredService->getLocalsRequiredArray();
        /** @var PromoCode|null $promocode */
        $promocode = $this->getSubject();
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user instanceof User && \in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);
        $creator = null;
        $allowedToEdit = false;
        if ($promocode instanceof PromoCode) {
            $creator = $promocode->getCreatedBy();
            $isCreator = $user instanceof User && $creator instanceof User && $user->isEqualTo($creator);
            $allowedToEdit = ($isCreator && 0 === $promocode->getUsedCount()) || $isSuperAdmin || null === $promocode->getId();
        }

        $datetimePickerOptions =
            [
                'format' => 'dd.MM.y',
            ];
        $formMapper
            ->with('Переводы')
                ->add('translations', GedmoTranslationsType::class, [
                    'label' => 'Переводы',
                    'translatable_class' => $this->getClass(),
                    'disabled' => !$allowedToEdit,
                    'fields' => [
                        'title' => [
                            'label' => 'Название',
                            'locale_options' => $localOptions,
                        ],
                    ],
                ])
            ->end()
            ->with('Общие')
                ->add('discountAmount', null, ['required' => true, 'disabled' => !$allowedToEdit, 'label' => 'Скидка (%)'])
                ->add('code', null, ['disabled' => !$allowedToEdit, 'label' => 'Код'])
                ->add('event', EntityType::class, [
                    'class' => Event::class,
                    'label' => 'Событие',
                    'required' => true,
                    'disabled' => !$allowedToEdit,
                    'placeholder' => 'Выбирите событие',
                    'choices' => $this->getActiveEvents(),
                    'attr' => ['class' => 'event_choice'],
                ])
                ->add(
                    'tickerCostType',
                    ChoiceType::class,
                    [
                        'choices' => TicketCost::getTypesWithOutFree(),
                        'label' => 'Тип билета',
                        'required' => true,
                    ]
                )
                ->add('date_for_promo', ChoiceType::class, [
                    'mapped' => false,
                    'label' => false,
                    'attr' => ['class' => 'date_for_promo hidden'],
                    'choices' => $this->getActiveEventsDates(),
                ])
                ->add('maxUseCount', null, ['disabled' => !$allowedToEdit, 'label' => 'Максимальное количество использований', 'help' => '(0 - безлимитный)'])
                ->add(
                    'endDate',
                    DateTimePickerType::class,
                    array_merge(
                        [
                            'disabled' => !$allowedToEdit,
                            'required' => true,
                            'label' => 'Дата окончания',
                            'attr' => ['class' => 'promo_end_date'],
                        ],
                        $datetimePickerOptions
                    )
                )
                ->add('description', TextType::class, ['disabled' => !$allowedToEdit, 'label' => 'Описание', 'required' => false])
            ->end()
        ;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('title', null, ['label' => 'Название'])
            ->add(
                'event',
                null,
                ['label' => 'Событие'],
                EntityType::class,
                ['choices' => $this->getEvents()]
            )
            ->add('createdBy.email', null, ['label' => 'Создал'])
            ->add(
                'tickerCostType',
                'doctrine_orm_choice',
                ['label' => 'Тип билета'],
                ChoiceType::class,
                [
                    'choices' => TicketCost::getTypesWithOutFree(),
                    'required' => false,
                ]
            )
        ;
    }

    /**
     * @return array
     */
    private function getEvents(): array
    {
        return $this->eventRepository->findBy([], ['id' => Criteria::DESC]);
    }

    /**
     * @return array
     */
    private function getActiveEvents(): array
    {
        if (empty($this->activeEvents)) {
            $this->activeEvents = $this->eventRepository->findBy(['active' => true, 'receivePayments' => true], ['id' => Criteria::DESC]);
        }

        return $this->activeEvents;
    }

    /**
     * @return mixed
     */
    private function getCurrentUser()
    {
        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            return null;
        }

        return $token->getUser();
    }

    /**
     * @return array
     */
    private function getActiveEventsDates(): array
    {
        $result = [];
        $events = $this->getActiveEvents();
        /** @var Event $event */
        foreach ($events as $event) {
            $result[$event->getId()] = (clone $event->getEndDateFromDates())->modify('+1 day')->format('d.m.Y H:i:s');
        }

        return $result;
    }
}
