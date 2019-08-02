<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Application\Bundle\DefaultBundle\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class UserAdmin.
 */
final class UserAdmin extends AbstractAdmin
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setTemplate('list', 'ApplicationDefaultBundle:Admin:list_with_js.html.twig');
    }

    /**
     * @param User $project
     *
     * @return mixed|void
     */
    public function prePersist($project)
    {
        $project->setTickets($project->getTickets());
    }

    /**
     * @param User $project
     *
     * @return mixed|void
     */
    public function preUpdate($project)
    {
        $project->setTickets($project->getTickets());
    }

    /**
     * @return array
     */
    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            ['@ApplicationDefault/Admin/admin.light_theme.html.twig']
        );
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('fullname', null, ['label' => 'Имя Фамилия'])
            ->add('phone', null, ['label' => 'Телефон'])
            ->add('email', null, ['label' => 'Почта'])
            ->add('company', null, ['label' => 'Компания'])
            ->add('balance', null, ['label' => 'Баланс'])
            ->add('enabled', null, ['label' => 'Активирован'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
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

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $token = $container->get('security.token_storage')->getToken();
        $isSuperAdmin = false;
        if ($token) {
            $user = $token->getUser();
            $isSuperAdmin = $user instanceof User ? in_array('ROLE_SUPER_ADMIN', $user->getRoles()) : false;
        }

        $formMapper
            ->tab('Общие')
                ->with('Общие')
                    ->add('name', null, ['required' => true, 'label' => 'Имя'])
                    ->add('surname', null, ['required' => true, 'label' => 'Фамилия'])
                    ->add(
                        'email',
                        'email',
                        [
                            'required' => true,
                            'label' => 'Почта',
                            'disabled' => !$isSuperAdmin && $this->getSubject()->getId(),
                        ]
                    )
                    ->add('phone', null, ['required' => false, 'label' => 'Номер телефона'])
                    ->add('company', null, ['required' => false, 'label' => 'Компания'])
                    ->add('post', null, ['required' => false, 'label' => 'Должность'])
                    ->add('balance', null, ['required' => false, 'label' => 'Баланс', 'disabled' => !$isSuperAdmin])
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
                    ->add(
                        'plainPassword',
                        'text',
                        [
                            'required' => null === $this->getSubject()->getId(),
                            'label' => 'Пароль',
                            'disabled' => !$isSuperAdmin && $this->getSubject()->getId(),
                        ]
                    )
                    ->add('enabled', null, ['required' => false, 'label' => 'Активирован'])
                    ->add(
                        'roles',
                        ChoiceType::class,
                        [
                            'choices' => $this->getAvailableRoles(),
                            'multiple' => true,
                            'required' => false,
                            'label' => 'Роли',
                            'disabled' => !$isSuperAdmin,
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
