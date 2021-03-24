<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\TicketCost;
use App\Model\Translatable\TranslatableInterface;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * TicketBenefitAdmin.
 */
final class TicketBenefitAdmin extends AbstractTranslateAdmin
{
    use LocalsRequiredServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function prePersist($object): void
    {
        $this->preUpdate($object);
    }

    /**
     * @param TranslatableInterface $object
     */
    public function preUpdate($object): void
    {
        parent::preUpdate($object);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
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
                            'locale_options' => $this->localsRequiredService->getLocalsRequiredArray(true, 'Описание/список бонусов %lang%'),
                        ],
                        'certificateFile' => [
                            'data_class' => null,
                            'locale_options' => $this->localsRequiredService->getLocalsRequiredArray(true, 'Файл сертификата для %lang%'),
                        ],
                        'certificate' => [
                            'locale_options' => $this->localsRequiredService->getLocalsRequiredArray(true, 'Имя файла сертификата для %lang% (после сохранения)'),
                            'field_type' => null,
                            'attr' => ['readonly' => true],
                        ],
                    ],
                ]
            )
        ;
    }
}
