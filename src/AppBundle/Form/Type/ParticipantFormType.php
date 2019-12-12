<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ParticipantFormType.
 */
class ParticipantFormType extends AbstractType
{
    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => 'Имя участника',
                'required' => true,
            ])
            ->add('email', 'email', [
                'label' => 'E-mail участника',
                'required' => true,
            ]);
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return 'application_participant';
    }
}
