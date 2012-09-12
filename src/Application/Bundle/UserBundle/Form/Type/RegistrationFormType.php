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
            ->add('email', 'email', array('label' => 'fos_user_profile_form_email', 'translation_domain' => 'FOSUserBundle'))
            ->add('fullname', null, array('label' => 'fos_user_profile_form_fullname', 'translation_domain' => 'FOSUserBundle'))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                )
            )
            ->add('company', null, array('required' => false, 'label' => 'fos_user_registration_form_company', 'translation_domain' => 'FOSUserBundle'))
            ->add('post', null, array('required' => false, 'label' => 'fos_user_profile_form_post', 'translation_domain' => 'FOSUserBundle'))
            ->add('subscribe', 'checkbox', array('required' => false, 'label' => 'fos_user_profile_form_subscribe', 'translation_domain' => 'FOSUserBundle'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'application_user_registration';
    }
}
