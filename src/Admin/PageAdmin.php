<?php

namespace App\Admin;

use App\Admin\AbstractClass\AbstractPageAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class PageAdmin.
 */
final class PageAdmin extends AbstractPageAdmin
{
    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        parent::configureListFields($listMapper);
        $listMapper->add('showInFooter', null, ['label' => 'Показывать в футере']);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        parent::configureFormFields($formMapper);
        $formMapper
            ->with('Общие')
                ->add('showInFooter', null, ['label' => 'Показывать в футере'])
            ->end();
    }
}
