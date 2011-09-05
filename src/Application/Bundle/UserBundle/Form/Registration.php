<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Bundle\UserBundle\Form;

use FOS\UserBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\FormBuilder;

class Registration extends RegistrationFormType
{
    
    public function buildForm(FormBuilder $builder, array $options)
    {
//        echo 111;
//        exit;
        $builder
//            ->add('username')
            ->add('email', 'email')
            ->add('plainPassword', 'repeated', array('type' => 'password'));
    }
    
    public function getName()
    {
        return 'application_user_registration';
    }    
    
}