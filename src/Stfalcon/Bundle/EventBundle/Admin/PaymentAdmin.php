<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Knp\Bundle\MenuBundle\MenuItem;
use Stfalcon\Bundle\EventBundle\Entity\Payment;

/**
 * Class PaymentAdmin
 */
class PaymentAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('amount')
            ->add('fwdaysAmount')
            ->add('status')
            ->add('user')
            ->add('tickets', 'string', array(
                    'route' => array(
                        'name' => 'show'
                    ),
                    'template' => 'StfalconEventBundle:Admin:list_tickets.html.twig'
                )
            )
            ->add('gate')
            ->add('createdAt')
            ->add('updatedAt');

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
                        'admin' => 'admin',
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

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('amount', 'money', array(
                    'currency' => 'UAH'
                ))
                ->add('fwdaysAmount', 'money', array(
                    'currency' => 'UAH',
                    'required' => false
                ))
                ->add('status', 'choice', array(
                    'choices'   => array(
                        'pending'   => 'pending',
                        'paid' => 'paid'
                    )
                ))
                ->add('gate', 'choice', array(
                    'choices'   => array(
                        'interkassa'   => 'interkassa',
                        'admin' => 'admin'
                    )
                ))
                ->add('user')
                ->add('tickets', null, [
                    'by_reference' => false
                ])
            ->end();
    }

    /**
     * @return array|void
     */
    public function getBatchActions()
    {
        $actions = array();
    }
}