<?php

namespace Stfalcon\Bundle\NewsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('slug')
            ->add('title')
            ->add('preview')
            ->add('text')
            ->add('created_at')
        ;
    }

    public function getName()
    {
        return 'stfalcon_bundle_newsbundle_newstype';
    }
}
