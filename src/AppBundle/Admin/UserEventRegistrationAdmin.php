<?php

namespace App\Admin;

use App\Entity\Event;
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
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
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
            ->add('createdAt', null, ['label' => 'Дата регистрации'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('event', null, ['label' => 'Событие'])
            ->add('user.fullname', null, ['label' => 'Пользователь'])
            ->add('user', null, ['label' => 'E-mail'])
            ->add('user.phone', null, ['label' => 'Номер телефона'])
            ->add('createdAt', null, ['label' => 'Дата регистрации'])
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
}
