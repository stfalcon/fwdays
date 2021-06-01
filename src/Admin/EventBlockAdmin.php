<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\EventBlock;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class EventBlockAdmin.
 */
class EventBlockAdmin extends AbstractTranslateAdmin
{
    use LocalsRequiredServiceTrait;

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $localAllFalse = $this->localsRequiredService->getLocalsRequiredArray(false);
        $formMapper
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => EventBlock::getTypeChoices(),
                    'label' => 'Тип',
                    'attr' => ['style' => 'max-width: 160px'],
                ]
            )
            ->add(
                'visibility',
                ChoiceType::class,
                [
                    'choices' => EventBlock::getVisibilityChoices(),
                    'label' => 'Видимость блока',
                    'attr' => ['style' => 'max-width: 200px'],
                ]
            )
            ->add('visible', null, ['label' => 'Включен'])
            ->add('position', null, ['label' => 'Позиция', 'attr' => ['style' => 'max-width: 60px']])
            ->add('translations', GedmoTranslationsType::class, [
                'translatable_class' => $this->getClass(),
                'fields' => [
                    'text' => [
                        'label' => 'html текст',
                        'locale_options' => $localAllFalse,
                    ],
                ],
                'label' => 'Перевод',
            ])
        ;
    }
}
