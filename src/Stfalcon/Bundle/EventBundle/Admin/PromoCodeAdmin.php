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
        $formMapper
            ->with('General')
                ->add('title')
                ->add('discountAmount')
                ->add('code')
                ->add('event', null, array(
                    'required' => true,
                    'empty_value' => 'Choose event'
                ))
                ->add('endDate', 'date', array(
                    'widget' => 'single_text'
                ))
            ->end();
    }
}
