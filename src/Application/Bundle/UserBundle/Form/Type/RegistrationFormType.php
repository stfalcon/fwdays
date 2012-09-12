<?php

namespace Application\Bundle\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormType extends BaseRegistrationFormType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email')
            ->add('fullname')
            ->add('plainPassword', 'repeated', array('type' => 'password'))
            ->add('company', null, array('required' => false))
            ->add('post', null, array('required' => false))
            ->add('city', null, array('required' => false))
            ->add('country', null, array('required' => false))
            ->add('subscribe', 'checkbox', array('required' => false));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'application_user_registration';
    }
}
