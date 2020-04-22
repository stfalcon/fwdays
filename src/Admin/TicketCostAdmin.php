<?php

namespace App\Admin;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class TicketCostAdmin.
 */
final class TicketCostAdmin extends AbstractAdmin
{
    private $eventRepository;

    /**
     * @param string          $code
     * @param string          $class
     * @param string          $baseControllerName
     * @param EventRepository $eventRepository
     */
    public function __construct($code, $class, $baseControllerName, EventRepository $eventRepository)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param DatagridMapper $datagrid
     */
    protected function configureDatagridFilters(DatagridMapper $datagrid): void
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
    protected function configureListFields(ListMapper $listMapper): void
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
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('name', null, ['label' => 'название'])
            ->add('event', TextType::class, ['disabled' => true, 'label' => 'событие'])
            ->add('amount', null, ['label' => 'цена'])
            ->add('altAmount', null, ['label' => 'цена в валюте'])
            ->add('count', null, ['label' => 'количество'])
            ->add('soldCount', null, ['disabled' => true, 'label' => 'продано'])
            ->add('enabled', null, ['label' => 'активный'])
            ->add('unlimited', null, ['label' => 'безлимитный'])
            ->add('ticketsRunOut', null, ['label' => 'заканчиваются'])
            ->add('comingSoon', null, ['label' => 'вскоре'])
            ->add('visible', null, ['label' => 'показывать'])
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
