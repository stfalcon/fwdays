<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Application\Bundle\DefaultBundle\Admin\AbstractClass\AbstractTranslateAdmin;
use Application\Bundle\DefaultBundle\Form\Type\MyGedmoTranslationsType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * SponsorAdmin Class.
 */
class CategoryAdmin extends AbstractTranslateAdmin
{
    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
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
    protected function configureBatchActions($actions)
    {
        unset($actions['delete']);

        return $actions;
    }

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
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        $formMapper
            ->with('Переводы')
                ->add(
                    'translations',
                    MyGedmoTranslationsType::class,
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
