<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use A2lix\TranslationFormBundle\Util\GedmoTranslatable;
use Application\Bundle\UserBundle\Entity\User;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class EventAdmin.
 */
class EventAdmin extends Admin
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
            $isSuperAdmin = $user instanceof User ? in_array('ROLE_SUPER_ADMIN', $user->getRoles()) : false;
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
                    'template' => 'StfalconEventBundle:Admin:images_thumb_layout.html.twig',
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
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequredArray();
        $localAllFalse = $localsRequiredService->getLocalsRequredArray(false);
        $datetimePickerOptions =
            [
                'dp_use_seconds' => false,
                'dp_language' => 'ru',
                'format' => 'dd.MM.y, HH:mm',
                'dp_minute_stepping' => 10,
            ];

        $formMapper
            ->with('Переводы')
                ->add('translations', 'a2lix_translations_gedmo', [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'name' => [
                            'label' => 'Название',
                            'locale_options' => $localOptions,
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
                            'locale_options' => $localOptions,
                        ],
                        'about' => [
                            'label' => 'Описание',
                            'locale_options' => $localOptions,
                        ],
                        'metaDescription' => [
                            'label' => 'metaDescription',
                            'locale_options' => $localAllFalse,
                        ],
                    ],
                    'label' => 'Перевод',
                ])
            ->end()
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
                ->add('active', null, ['required' => false, 'label' => 'Активно'])
                ->add('receivePayments', null, ['required' => false, 'label' => 'Принимать оплату'])
                ->add('useDiscounts', null, ['required' => false, 'label' => 'Возможна скидка'])
                ->add('smallEvent', null, ['required' => false, 'label' => 'Событие с одним потоком'])
                ->add('adminOnly', null, ['required' => false, 'label' => 'Видимое только администраторам'])
                ->add('useCustomBackground', null, ['required' => false, 'label' => 'Использовать фоновое изображение'])
            ->end()
            ->with('Даты', ['class' => 'col-md-6'])
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
                    'logoFile',
                    'file',
                    [
                        'label' => 'Логотип',
                        'required' => is_null($subject->getLogo()),
                        'help' => 'Осноное изображения.',
                    ]
                )
                ->add(
                    'smallLogoFile',
                    'file',
                    [
                        'label' => 'Мини логотип',
                        'required' => false,
                        'help' => 'Если не указан, тогда используєтся основной.',
                    ]
                )
            ->end();
    }

    /**
     * @param GedmoTranslatable $object
     */
    private function removeNullTranslate($object)
    {
        foreach ($object->getTranslations() as $key => $translation) {
            if (!$translation->getContent()) {
                $object->getTranslations()->removeElement($translation);
            }
        }
    }
}
