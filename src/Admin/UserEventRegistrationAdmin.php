<?php

namespace App\Admin;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * UserEventRegistrationAdmin.
 */
final class UserEventRegistrationAdmin extends AbstractAdmin
{
    private $eventRepository;

    /**
     * @param string          $code
     * @param class-string    $class
     * @param string          $baseControllerName
     * @param EventRepository $eventRepository
     */
    public function __construct($code, $class, $baseControllerName, EventRepository $eventRepository)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->eventRepository = $eventRepository;
    }

    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'id',
            'event',
            'user.fullname',
            'user.email',
            'user.phone',
            'used',
            'createdAt',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccess($action, $object = null): bool
    {
        $result = parent::hasAccess($action, $object);
        if (\in_array($action, ['delete', 'create'], true)) {
            return false;
        }

        return $result;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add(
                'event',
                null,
                ['label' => 'Событие'],
                EntityType::class,
                ['choices' => $this->getEvents()]
            )
            ->add('user.fullname', null, ['label' => 'Пользователь'])
            ->add('user.email', null, ['label' => 'E-mail'])
            ->add('user.phone', null, ['label' => 'Номер телефона'])
            ->add('used', null, ['label' => 'Использован'])
            ->add('createdAt', null, ['label' => 'Дата регистрации'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->add('event', null, ['label' => 'Событие'])
            ->add('user.fullname', null, ['label' => 'Пользователь'])
            ->add('user', null, ['label' => 'E-mail'])
            ->add('user.phone', null, ['label' => 'Номер телефона'])
            ->add('used', null, ['label' => 'Использован'])
            ->add('createdAt', null, ['label' => 'Дата регистрации'])
        ;
    }

    /**
     * @return array
     */
    private function getEvents(): array
    {
        return $this->eventRepository->findBy([], ['id' => Criteria::DESC]);
    }
}
