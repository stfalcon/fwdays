<?php

namespace App\Admin;

use App\Entity\Event;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class TicketCostAdmin.
 */
final class TicketCostAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagrid
     */
    protected function configureDatagridFilters(DatagridMapper $datagrid)
    {
        $datagrid
            ->add('name')
            ->add('event', null, [], EntityType::class, ['choices' => $this->getEvents()])
            ->add('amount')
            ->add('count')
            ->add('soldCount')
            ->add('enabled')
            ->add('unlimited')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->addIdentifier('name')
            ->add('event')
            ->add('amount')
            ->add('count')
            ->add('soldCount')
            ->add('enabled')
            ->add('unlimited');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', null, ['label' => 'название'])
            ->add('event', 'text', ['disabled' => true, 'label' => 'событие'])
            ->add('amount', null, ['label' => 'цена'])
            ->add('altAmount', null, ['label' => 'цена в валюте'])
            ->add('count', null, ['label' => 'количество'])
            ->add('soldCount', null, ['disabled' => true, 'label' => 'продано'])
            ->add('enabled', null, ['label' => 'активный'])
            ->add('unlimited', null, ['label' => 'безлимитный'])
            ->add('ticketsRunOut', null, ['label' => 'заканчиваются'])
            ->add('comingSoon', null, ['label' => 'вскоре'])
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
