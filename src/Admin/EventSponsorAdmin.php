<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * SponsorAdmin Class.
 */
final class EventSponsorAdmin extends AbstractAdmin
{
    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('event', null, ['label' => 'Событие'])
            ->add('category', null, ['label' => 'Категория']);
    }
}
