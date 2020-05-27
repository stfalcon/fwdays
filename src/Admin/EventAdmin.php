<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\City;
use App\Entity\Event;
use App\Repository\CityRepository;
use App\Service\User\UserService;
use App\Traits\GoogleMapServiceTrait;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

/**
 * Class EventAdmin.
 */
class EventAdmin extends AbstractTranslateAdmin
{
    use LocalsRequiredServiceTrait;
    use GoogleMapServiceTrait;

    /** @var City|null */
    protected $saveCity;

    /** @var string */
    protected $savePlace;
    private $userService;
    private $cityRepository;

    /**
     * @var array
     */
    protected $datagridValues =
        [
            '_page' => 1,
            '_sort_order' => 'DESC',
            '_sort_by' => 'id',
        ];

    /**
     * @param string         $code
     * @param string         $class
     * @param string         $baseControllerName
     * @param UserService    $userService
     * @param CityRepository $cityRepository
     */
    public function __construct($code, $class, $baseControllerName, UserService $userService, CityRepository $cityRepository)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->userService = $userService;
        $this->cityRepository = $cityRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        /** @var Event $instance */
        $instance = parent::getNewInstance();
        $defaultCity = $this->cityRepository->findDefault();
        if ($defaultCity instanceof City) {
            $instance->setCity($defaultCity);
        }

