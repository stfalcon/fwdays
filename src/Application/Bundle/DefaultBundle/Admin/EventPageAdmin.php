<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Application\Bundle\DefaultBundle\Form\Type\MyGedmoTranslationsType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Application\Bundle\DefaultBundle\Admin\AbstractClass\AbstractPageAdmin;

/**
 * Class EventPageAdmin.
 */
final class EventPageAdmin extends AbstractPageAdmin
{
    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     *
     * @return \Sonata\AdminBundle\Datagrid\ListMapper|void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper = parent::configureListFields($listMapper);
        $listMapper
            ->add('event', null, ['label' => 'Событие'])
            ->add('sortOrder', null, ['label' => 'Номер сортировки']);
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        $localOptionsAllFalse = $localsRequiredService->getLocalsRequiredArray(false);
        $formMapper
            ->with('Переводы')
                ->add('translations', MyGedmoTranslationsType::class, [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'title' => [
                            'label' => 'Название',
                            'locale_options' => $localOptions,
                        ],
                        'text' => [
                            'label' => 'текст',
                            'locale_options' => $localOptions,
                        ],
                        'textNew' => [
                            'label' => 'текст для нового дизайна',
                            'locale_options' => $localOptionsAllFalse,
                        ],
                        'metaKeywords' => [
                            'label' => 'metaKeywords',
                            'locale_options' => $localOptionsAllFalse,
                        ],
                        'metaDescription' => [
                            'label' => 'metaDescription',
                            'locale_options' => $localOptionsAllFalse,
                        ],
                    ],
                ])
            ->end()
            ->with('Общие')
                ->add('slug')
                ->add('event', 'entity', [
                    'class' => 'Application\Bundle\DefaultBundle\Entity\Event',
                ])
                ->add('showInMenu', null, ['required' => false, 'label' => 'Показывать страницу'])
                ->add('sortOrder', null, [
                    'label' => 'Номер сортировки',
                    'attr' => ['min' => 1],
                ])
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event');
    }
}
