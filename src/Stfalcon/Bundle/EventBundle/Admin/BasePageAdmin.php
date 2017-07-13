<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

abstract class BasePageAdmin extends Admin
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
            ->add('title');

        return $listMapper;
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('translations', 'a2lix_translations_gedmo', [
                'translatable_class' => 'Stfalcon\Bundle\EventBundle\Entity\Page',
                'fields' => [
                    'title' => [
                        'label' => 'title',
                        'locale_options' => [
                            'uk' => ['required' => true],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                    'text' => [
                        'label' => 'text',
                        'locale_options' => [
                            'uk' => ['required' => true],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                    'metaKeywords' => [
                        'label' => 'metaKeywords',
                        'locale_options' => [
                            'uk' => ['required' => false],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                    'metaDescription' => [
                        'label' => 'metaDescription',
                        'locale_options' => [
                            'uk' => ['required' => false],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                ]
            ])
            ->add('slug')
        ;

        return $formMapper;
    }
}