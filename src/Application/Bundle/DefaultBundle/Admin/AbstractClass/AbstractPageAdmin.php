<?php

namespace Application\Bundle\DefaultBundle\Admin\AbstractClass;

use Application\Bundle\DefaultBundle\Form\Type\MyGedmoTranslationsType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class AbstractPageAdmin.
 */
abstract class AbstractPageAdmin extends AbstractTranslateAdmin
{
    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     *
     * @return \Sonata\AdminBundle\Datagrid\ListMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('title', null, ['label' => 'Название']);

        return $listMapper;
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return \Sonata\AdminBundle\Form\FormMapper
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
            ->end()
        ;

        return $formMapper;
    }
}
