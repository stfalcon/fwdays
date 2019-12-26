<?php

namespace Application\Bundle\DefaultBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * RegistrationFormType Class.
 */
class RegistrationFormType extends BaseRegistrationFormType
{
    private $locales;

    /**
     * @param string $class
     * @param array  $locales
     */
    public function __construct(string $class, array $locales)
    {
        parent::__construct($class);
        $this->locales = $locales;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'fos_user_profile_form_email',
            ])
            ->add('surname', TextType::class, [
                'required' => true,
                'label' => 'fos_user_profile_form_surname',
            ])
            ->add('fullname', TextType::class, [
                'label' => 'fos_user_profile_form_fullname',
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'fos_user_profile_form_name',
            ])
            ->add('country', TextType::class, [
                'label' => 'fos_user_profile_form_country',
            ])
            ->add('phone', TelType::class, [
                'label' => 'fos_user_profile_form_phone',
            ])
            ->add('city', TextType::class, [
                'label' => 'fos_user_profile_form_city',
            ])
            ->add('company', TextType::class, [
                'required' => false,
                'label' => 'fos_user_registration_form_company',
            ])
            ->add('post', TextType::class, [
                'required' => false,
                'label' => 'fos_user_profile_form_post',
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => true,
                'label' => 'fos_user_profile_form_password',
            ])
            ->add(
                'emailLanguage',
                ChoiceType::class,
                [
                    'multiple' => false,
                    'choices' => $this->locales,
                ]
            )
            ->add('subscribe', CheckboxType::class, [
                'required' => false,
                'data' => true,
                'label' => 'fos_user_profile_form_subscribe',
            ])
            ->add('facebookID')
            ->add('googleID')
        ;
    }
}
