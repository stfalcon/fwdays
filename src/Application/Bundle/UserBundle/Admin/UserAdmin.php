<?php
namespace Application\Bundle\UserBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use FOS\UserBundle\Model\UserManagerInterface;

class UserAdmin extends Admin
{

    protected function configureDatagridFilters(DatagridMapper $datagrid)
    {
        $datagrid
            ->add('fullname')
            ->add('email')
            ->add('company')
            ->add('enabled')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('fullname')
            ->addIdentifier('email')
            ->add('company')
            ->add('enabled')
            ->add('createdAt')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('fullname')
                ->add('email')
                ->add('company', null, array('required' => false))
                ->add('post', null, array('required' => false))
                ->add('subscribe', null, array('required' => false))
//                ->add('plainPassword', 'text')
            ->end()
            ->with('Management')
//                ->add('locked', null, array('required' => false))
//                ->add('expired', null, array('required' => false))
                ->add('enabled', null, array('required' => false))
//                ->add('credentialsExpired', null, array('required' => false))
            ->end()
        ;
    }

}