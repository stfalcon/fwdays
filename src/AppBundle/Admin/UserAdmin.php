<?php

namespace App\Admin;

use App\Entity\Event;
use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
        $this->setTemplate('list', 'AppBundle:Admin:list_with_js.html.twig');
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
            ['@App/Admin/admin.light_theme.html.twig']
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
            ->add(
                'wantsToVisitEvents',
                null,
                ['label' => 'Зарегистрировались на событие'],
                EntityType::class,
                ['choices' => $this->getEvents()]
            )
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
        $userService = $container->get(UserService::class);
        $user = $userService->getCurrentUser();
        $environment = $container->getParameter('kernel.environment');
        $isSuperAdmin = \in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true) || 'dev' === $environment;

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
        $roles = [];
        $rolesHierarhy = $this->getConfigurationPool()->getContainer()
            ->getParameter('security.role_hierarchy.roles');
        foreach (array_keys($rolesHierarhy) as $role) {
            $roles[$role] = $role;
        }

        return $roles;
    }

    /**
     * @return array
     */
    private function getEvents(): array
    {
        $eventRepository = $this->getConfigurationPool()->getContainer()->get('doctrine')->getRepository(Event::class);

        return $eventRepository->findBy([], ['id' => Criteria::DESC]);
    }
}