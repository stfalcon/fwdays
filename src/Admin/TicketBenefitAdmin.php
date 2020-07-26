<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Entity\TicketCost;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * TicketBenefitAdmin.
 */
final class TicketBenefitAdmin extends AbstractAdmin
{
    use LocalsRequiredServiceTrait;

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $localAllTrue = $this->localsRequiredService->getLocalsRequiredArray(true);

        $formMapper
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => TicketCost::getTypes(),
                    'label' => 'Тип',
                ]
            )
            ->add(
                'translations',
                GedmoTranslationsType::class,
                [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'benefits' => [
                            'label' => 'Описание/список бонусов',
                            'locale_options' => $localAllTrue,
                        ],
                    ],
                ]
            )
        ;
    }
}
