<?php

namespace App\Admin;

use App\Admin\AbstractClass\AbstractPageAdmin;
use App\Entity\Event;
use App\Entity\Speaker;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class ReviewAdmin.
 */
final class ReviewAdmin extends AbstractPageAdmin
{
    /** @var EventRepository */
    private $eventRepository;

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
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        parent::configureListFields($listMapper);
        $listMapper
            ->add('event', null, ['label' => 'Событие'])
            ->add('speakers', null, ['label' => 'Докладчики']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        parent::configureFormFields($formMapper);
        $formMapper
            ->with('Общие')
                ->add('event', EntityType::class, [
                    'class' => Event::class,
                    'label' => 'Событие',
                ])
                ->add('speakers', EntityType::class, [
                    'class' => Speaker::class,
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'Докладчики',
                ])
                ->add('keywords', null, [
                    'label' => 'Ключевые слова',
                ])
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add(
                'event',
                null,
                [],
                EntityType::class,
                ['choices' => $this->getEvents()]
            );
    }

    /**
     * @return Event[]
     */
    private function getEvents(): array
    {
        return $this->eventRepository->findBy([], ['id' => Criteria::DESC]);
    }
}
