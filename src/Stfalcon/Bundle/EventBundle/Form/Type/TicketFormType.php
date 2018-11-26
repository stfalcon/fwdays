<?php

namespace Stfalcon\Bundle\EventBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TicketFormType.
 */
class TicketFormType extends AbstractType
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
            ->add(
                'participants',
                'collection',
                [
                    'type' => new ParticipantFormType(),
                    'allow_add' => true,
                ]
            );
    }

    /**
     * {@inheritdoc}
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'intention' => 'event_ticket_intention',
            ]
        );
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return 'stfalcon_event_ticket';
    }
}
