<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class EventAdmin.
 */
class EventAdmin extends Admin
{
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
     * @param $object
     *
     * @return mixed|void
     */
    public function preUpdate($object)
    {
        $this->removeNullTranslate($object);
    }

    /**
     * @param $object
     *
     * @return mixed|void
     */
    public function prePersist($object)
    {
        $this->removeNullTranslate($object);
    }

    /**
     * @return array|void
     */
    public function getBatchActions()
    {
        $actions = array();
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
        $subject = $this->getSubject();
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
                            'required' => false,
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
                ->add('backgroundColor', 'sonata_type_color_selector', ['required' => true, 'label' => 'Цвет фона'])
                ->add(
                    'logoFile',
                    'file',
                    [
                        'label' => 'Логотип',
                        'required' => is_null($subject->getLogo()),
                        'help' => 'Изображения должно быть квадратное.',
                    ]
                )
                ->add(
                    'pdfBackgroundFile',
                    'file',
                    [
                        'label' => 'Изображение для pdf',
                        'required' => false,
                        'help' => 'Левый верхний угол.',
                    ]
                )
                ->add(
                    'emailBackgroundFile',
                    'file',
                    [
                        'label' => 'Изображение для писем',
                        'required' => false,
                        'help' => 'Левый правый угол.',
                    ]
                )
            ->end();
    }

    /**
     * @param $object
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
