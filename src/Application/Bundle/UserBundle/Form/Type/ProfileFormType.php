<?php

namespace Application\Bundle\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfileFormType;
use Symfony\Component\Form\FormBuilder;

class ProfileFormType extends BaseProfileFormType
{
    
    private $class;
    
    public function __construct($class)
    {
        $this->class = $class;
    }
    
    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilder $builder
     * @param array $options
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('email', 'email')
            ->add('fullname')
            ->add('company', null, array('required' => false))
            ->add('post', null, array('required' => false))
            ->add('subscribe', 'checkbox', array('required' => false));
        ;
    }
    
    public function getDefaultOptions(array $options)
    {
        return array('data_class' => $this->class);
    }    
    
    public function getName()
    {
        return 'application_user_profile';
    }    
    
}