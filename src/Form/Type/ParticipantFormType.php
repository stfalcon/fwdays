<?php

namespace App\Form\Type;

use App\Entity\User;
use FOS\UserBundle\Form\Type\ProfileFormType as FosProfileFormType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * ParticipantFormType.
 */
class ParticipantFormType extends FosProfileFormType
{
    /**
     * ParticipantFormType constructor.
     */
    public function __construct()
    {
        parent::__construct(User::class);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('name', TextType::class)
            ->add('surname', TextType::class)
            ->add('promocode', TextType::class, ['mapped' => false])
        ;
    }
}
