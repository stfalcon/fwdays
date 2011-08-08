<?php
namespace Stfalcon\Bundle\NewsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

use Stfalcon\Bundle\NewsBundle\Entity\News;

class NewsAdmin extends Admin 
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('title')
            ->add('preview')
            ->add('created_at')
        ;
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('slug')
                ->add('title')
                ->add('preview')
                ->add('text')
                ->add('created_at')
            ->end()
        ;
    }
}