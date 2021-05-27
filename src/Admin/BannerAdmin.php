<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Traits\LocalsRequiredServiceTrait;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\DateTimePickerType;

/**
 * BannerAdmin.
 */
class BannerAdmin extends AbstractAdmin
{
    use LocalsRequiredServiceTrait;

    /** {@inheritdoc } */
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_page'] = 1;
        $sortValues['_sort_order'] = Criteria::DESC;
        $sortValues['_sort_by'] = 'since';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $localOptions = $this->localsRequiredService->getLocalsRequiredArray(true);
        $datetimePickerOptions = [
            'dp_use_seconds' => false,
            'dp_language' => 'ru',
            'format' => 'dd.MM.y, HH:mm',
            'dp_minute_stepping' => 10,
        ];

        $form
            ->with('Переводы')
                ->add('translations', GedmoTranslationsType::class, [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'text' => [
                            'label' => 'Текст',
                            'locale_options' => $localOptions,
                        ],
                    ],
                ])
            ->end()
            ->with('Настройки')
                ->add('since', DateTimePickerType::class,
                    array_merge(
                        [
                            'required' => false,
                            'label' => 'Дата начала',
                        ],
                        $datetimePickerOptions
                    ))
                ->add('till',
                    DateTimePickerType::class,
                    \array_merge(
                        [
                            'required' => false,
                            'label' => 'Дата окончания',
                        ],
                        $datetimePickerOptions
                    ))
                ->add('backgroundColor', null, [
                    'label' => 'Фоновый цвет',
                    'required' => true,
                    'help' => 'цвет в формате #1F2B3C',
                ])
                ->add('url', null, [
                    'required' => true,
                    'label' => 'Адрес',
                    'help' => 'RegExp пример: /event/ - банер для всех ивентов, /event/\D*2021 - для всех ивентов 21го года, / - для всех страниц, /$ - только для главной',
                ])
                ->add('active')
            ->end()
        ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('url', null, [ 'label' => 'Адрес'])
            ->add('since', null, [ 'label' => 'Дата начала'])
            ->add('till', null, [ 'label' => 'Дата окончания'])
            ->add('active', null, [ 'label' => 'Включен'])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('text', null, [ 'label' => 'Текст'])
            ->add('url', null, [ 'label' => 'Адрес'])
            ->add('since', null, [ 'label' => 'Дата начала'])
            ->add('till', null, [ 'label' => 'Дата окончания'])
            ->add('active', 'boolean', [ 'label' => 'Включен', 'editable' => true])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [
                        'link_parameters' => [
                            'full' => true,
                        ]
                    ],
                ]
            ])
        ;
    }
}