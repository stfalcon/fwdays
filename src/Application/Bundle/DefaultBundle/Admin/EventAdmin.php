<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Application\Bundle\DefaultBundle\Entity\User;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Application\Bundle\DefaultBundle\Admin\AbstractClass\AbstractTranslateAdmin;
use Application\Bundle\DefaultBundle\Entity\Event;

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
            $this->getConfigurationPool()->getContainer()->get('app.service.google_map_service')
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
        $this->getConfigurationPool()->getContainer()->get('app.service.google_map_service')
            ->setEventMapPosition($object);
    }

    /**
     * @return array
     */
    public function getBatchActions()
    {
        $container = $this->getConfigurationPool()->getContainer();
        $token = $container->get('security.token_storage')->getToken();
        $isSuperAdmin = false;
        if ($token) {
            $user = $token->getUser();
            $isSuperAdmin = $user instanceof User ? \in_array('ROLE_SUPER_ADMIN', $user->getRoles()) : false;
        }

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
            ->add('audiences', null, ['label' => 'Аудитории'])
            ->add(
                'images',
                'string',
                [
                    'template' => 'ApplicationDefaultBundle:Admin:images_thumb_layout.html.twig',
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
        if (!is_null($subject->getId())) {
            $this->saveCity = $subject->getCity();
            $this->savePlace = $subject->getPlace();
        }
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application.sonata.locales.required');
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
                    ->add('translations', 'a2lix_translations_gedmo', [
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
                                'locale_options' => $localOptions,
                                'sonata_help' => 'указывать город в котором проводиться событие (используется для поиска координат на карте)',
                            ],
                            'place' => [
                                'label' => 'Место проведения',
                                'locale_options' => $localOptions,
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
                ->with('Настройки')
                    ->add('slug')
                    ->add('group', null, ['label' => 'Группа'])
                    ->add('audiences', null, ['label' => 'Аудитории'])
                    ->add(
                        'ticketsCost',
                        'sonata_type_collection',
                        [
                            'label' => 'Цены события',
                            'by_reference' => false,
                            'type_options' => ['delete' => true],
                            'btn_add' => is_null($subject->getId()) ? false : 'Добавить цену',
                            'help' => is_null($subject->getId()) ? 'добавление цен возможно только после создания события'
                                : 'добавьте блоки с ценами на билеты',
                        ],
                        [
                            'edit' => 'inline',
                            'inline' => 'table',
                        ]
                    )
                ->end()
                ->with('Переключатели', ['class' => 'col-md-6'])
                    ->add('active', null, ['required' => false, 'label' => 'Активно'])
                    ->add('receivePayments', null, ['required' => false, 'label' => 'Принимать оплату'])
                    ->add('useDiscounts', null, ['required' => false, 'label' => 'Возможна скидка'])
                    ->add('adminOnly', null, ['required' => false, 'label' => 'Видимое только администраторам'])
                    ->add('smallEvent', null, ['required' => false, 'label' => 'Событие с одним потоком'])
                    ->add('useCustomBackground', null, ['required' => false, 'label' => 'Использовать фоновое изображение'])
                    ->add('showLogoWithBackground', null, ['required' => false, 'label' => 'Использовать логотип c фоновым изображением'])
                ->end()
            ->with('Изображения и цвет', ['class' => 'col-md-6'])
                ->add(
                    'backgroundColor',
                    'text',
                    [
                        'required' => true,
                        'label' => 'Цвет фона',
                        'help' => 'цвет в формате #1F2B3C',
                    ]
                )
                ->add(
                    'backgroundFile',
                    'file',
                    [
                        'label' => 'Фоновое изображение',
                        'required' => false,
                        'help' => 'Заменяет цвет фона на странице ивента. '.$subject->getBackground(),
                    ]
                )
                ->add(
                    'logoFile',
                    'file',
                    [
                        'label' => 'Логотип',
                        'required' => is_null($subject->getLogo()),
                        'help' => 'Основное изображение. '.$subject->getLogo(),
                    ]
                )
                ->add(
                    'smallLogoFile',
                    'file',
                    [
                        'label' => 'Мини логотип',
                        'required' => false,
                        'help' => 'Если не указан, тогда используєтся основной. '.$subject->getSmallLogo(),
                    ]
                )
                ->end()
            ->end()
            ->tab('Блоки')
                ->with('Блоки')
                    ->add(
                        'blocks',
                        'sonata_type_collection',
                        [
                            'label' => 'Блоки отображения события',
                            'by_reference' => false,
                            'type_options' => ['delete' => true],
                            'btn_add' => is_null($subject->getId()) ? false : 'Добавить блок',
                            'help' => is_null($subject->getId()) ? 'добавление блоков возможно только после создания события'
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
