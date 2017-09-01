<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class PromoCodeAdmin
 */
class PromoCodeAdmin extends Admin
{

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('discountAmount')
            ->add('code')
            ->add('event')
            ->add('endDate');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequredArray();
        $formMapper
            ->with('General')
                ->add('translations', 'a2lix_translations_gedmo', [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'title'=> [
                            'label' => 'title',
                            'locale_options' => $localOptions
                        ]
                    ]
                ])
                ->add('title')
                ->add('discountAmount')
                ->add('code')
                ->add('event', null, array(
                    'required' => true,
                    'placeholder' => 'Choose event'
                ))
                ->add('endDate', 'date', array(
                    'widget' => 'single_text'
                ))
            ->end();
    }
}
