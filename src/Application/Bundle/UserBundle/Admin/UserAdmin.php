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
            ->add('id')
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
            ->add('createdAt');
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
                ->add('plainPassword', 'text', array('required' => false))
            ->end()
            ->with('Management')
                ->add('enabled', null, array('required' => false))
                ->add('roles', 'choice', array(
                    'choices' => $this->getAvailableRoles(),
                    'multiple' => true,
                    'required' => false
                ))
            ->end();
    }

    /**
     * @return array
     */
    private function getAvailableRoles()
    {
        $roles = array();
        $rolesHierarhy = $this->getConfigurationPool()->getContainer()
            ->getParameter('security.role_hierarchy.roles');
        foreach (array_keys($rolesHierarhy) as $role) {
            $roles[$role] = $role;
        }

        return $roles;
    }

}