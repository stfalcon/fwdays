<?php

namespace App\Admin\AbstractClass;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class AbstractPageAdmin.
 */
abstract class AbstractPageAdmin extends AbstractTranslateAdmin
{
    use LocalsRequiredServiceTrait;

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('title', null, ['label' => 'Название']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $localOptions = $this->localsRequiredService->getLocalsRequiredArray();
        $localOptionsAllFalse = $this->localsRequiredService->getLocalsRequiredArray(false);
        $formMapper
            ->with('Переводы')
                ->add('translations', GedmoTranslationsType::class, [
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
    }
}
