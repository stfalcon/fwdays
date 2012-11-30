<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Stfalcon\Bundle\NewsBundle\Admin\NewsAdmin as BaseNewsAdmin;

class NewsAdmin extends BaseNewsAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper = parent::configureListFields($listMapper);
        $listMapper->add('event');
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper = parent::configureFormFields($formMapper);
        $formMapper
            ->with('General')
                ->add('event', 'entity',  array(
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
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