<?php

namespace Application\Bundle\DefaultBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PromoCodeFormType.
 */
class PromoCodeFormType extends AbstractType
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
            ->add('code', 'text', array(
                'label' => 'У меня есть промокод',
                'required' => true,
            ));
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return 'application_promo_code';
    }
}
