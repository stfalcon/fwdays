<?php

namespace Application\Bundle\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * RegistrationFormType Class
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
            ->add('email', 'email', array(
                'label'              => 'fos_user_profile_form_email',
                'translation_domain' => 'FOSUserBundle'
            ))
            ->add('fullname', null, array(
                'required'           => true,
                'label'              => 'fos_user_profile_form_fullname',
                'translation_domain' => 'FOSUserBundle'
            ))
            ->add('country', null, array(
                'label'              => 'fos_user_profile_form_country',
                'translation_domain' => 'FOSUserBundle'
            ))
            ->add('city', null, array(
                'label'              => 'fos_user_profile_form_city',
                'translation_domain' => 'FOSUserBundle'
            ))
            ->add('company', null, array(
                'required'           => false,
                'label'              => 'fos_user_registration_form_company',
                'translation_domain' => 'FOSUserBundle'
            ))
            ->add('post', null, array(
                'required'           => false,
                'label'              => 'fos_user_profile_form_post',
                'translation_domain' => 'FOSUserBundle'
            ))
            ->add('plainPassword', 'password', array(
                'required'           => true,
                'label'              => 'fos_user_profile_form_password',
                'translation_domain' => 'FOSUserBundle'
            ))
            ->add('subscribe', 'checkbox', array(
                'required'           => false,
                'label'              => 'fos_user_profile_form_subscribe',
                'translation_domain' => 'FOSUserBundle'
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'application_user_registration';
    }
}
