<?php

namespace Stfalcon\Bundle\EventBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TicketFormType
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
            ->add('participants', 'collection', array(
                    'type' => new ParticipantFormType(),
                    'allow_add'    => true,
                ));
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'stfalcon_event_ticket';
    }
}
