<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\EventBlock;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class EventBlockAdmin.
 */
class EventBlockAdmin extends AbstractTranslateAdmin
{
    use LocalsRequiredServiceTrait;

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->addIdentifier('type')
            ->add('event')
            ->add('visible')
        ;
    }

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
                ]
            )
            ->add('event', TextType::class, ['disabled' => true, 'label' => 'событие'])
            ->add('visible', null, ['label' => 'включен'])
            ->add('position', null, ['label' => 'позиция'])
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