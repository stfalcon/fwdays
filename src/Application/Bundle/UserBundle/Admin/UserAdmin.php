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
            ->add('fullname')
            ->add('company')
            ->add('post')
        ;
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('fullname')
                ->add('email')
                ->add('company')
                ->add('post')
//                ->add('plainPassword', 'text')
            ->end()
//            ->with('Groups')
//                ->add('groups', 'sonata_type_model', array('required' => false))
//            ->end()
            ->with('Management')
//                ->add('roles', 'sonata_security_roles', array( 'multiple' => true))
                ->add('locked', null, array('required' => false))
                ->add('expired', null, array('required' => false))
                ->add('enabled', null, array('required' => false))
                ->add('credentialsExpired', null, array('required' => false))
            ->end()
        ;
    }    

//    protected function configureFormFields(FormMapper $formMapper)
//    {
//        $formMapper
//            ->with('General')
//                ->add('username')
//                ->add('email')
//                ->add('plainPassword', 'text')
//            ->end()
//            ->with('Groups')
//                ->add('groups', 'sonata_type_model', array('required' => false))
//            ->end()
//            ->with('Management')
//                ->add('roles', 'sonata_security_roles', array( 'multiple' => true))
//                ->add('locked', null, array('required' => false))
//                ->add('expired', null, array('required' => false))
//                ->add('enabled', null, array('required' => false))
//                ->add('credentialsExpired', null, array('required' => false))
//            ->end()
//        ;
//    }
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