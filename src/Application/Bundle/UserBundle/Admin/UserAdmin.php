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
    public function prePersist($project)
    {
        $project->setTickets($project->getTickets());
    }

    public function preUpdate($project)
    {
        $project->setTickets($project->getTickets());
    }

    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            ['@ApplicationDefault/Admin/admin.light_theme.html.twig']
        );
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name', null, ['label' => 'Имя'])
            ->add('surname', null, ['label' => 'Фамилия'])
            ->add('phone', null, ['label' => 'Телефон'])
            ->add('email', null, ['label' => 'Почта'])
            ->add('company', null, ['label' => 'Компания'])
            ->add('balance', null, ['label' => 'Баланс'])
            ->add('enabled', null, ['label' => 'Активирован'])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, ['label' => 'Имя'])
            ->add('surname', null, ['label' => 'Фамилия'])
            ->addIdentifier('email', null, ['label' => 'Почта'])
            ->add('phone', null, ['label' => 'Номер телефона'])
            ->add('company', null, ['label' => 'Компания'])
            ->add('balance', null, ['label' => 'Баланс'])
            ->add('enabled', null, ['label' => 'Активирован'])
            ->add('createdAt', null, ['label' => 'Дата создания']);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Общие')
                ->with('Общие')
                    ->add('name', null, ['required' => true, 'label' => 'Имя'])
                    ->add('surname', null, ['required' => true, 'label' => 'Фамилия'])
                    ->add('email', null, ['required' => true, 'label' => 'Почта'])
                    ->add('phone', null, ['required' => false, 'label' => 'Номер телефона'])
                    ->add('company', null, ['required' => false, 'label' => 'Компания'])
                    ->add('post', null, ['required' => false, 'label' => 'Должность'])
                    ->add('balance', null, ['required' => false, 'label' => 'Баланс'])
                    ->add('subscribe', null, ['required' => false, 'label' => 'Подписан на рассылку'])

                ->end()
            ->end()
            ->tab('Билеты')
                ->with('Билеты')
                    ->add(
                        'tickets',
                        'sonata_type_collection',
                        [
                            'by_reference' => false,
                            'disabled' => true,
                            'type_options' => [
                                'delete' => false,
                            ],
                        ],
                        [
                            'edit' => 'inline',
                            'inline' => 'table',
                            'sortable' => 'id',
                        ]
                    )
                ->end()
            ->end()
            ->tab('Management')
                ->with('Management')
                    ->add('plainPassword', 'text', ['required' => null === $this->getSubject()->getId(), 'label' => 'Пароль'])
                    ->add('enabled', null, ['required' => false, 'label' => 'Активирован'])
                    ->add(
                        'roles',
                        'choice',
                        [
                            'choices' => $this->getAvailableRoles(),
                            'multiple' => true,
                            'required' => false,
                            'label' => 'Роли',
                        ]
                    )
                ->end()
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
