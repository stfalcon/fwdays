<?php
namespace Stfalcon\Bundle\PaymentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

class PaymentAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('amount')
            ->add('status')
            ->add('user')
//            ->add('createdAt')
        ;

        return $listMapper;
    }

    public function getBatchActions()
    {
        $actions = array();
    }

}