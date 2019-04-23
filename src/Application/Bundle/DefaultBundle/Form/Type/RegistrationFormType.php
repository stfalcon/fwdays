<?php

namespace Application\Bundle\DefaultBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * RegistrationFormType Class.
 */
class RegistrationFormType extends BaseRegistrationFormType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', [
                'required' => true,
                'label' => 'fos_user_profile_form_email',
            ])
            ->add('surname', null, [
                'required' => true,
                'label' => 'fos_user_profile_form_surname',
            ])
            ->add('fullname', null, [
                'label' => 'fos_user_profile_form_fullname',
            ])
            ->add('name', null, [
                'required' => true,
                'label' => 'fos_user_profile_form_name',
            ])
            ->add('country', null, [
                'label' => 'fos_user_profile_form_country',
            ])
            ->add('phone', null, [
                'label' => 'fos_user_profile_form_phone',
            ])
            ->add('city', null, [
                'label' => 'fos_user_profile_form_city',
            ])
            ->add('company', null, [
                'required' => false,
                'label' => 'fos_user_registration_form_company',
            ])
            ->add('post', null, [
                'required' => false,
                'label' => 'fos_user_profile_form_post',
            ])
            ->add('plainPassword', 'password', [
                'required' => true,
                'label' => 'fos_user_profile_form_password',
            ])
            ->add('subscribe', 'checkbox', [
                'required' => false,
                'data' => true,
                'label' => 'fos_user_profile_form_subscribe',
            ])
            ->add('facebookID')
            ->add('googleID')
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'application_registration';
    }
}
