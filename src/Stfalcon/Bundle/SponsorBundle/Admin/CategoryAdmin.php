<?php

namespace Stfalcon\Bundle\SponsorBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * SponsorAdmin Class
 */
class CategoryAdmin extends Admin
{
    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name')
            ->add('sortOrder')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
            ->add('translations', 'a2lix_translations_gedmo', [
                'translatable_class' => 'Stfalcon\Bundle\SponsorBundle\Entity\Category',
                'fields' => [
                    'name'=> [
                        'label' => 'name',
                        'locale_options' => [
                            'uk' => ['required' => true],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                ]
            ])
            ->add('sortOrder')
            ->end();
    }

}
