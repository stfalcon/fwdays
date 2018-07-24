<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Stfalcon\Bundle\EventBundle\Admin\AbstractClass\AbstractPageAdmin;

/**
 * Class PageAdmin.
 */
class PageAdmin extends AbstractPageAdmin
{
    /**
     * @param ListMapper $listMapper
     *
     * @return ListMapper|void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper = parent::configureListFields($listMapper);
        $listMapper->add('showInFooter', null, ['label' => 'Показывать в футере']);
    }

    /**
     * @param FormMapper $formMapper
     *
     * @return FormMapper|void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper = parent::configureFormFields($formMapper);
        $formMapper
            ->with('Общие')
                ->add('showInFooter', null, ['label' => 'Показывать в футере'])
            ->end();
    }
}
