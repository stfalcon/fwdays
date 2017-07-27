<?php

namespace Stfalcon\Bundle\SponsorBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * SponsorAdmin Class
 */
class SponsorAdmin extends Admin
{
    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name')
            ->add('site')
            ->add('about')
            ->add('onMain')
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
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequredArray();
        $localOptionsAllFalse = $localsRequiredService->getLocalsRequredArray(false);
        $formMapper
            ->with('General')
                ->add('translations', 'a2lix_translations_gedmo', [
                        'translatable_class' => $this->getClass(),
                        'fields' => [
                            'name'=> [
                                'label' => 'name',
                                'locale_options' => $localOptions
                            ],
                            'about'=> [
                                'label' => 'about',
                                'locale_options' => $localOptionsAllFalse
                            ],
                        ]
                ])
                ->add('slug')
                ->add('site')
                // @todo rm array options https://github.com/dustin10/VichUploaderBundle/issues/27 and https://github.com/symfony/symfony/pull/5028
                ->add('file', 'file', array(
                      'required' => false,
                      'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                      'property_path' => 'file')
                      )
                ->add('sortOrder', null, array(
                    'attr' => array(
                        'min' => 1
                    )
                ))
                ->add('onMain', null, array('required' => false))
            ->end()
            ->with('Events')
                ->add('sponsorEvents', 'sonata_type_collection',
                    array(
                        'label' => 'Events',
                        'by_reference' => false
                    ), array(
                        'edit' => 'inline',
                        'inline' => 'table',
                ))
            ->end();
    }

    /**
     * @return array|void
     */
    public function getBatchActions()
    {
        $actions = array();
    }
}
