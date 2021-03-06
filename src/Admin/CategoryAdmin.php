<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * SponsorAdmin Class.
 */
class CategoryAdmin extends AbstractTranslateAdmin
{
    use LocalsRequiredServiceTrait;

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->remove('application.admin.category.delete');
    }

    /**
     * Allows you to customize batch actions.
     *
     * @param array $actions List of actions
     *
     * @return array
     */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
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
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $localOptions = $this->localsRequiredService->getLocalsRequiredArray();
        $formMapper
            ->with('Переводы')
                ->add(
                    'translations',
                    GedmoTranslationsType::class,
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
