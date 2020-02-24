<?php

namespace App\Admin;

use App\Entity\User;
use App\Service\User\UserService;
use App\Traits\TokenStorageTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UserAdmin.
 */
final class UserAdmin extends AbstractAdmin
{
    use TokenStorageTrait;

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this->setTemplate('list', 'Admin/list_with_js.html.twig');
    }

    /**
     * @param object $object
     */
    public function preRemove($object): void
    {
        $user = $this->getCurrentUser();

        if (!$user instanceof User || !\in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccess($action, $object = null): bool
    {
        $result = parent::hasAccess($action, $object);
        if ('delete' === $action) {
            $user = $this->getCurrentUser();
            if ($user instanceof User && !\in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
                return false;
            }
        }

        return $result;
    }

    /**
     * @param User $project
     */
    public function prePersist($project): void
    {
        $project->setTickets($project->getTickets());
    }

    /**
     * @param User $project
     */
    public function preUpdate($project): void
    {
        $project->setTickets($project->getTickets());
    }

    /**
     * @return array
     */
    public function getFormTheme(): array
    {
        return array_merge(
            parent::getFormTheme(),
            ['Admin/admin.light_theme.html.twig']
        );
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
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
                'emailLanguage',
                null,
                ['label' => 'Язык рассылки'],
                ChoiceType::class,
                [
                    'choices' => [
                        'Украинский' => 'uk',
                        'Английский' => 'en',
                    ],
                ]
            )
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
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
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $container = $this->getConfigurationPool()->getContainer();
        if (!$container instanceof ContainerInterface) {
            throw new BadRequestHttpException('container not found');
        }
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $user = $userService->getCurrentUser();
        $environment = $container->getParameter('kernel.environment');

        $isSuperAdmin = \in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true) || 'dev' === $environment;
        $object = $this->getSubject();
        $objectId = null;
        if ($object instanceof User) {
            $objectId = $object->getId();
        }

        $formMapper
            ->tab('Общие')
                ->with('Общие')
                    ->add('name', null, ['required' => true, 'label' => 'Имя'])
                    ->add('surname', null, ['required' => true, 'label' => 'Фамилия'])
                    ->add(
                        'email',
                        EmailType::class,
                        [
                            'required' => true,
                            'label' => 'Почта',
                            'disabled' => !$isSuperAdmin && $objectId,
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
                        CollectionType::class,
                        [
                            'by_reference' => false,
                            'disabled' => true,
//                            'allow_delete' => false,
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
                        TextType::class,
                        [
                            'required' => null === $objectId,
                            'label' => 'Пароль',
                            'disabled' => !$isSuperAdmin && $objectId,
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
            ->end()
        ;
    }

    /**
     * @return array
     */
    private function getAvailableRoles(): array
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
     * @return mixed
     */
    private function getCurrentUser()
    {
        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            return null;
        }

        return $token->getUser();
    }
}
