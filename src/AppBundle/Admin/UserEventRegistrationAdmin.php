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
     * Allows you to customize batch actions.
     *
     * @param array $actions List of actions
     *
     * @return array
     */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
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
            ->add('user.fullname', null, ['label' => 'Имя'])
            ->add('user.email', null, ['label' => 'Почта'])
            ->add('user.phone', null, ['label' => 'Номер телефона'])
            ->add('createdAt')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('event')
            ->add('user.fullname', null, ['label' => 'Имя'])
            ->add('user', null, ['label' => 'Почта'])
            ->add('user.phone', null, ['label' => 'Номер телефона'])
            ->add('createdAt', null, ['label' => 'Дата создания'])
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
