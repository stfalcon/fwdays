<?php

namespace Stfalcon\Bundle\SponsorBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Stfalcon\Bundle\EventBundle\Admin\AbstractClass\AbstractTranslateAdmin;

/**
 * SponsorAdmin Class.
 */
class CategoryAdmin extends AbstractTranslateAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('name', null, ['label' => 'Название'])
            ->add('sortOrder', null, ['label' => 'Номер сортировки'])
            ->add('isWideContainer', null, ['label' => 'Главная категория'])
            ->add('_action', 'actions', [
                'label' => 'Действие',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        $formMapper
            ->with('Переводы')
                ->add(
                    'translations',
                    'a2lix_translations_gedmo',
                    [
                        'translatable_class' => $this->getClass(),
                        'label' => 'Переводы',
                        'fields' => [
                            'name' => [
                                'label' => 'Название',
                                'locale_options' => $localOptions,
                            ],
                        ],
                    ]
                )
            ->end()
            ->with('Общие')
                ->add('isWideContainer', null, ['required' => false, 'label' => 'Главная категория (широкий контейнер)'])
                ->add('sortOrder', null, ['label' => 'Номер сортировки'])
            ->end();
    }
}
