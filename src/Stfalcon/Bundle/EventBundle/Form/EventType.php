<?php

namespace Stfalcon\Bundle\EventBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EventType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('slug')
            ->add('name')
            ->add('logo')
            ->add('title')
            ->add('description')
        ;
    }

    public function getName()
    {
        return 'stfalcon_bundle_eventbundle_eventtype';
    }
}
