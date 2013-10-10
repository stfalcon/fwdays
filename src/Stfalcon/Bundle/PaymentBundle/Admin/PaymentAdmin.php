<?php
namespace Stfalcon\Bundle\PaymentBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * Class PaymentAdmin
 *
 * @package Stfalcon\Bundle\PaymentBundle\Admin
 */
class PaymentAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('amount')
            ->add('status')
            ->add('user')
            ->add('ticketNumber');

        return $listMapper;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'gate',
                'doctrine_orm_choice',
                array(),
                'choice',
                array(
                    'choices' => array(
                        'interkassa' => 'interkassa',
                    ),
                    'required' => false,
                )
            )
            ->add(
                'events',
                'doctrine_orm_callback',
                array(
                    'label' => 'Events',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        $eventsId = array();
                        /** @var $event \Stfalcon\Bundle\EventBundle\Entity\Event */
                        foreach ($value['value'] as $event) {
                            $eventsId[] = $event->getId();
                        }

                        if (empty($eventsId)) {
                            return;
                        }

                        /** @var $queryBuilder QueryBuilder */
                        $queryBuilder->join(sprintf('%s.tickets', $alias), 't');
                        $queryBuilder->join('t.event', 'e');
                        $queryBuilder->andWhere($queryBuilder->expr()->in('e.id', $eventsId));

                        return true;
                    },
                    'field_type' => 'entity',
                    'field_options' => array(
                        'class' => 'StfalconEventBundle:Event',
                        'property' => 'name',
                        'multiple' => true,
                        'required' => false
                    )
                )
            );
    }

    public function getBatchActions()
    {
        $actions = array();
    }

}