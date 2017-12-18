<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Stfalcon\Bundle\EventBundle\Admin\AbstractClass\AbstractPageAdmin;

class ReviewAdmin extends AbstractPageAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper = parent::configureListFields($listMapper);
        $listMapper
            ->add('event', null, ['label' => 'Событие'])
            ->add('speakers', null, ['label' => 'Докладчики']);
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper = parent::configureFormFields($formMapper);
        $formMapper
            ->with('Общие')
                ->add('event', 'entity', [
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'label' => 'Событие',
                ])
                ->add('speakers', 'entity', [
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Speaker',
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'Докладчики',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event');
    }
}