<?php

namespace Application\Bundle\DefaultBundle\Form\Type;

use FOS\UserBundle\Form\Type\ProfileFormType as FosProfileFormType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * ProfileFormType.
 */
class ProfileFormType extends FosProfileFormType
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'fos_user_profile_form_email',
                'translation_domain' => 'FOSUserBundle',
            ])
            ->add('name', TextType::class, [
                'label' => 'fos_user_profile_form_name',
                'translation_domain' => 'FOSUserBundle',
            ])
            ->add('surname', TextType::class, [
                'label' => 'fos_user_profile_form_surname',
                'translation_domain' => 'FOSUserBundle',
            ])
            ->add('phone', TelType::class, [
                'required' => false,
                'label' => 'fos_user_profile_form_phone',
                'translation_domain' => 'FOSUserBundle',
            ])
            ->add('country', TextType::class, [
                'required' => false,
                'label' => 'fos_user_profile_form_country',
                'translation_domain' => 'FOSUserBundle',
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'label' => 'fos_user_profile_form_city',
                'translation_domain' => 'FOSUserBundle',
            ])
            ->add('company', TextType::class, [
                'required' => false,
                'label' => 'fos_user_profile_form_company',
                'translation_domain' => 'FOSUserBundle',
            ])
            ->add('post', TextType::class, [
                'required' => false,
                'label' => 'fos_user_profile_form_post',
                'translation_domain' => 'FOSUserBundle',
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
                'label' => 'fos_user_profile_form_subscribe',
                'translation_domain' => 'FOSUserBundle',
            ]);
    }
}
