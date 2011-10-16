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
            ->add('email')
            ->add('fullname')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('email')
            ->add('enabled')
            ->add('fullname')
            ->add('company')
            ->add('comment')
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
                ->add('comment', null, array('required' => false))
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

    public function getBatchActions()
    {
        return array();
    }

//    public function preUpdate($user)
//    {
//        $this->getUserManager()->updateCanonicalFields($user);
//        $this->getUserManager()->updatePassword($user);
//    }
//
//    public function setUserManager(UserManagerInterface $userManager)
//    {
//        $this->userManager = $userManager;
//    }
//
//    /**
//     * @return UserManagerInterface
//     */
//    public function getUserManager()
//    {
//        return $this->userManager;
//    }
}