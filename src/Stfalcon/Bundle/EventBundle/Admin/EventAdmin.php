<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

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
            ->add('useDiscounts')
            ->add('receivePayments')
            ->add('cost')
            ->add(
                'images',
                'string',
                array(
                    'template' => 'StfalconEventBundle:Admin:images_thumb_layout.html.twig'
                )
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $formMapper
            ->with('General')
            ->add('name')
            ->add('slug')
            ->add('city')
            ->add('place')
            ->add('date')
            ->add('description')
            ->add('about')
            ->add('active', null, array('required' => false))
            ->add('receivePayments', null, array('required' => false))
            ->add('useDiscounts', null, array('required' => false))
            ->add('cost', null, array('required' => true))
            ->end()
            ->with('Images')
            ->add(
                'logoFile',
                'file',
                array(
                    'label' => 'Logo',
                    'required' => is_null($subject->getLogo())
                )
            )
            ->add(
                'pdfBackgroundFile',
                'file',
                array(
                    'label' => 'Background image',
                    'required' => false,
                )
            )
            ->add(
                'emailBackgroundFile',
                'file',
                array(
                    'label' => 'Email background',
                    'required' => false,
                )
            )
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
