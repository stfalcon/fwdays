<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

use Stfalcon\Bundle\EventBundle\Entity\Speaker;

class SpeakerAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('slug')
                ->add('name')
                ->add('email')
                ->add('company')
                ->add('about')
                ->add('file', 'file', array(
                        'required' => true,
                        'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                        'property_path' => 'file'
                ))
                ->add('events', 'entity',  array(
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'multiple' => true, 'expanded' => true,
                ))
            ->end()
        ;
    }
}