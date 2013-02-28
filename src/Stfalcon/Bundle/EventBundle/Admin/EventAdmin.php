<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EventAdmin extends Admin
{

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name')
            ->add('active')
            ->add('receivePayments')
            ->add('cost')
        ;
    }

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
                ->add('logoFile', 'file', array(
                        'label' => 'Logo',
                        'required' => false,
                        'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                        'property_path' => 'logoFile'
                ))
                ->add('bgFile', 'file', array(
                        'label' => 'Background image',
                        'required' => false,
                        'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                        'property_path' => 'bgFile'
                ))
                ->add('emailBackgroundFile', 'file', array(
                        'label' => 'Email background',
                        'required' => false,
                        'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                        'property_path' => 'emailBackgroundFile'
                ))
                ->add('sliderBackgroundFile', 'file', array(
                        'label' => 'Slider background',
                        'required' => false,
                        'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                        'property_path' => 'sliderBackgroundFile'
                ))
                ->add('active', null, array('required' => false))
                ->add('receivePayments', null, array('required' => false))
                ->add('cost', null, array('required' => true))
            ->end()
        ;
    }

    public function getBatchActions()
    {
        $actions = array();
    }
}
