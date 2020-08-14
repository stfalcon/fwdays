<?php

namespace App\Admin;

use App\Entity\Option;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * OptionAdmin.
 */
class OptionAdmin extends AbstractAdmin
{
    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('key')
            ->add('value')
            ->add('type')
            ->add('typedValueString')
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('key')
            ->add('value')
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => Option::getTypes(),
                    'label' => 'Тип',
                ]
            )
        ;
    }
}
