<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Application\Bundle\DefaultBundle\Admin\AbstractClass\AbstractPageAdmin;

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
                    'class' => 'Application\Bundle\DefaultBundle\Entity\Event',
                    'label' => 'Событие',
                ])
                ->add('speakers', 'entity', [
                    'class' => 'Application\Bundle\DefaultBundle\Entity\Speaker',
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
