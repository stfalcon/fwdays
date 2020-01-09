<?php

namespace App\Admin;

use App\Admin\AbstractClass\AbstractPageAdmin;
use App\Entity\Event;
use App\Entity\Speaker;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class ReviewAdmin.
 */
final class ReviewAdmin extends AbstractPageAdmin
{
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
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper = parent::configureListFields($listMapper);
        $listMapper
            ->add('event', null, ['label' => 'Событие'])
            ->add('speakers', null, ['label' => 'Докладчики']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper = parent::configureFormFields($formMapper);
        $formMapper
            ->with('Общие')
                ->add('event', 'entity', [
                    'class' => Event::class,
                    'label' => 'Событие',
                ])
                ->add('speakers', 'entity', [
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
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event', null, ['label' => 'Событие']);
    }
}