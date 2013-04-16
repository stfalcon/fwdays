<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class EventAdmin
 */
class EventAdmin extends Admin
{

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name')
            ->add('active')
            ->add('receivePayments')
            ->add('cost');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('name')
                ->add('slug')
                ->add('city')
                ->add('place')
                ->add('date')
                ->add('description')
                ->add('about')
                // @todo rm array options https://github.com/dustin10/VichUploaderBundle/issues/27 and https://github.com/symfony/symfony/pull/5028
                ->add('file', 'file', array(
                    'label'         => 'Logo',
                    'required'      => false,
                    'data_class'    => 'Symfony\Component\HttpFoundation\File\File',
                    'property_path' => 'file'
                ))
                ->add('bgFile', 'file', array(
                    'label'         => 'Background image',
                    'required'      => false,
                    'data_class'    => 'Symfony\Component\HttpFoundation\File\File',
                    'property_path' => 'bgFile'
                ))
                ->add('active', null, array('required' => false))
                ->add('receivePayments', null, array('required' => false))
                ->add('cost', null, array('required' => true))
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
