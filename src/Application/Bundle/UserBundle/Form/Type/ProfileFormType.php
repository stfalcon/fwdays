<?php

namespace Application\Bundle\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfileFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileFormType extends BaseProfileFormType
{
    /**
     * @var string
     */
    private $class;

    /**
     * Constructor
     *
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email')
            ->add('fullname')
            ->add('company', null, array('required' => false))
            ->add('post', null, array('required' => false))
            ->add('city', null, array('required' => false))
            ->add('country', null, array('required' => false))
            ->add('subscribe', 'checkbox', array('required' => false));
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'intention'  => 'profile',
        ));
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'application_user_profile';
    }

}
