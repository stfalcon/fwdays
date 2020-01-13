<?php

namespace App\Admin;

use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\Event;
use App\Form\Type\MyGedmoTranslationsType;
use App\Service\GoogleMapService;
use App\Service\LocalsRequiredService;
use App\Service\User\UserService;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

/**
 * Class EventAdmin.
 */
class EventAdmin extends AbstractTranslateAdmin
{
    /** @var string */
    protected $saveCity;

    /** @var string */
    protected $savePlace;

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
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $this->removeNullTranslate($object);
        foreach ($object->getBlocks() as $block) {
            $this->removeNullTranslate($block);
        }
        if ($this->saveCity !== $object->getCity() || $this->savePlace !== $object->getPlace()) {
            $this->getConfigurationPool()->getContainer()->get(GoogleMapService::class)
                ->setEventMapPosition($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $this->removeNullTranslate($object);
        foreach ($object->getBlocks() as $block) {
            $this->removeNullTranslate($block);
        }
        $this->getConfigurationPool()->getContainer()->get(GoogleMapService::class)
            ->setEventMapPosition($object);
    }

    /**
     * @return array
     */
    public function getBatchActions()
    {
        $container = $this->getConfigurationPool()->getContainer();

        $userService = $container->get(UserService::class);
        $user = $userService->getCurrentUser();
        $isSuperAdmin = \in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);

        if (!$isSuperAdmin) {
            return [];
        }

        return parent::getBatchActions();
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
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
                    'template' => 'AppBundle:Admin:images_thumb_layout.html.twig',
                    'label' => 'Изображения',
                ]
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Event $subject */
        $subject = $this->getSubject();
        if (null !== $subject->getId()) {
            $this->saveCity = $subject->getCity();
            $this->savePlace = $subject->getPlace();
        }
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get(LocalsRequiredService::class);
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        $localAllFalse = $localsRequiredService->getLocalsRequiredArray(false);
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
                    ->add('translations', MyGedmoTranslationsType::class, [
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
                            'city' => [
                                'label' => 'Город',
                                'locale_options' => $localAllFalse,
                                'sonata_help' => 'указывать город в котором проводиться событие (используется для поиска координат на карте)',
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
                ->with('Slug', ['class' => 'col-md-4'])
                    ->add('slug')
                ->end()
                ->with('Группа', ['class' => 'col-md-4'])
                    ->add('group', null, ['label' => 'Группа'])
                ->end()
                ->with('Аудитории', ['class' => 'col-md-4'])
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
                            'btn_add' => null === $subject->getId() ? false : 'Добавить цену',
                            'help' => null === $subject->getId() ? 'добавление цен возможно только после создания события'
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
                    ->add('useDiscounts', null, ['required' => false, 'label' => 'Возможна скидка'])
                    ->add('adminOnly', null, ['required' => false, 'label' => 'Видимое только администраторам'])
                    ->add('smallEvent', null, ['required' => false, 'label' => 'Событие с одним потоком'])
                    ->add('useCustomBackground', null, ['required' => false, 'label' => 'Показать фон'])
                    ->add('showLogoWithBackground', null, ['required' => false, 'label' => 'Показать логотип на фоне'])
                ->end()
                ->with('Логотип и цвет', ['class' => 'col-md-4'])
                    ->add(
                        'backgroundColor',
                        'text',
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
                            'label' => $subject->getLogo() ? 'Логотип | '.$subject->getLogo() : 'Логотип',
                            'required' => null === $subject->getLogo(),
                            'help' => 'Основной логотип.',
                        ]
                    )
                    ->add(
                        'smallLogoFile',
                        FileType::class,
                        [
                            'label' => $subject->getSmallLogo() ? 'Мини логотип | '.$subject->getSmallLogo() : 'Мини логотип',
                            'required' => false,
                            'help' => 'Если не указан, тогда используется основной.',
                        ]
                    )
                ->end()
                ->with('Фон', ['class' => 'col-md-4'])
                    ->add(
                        'backgroundFile',
                        FileType::class,
                        [
                            'label' => $subject->getBackground() ? 'Изображение | '.$subject->getBackground() : 'Изображение',
                            'required' => false,
                            'help' => 'Фоновое изображение в шапке ивента.',
                        ]
                    )
                    ->add(
                        'headerVideoFile',
                        FileType::class,
                        [
                            'label' => $subject->getHeaderVideo() ? 'Видео | '.$subject->getHeaderVideo() : 'Видео',
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
                            'btn_add' => null === $subject->getId() ? false : 'Добавить блок',
                            'help' => null === $subject->getId() ? 'добавление блоков возможно только после создания события'
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
                        'sonata_type_datetime_picker',
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
                        'sonata_type_datetime_picker',
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
