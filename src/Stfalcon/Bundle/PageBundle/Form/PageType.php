<?php

namespace Stfalcon\Bundle\PageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PageType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('slug')
            ->add('title')
            ->add('text')
        ;
    }

    public function getName()
    {
        return 'stfalcon_bundle_pagebundle_pagetype';
    }
}
