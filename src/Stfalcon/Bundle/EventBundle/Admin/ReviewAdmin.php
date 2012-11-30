<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Stfalcon\Bundle\PageBundle\Admin\PageAdmin as BasePageAdmin;

class ReviewAdmin extends BasePageAdmin 
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper = parent::configureListFields($listMapper);
        $listMapper->add('event');
        $listMapper->add('speakers');
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper = parent::configureFormFields($formMapper);
        $formMapper
            ->with('General')
                ->add('event', 'entity',  array(
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                ))
                ->add('speakers', 'entity',  array(
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Speaker',
                    'multiple' => true, 'expanded' => true,
                ))
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event');
    }
}