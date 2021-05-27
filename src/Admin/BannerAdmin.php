<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
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

    protected function configureFormFields(FormMapper $form): void
    {
        $localOptions = $this->localsRequiredService->getLocalsRequiredArray(true);
        $datetimePickerOptions =
            [
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
                            'required' => true,
                            'label' => 'Дата начала',
                        ],
                        $datetimePickerOptions
                    ))
                ->add('till',
                    DateTimePickerType::class,
                    \array_merge(
                        [
                            'required' => true,
                            'label' => 'Дата окончания',
                        ],
                        $datetimePickerOptions
                    ))
                ->add('backgroundColor', null, [
                    'label' => 'Фоновый цвет',
                    'required' => true,
                    'help' => 'цвет в формате #1F2B3C',
                ])
                ->add('url', null, ['required' => true])
                ->add('active')
            ->end()
        ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
    }

    protected function configureListFields(ListMapper $list): void
    {
    }
}