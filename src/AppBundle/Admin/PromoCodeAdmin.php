<?php

namespace App\Admin;

use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\Event;
use App\Entity\PromoCode;
use App\Entity\User;
use App\Form\Type\MyGedmoTranslationsType;
use App\Service\LocalsRequiredService;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class PromoCodeAdmin.
 */
class PromoCodeAdmin extends AbstractTranslateAdmin
{
    /**
     * Allows you to customize batch actions.
     *
     * @param array $actions List of actions
     *
     * @return array
     */
    protected function configureBatchActions($actions): array
    {
        $user = $this->getCurrentUser();
        if (!$user instanceof User || !\in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            unset($actions['delete']);
        }

        return $actions;
    }

    /**
     * @param object $object
     */
    public function preRemove($object)
    {
        $user = $this->getCurrentUser();

        if (!$user instanceof User || !\in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
           throw new AccessDeniedException();
        }
    }

    /**
     * @var array
     */
    protected $datagridValues =
        [
            '_page' => 1,
            '_sort_order' => 'DESC',
            '_sort_by' => 'id',
        ];

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, ['label' => 'Название'])
            ->add('discountAmount', null, ['label' => 'Скидка (%)'])
            ->add('code', null, ['label' => 'Код'])
            ->add('event', null, ['label' => 'Событие'])
            ->add('used', null, ['label' => 'Использований'])
            ->add('endDate', null, ['label' => 'Дата окончания'])
            ->add('createdBy', null, ['label' => 'Создал'])
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get(LocalsRequiredService::class);
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        /** @var PromoCode|null $promocode */
        $promocode = $this->getSubject();
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user instanceof User && \in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);
        $creator = null;
        $allowedToEdit = false;
        if ($promocode instanceof PromoCode) {
            $creator = $promocode->getCreatedBy();
            $isCreator = $user instanceof User && $creator instanceof User && $user->isEqualTo($creator);
            $allowedToEdit = ($isCreator && 0 === $promocode->getUsedCount()) || $isSuperAdmin || null === $promocode->getId();
        }

        $datetimePickerOptions =
            [
                'format' => 'dd.MM.y',
            ];
        $formMapper
            ->with('Переводы')
                ->add('translations', MyGedmoTranslationsType::class, [
                    'label' => 'Переводы',
                    'translatable_class' => $this->getClass(),
                    'disabled' => !$allowedToEdit,
                    'fields' => [
                        'title' => [
                            'label' => 'Название',
                            'locale_options' => $localOptions,
                        ],
                    ],
                ])
            ->end()
            ->with('Общие')
                ->add('discountAmount', null, ['required' => true, 'disabled' => !$allowedToEdit, 'label' => 'Скидка (%)'])
                ->add('code', null, ['disabled' => !$allowedToEdit, 'label' => 'Код'])
                ->add('event', null, [
                    'label' => 'Событие',
                    'required' => true,
                    'disabled' => !$allowedToEdit,
                    'placeholder' => 'Choose event',
                ])
                ->add('maxUseCount', null, ['disabled' => !$allowedToEdit, 'label' => 'Максимальное количество использований', 'help' => '(0 - безлимитный)'])
                ->add(
                    'endDate',
                    'sonata_type_date_picker',
                    array_merge(
                        [
                            'disabled' => !$allowedToEdit,
                            'required' => true,
                            'label' => 'Дата окончания',
                        ],
                        $datetimePickerOptions
                    )
                )
                ->add('description', TextType::class, ['disabled' => !$allowedToEdit, 'label' => 'Описание', 'required' => false])
            ->end();
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add(
            'event',
            null,
            [],
            EntityType::class,
            ['choices' => $this->getEvents()]
        )
            ->add('createdBy', null, ['label' => 'Создал'])
        ;
    }

    /**
     * @return array
     */
    private function getEvents(): array
    {
        $eventRepository = $this->getConfigurationPool()->getContainer()->get('doctrine')->getRepository(Event::class);

        return $eventRepository->findBy([], ['id' => Criteria::DESC]);
    }

    /**
     * @return User|null
     */
    private function getCurrentUser(): ?User
    {
        $token = $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken();

        if (!$token instanceof TokenInterface) {
            return null;
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return $user;
    }
}