        return $instance;
    }

    /**
     * @param Event $object
     */
    public function preUpdate($object): void
    {
        parent::preUpdate($object);
        foreach ($object->getBlocks() as $block) {
            $this->removeNullTranslate($block);
        }
        if ($this->saveCity !== $object->getCity() || $this->savePlace !== $object->getPlace()) {
            $this->googleMap->setEventMapPosition($object);
        }
    }

    /**
     * @param Event $object
     */
    public function prePersist($object): void
    {
        parent::prePersist($object);
        foreach ($object->getBlocks() as $block) {
            $this->removeNullTranslate($block);
        }
        $this->googleMap->setEventMapPosition($object);
    }

    /**
     * @return array
     */
    public function getBatchActions(): array
    {
        $user = $this->userService->getCurrentUser();
        $isSuperAdmin = \in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);

        if (!$isSuperAdmin) {
            return [];
        }

        return parent::getBatchActions();
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('slug')
            ->add('name', null, ['label' => 'Название'])
            ->add('active', null, ['label' => 'Активно'])
            ->add('wantsToVisitCount', null, ['label' => 'Желающих посетить событие'])
            ->add('useDiscounts', null, ['label' => 'Возможна скидка'])
            ->add('receivePayments', null, ['label' => 'Продавать билеты'])
            ->add('group', null, ['label' => 'Группа'])
            ->add('audiences', CollectionType::class, ['label' => 'Аудитории', 'by_reference' => false])
            ->add(
                'images',
                'string',
                [
                    'template' => 'Admin/images_thumb_layout.html.twig',
                    'label' => 'Изображения',
                ]
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        /** @var Event|null $subject */
        $subject = $this->getSubject();
        $objectId = null;
        $logo = null;
        $smallLogo = null;
        $backGround = null;
        $video = null;
        if (\is_object($subject) && null !== $subject->getId()) {
            $this->saveCity = $subject->getCity();
            $this->savePlace = $subject->getPlace();
            $objectId = $subject->getId();
            $logo = $subject->getLogo();
            $smallLogo = $subject->getSmallLogo();
            $backGround = $subject->getBackground();
            $video = $subject->getHeaderVideo();
        }

        $localOptions = $this->localsRequiredService->getLocalsRequiredArray();
        $localAllFalse = $this->localsRequiredService->getLocalsRequiredArray(false);
        $datetimePickerOptions =
            [
                'dp_use_seconds' => false,
                'dp_language' => 'ru',
                'format' => 'dd.MM.y, HH:mm',
                'dp_minute_stepping' => 10,
            ];

        $formMapper
            ->tab('Переводы')
                ->with('Переводы')
                    ->add('translations', GedmoTranslationsType::class, [
                        'translatable_class' => $this->getClass(),
                        'fields' => [
                            'name' => [
                                'label' => 'Название',
                                'locale_options' => $localOptions,
                            ],
                            'seoTitle' => [
                                'label' => 'Seo Title',
                                'locale_options' => $localAllFalse,
                            ],
                            'place' => [
                                'label' => 'Место проведения',
                                'locale_options' => $localAllFalse,
                                'sonata_help' => 'указывать либо точный адрес, либо название здания, где проводиться событие (используется для поиска координат на карте)',
                            ],
                            'description' => [
                                'label' => 'Краткое описание',
                                'locale_options' => $localAllFalse,
                            ],
                            'about' => [
                                'label' => 'Описание',
                                'locale_options' => $localAllFalse,
                            ],
                            'metaDescription' => [
                                'label' => 'metaDescription',
                                'locale_options' => $localAllFalse,
                            ],
                        ],
                        'label' => 'Перевод',
                    ])
                ->end()
            ->end()
            ->tab('Настройки')
                ->with('Город', ['class' => 'col-md-2'])
                    ->add('city', null, ['label' => 'Город'])
                ->end()
                ->with('Slug', ['class' => 'col-md-3'])
                    ->add('slug')
                ->end()
                ->with('Тип', ['class' => 'col-md-2'])
                    ->add(
                        'type',
                        ChoiceType::class,
                        [
                            'choices' => Event::getTypeChoices(),
                            'label' => 'Тип',
                            'required' => false,
                        ]
                    )
                ->end()
                ->with('Группа', ['class' => 'col-md-2'])
                    ->add('group', null, ['label' => 'Группа'])
                ->end()
                ->with('Аудитории', ['class' => 'col-md-2'])
                        ->add('audiences', null, ['label' => 'Аудитории'])
                ->end()
                ->with('Цены')
                    ->add(
                        'ticketsCost',
                        CollectionType::class,
                        [
                            'label' => 'Цены события',
                            'by_reference' => false,
                            'type_options' => ['delete' => true],
                            'btn_add' => null === $objectId ? false : 'Добавить цену',
                            'help' => null === $objectId ? 'добавление цен возможно только после создания события'
                                : 'добавьте блоки с ценами на билеты',
                        ],
                        [
                            'edit' => 'inline',
                            'inline' => 'table',
                        ]
                    )
                ->end()
                ->with('Переключатели', ['class' => 'col-md-4'])
                    ->add('active', null, ['required' => false, 'label' => 'Активно'])
                    ->add('receivePayments', null, ['required' => false, 'label' => 'Принимать оплату'])
                    ->add('free', null, ['required' => false, 'label' => 'Бесплатное событие'])
                    ->add('registrationOpen', null, ['required' => false, 'label' => 'Включить регистрацию'])
                    ->add('useDiscounts', null, ['required' => false, 'label' => 'Возможна скидка'])
                    ->add('adminOnly', null, ['required' => false, 'label' => 'Видимое только администраторам'])
                    ->add('smallEvent', null, ['required' => false, 'label' => 'Событие с одним потоком'])
                    ->add('online', null, ['required' => false, 'label' => 'Online событие'])
                ->end()
                ->with('Логотип и цвет', ['class' => 'col-md-4'])
                    ->add(
                        'backgroundColor',
                        null,
                        [
                            'label' => 'Цвет',
                            'required' => true,
                            'help' => 'цвет в формате #1F2B3C',
                        ]
                    )
                    ->add(
                        'logoFile',
                        FileType::class,
                        [
                            'label' => $logo ? 'Логотип | '.$logo : 'Логотип',
                            'required' => null === $logo,
                            'help' => 'Основной логотип.',
                        ]
                    )
                    ->add(
                        'smallLogoFile',
                        FileType::class,
                        [
                            'label' => $smallLogo ? 'Мини логотип | '.$smallLogo : 'Мини логотип',
                            'required' => false,
                            'help' => 'Если не указан, тогда используется основной.',
                        ]
                    )
                ->end()
                ->with('Фон', ['class' => 'col-md-4'])
                    ->add('useCustomBackground', null, ['required' => false, 'label' => 'Показать фон'])
                    ->add('showLogoWithBackground', null, ['required' => false, 'label' => 'Показать логотип на фоне'])
                    ->add(
                        'backgroundFile',
                        FileType::class,
                        [
                            'label' => $backGround ? 'Изображение | '.$backGround : 'Изображение',
                            'required' => false,
                            'help' => 'Фоновое изображение в шапке ивента.',
                        ]
                    )
                    ->add(
                        'headerVideoFile',
                        FileType::class,
                        [
                            'label' => $video ? 'Видео | '.$video : 'Видео',
                            'required' => false,
                            'help' => 'Фоновое видео в шапке ивента.',
                        ]
                    )
                ->end()
            ->end()
            ->tab('Блоки')
                ->with('Блоки')
                    ->add(
                        'blocks',
                        CollectionType::class,
                        [
                            'label' => 'Блоки отображения события',
                            'by_reference' => false,
                            'type_options' => ['delete' => true],
                            'btn_add' => null === $objectId ? false : 'Добавить блок',
                            'help' => null === $objectId ? 'добавление блоков возможно только после создания события'
                                : 'добавьте блоки отображения',
                        ],
                        [
                            'edit' => 'inline',
                            'inline' => 'table',
                        ]
                    )
                ->end()
            ->end()
            ->tab('Даты')
                ->with('Даты')
                    ->add('dateFormat', null, [
                        'required' => true,
                        'label' => 'Формат даты',
                        'help' => 'd - день (11), MMMM - полное название месяца (січень), MMM - сокращеное название месяца (січ.), 
                        MM - числовой вид месяца (01), Y - год (2018), HH:mm - время (13:45), S - время года (зима), 
                         одновремено можно использовать только либо S либо MMMM',
                    ])
                    ->add(
                        'date',
                        DateTimePickerType::class,
                        array_merge(
                            [
                                'required' => true,
                                'label' => 'Дата начала',
                            ],
                            $datetimePickerOptions
                        )
                    )
                    ->add(
                        'dateEnd',
                        DateTimePickerType::class,
                        array_merge(
                            [
                                'required' => false,
                                'label' => 'Дата окончания',
                            ],
                            $datetimePickerOptions
                        )
                    )
                ->end()
            ->end()
        ;
    }
}
