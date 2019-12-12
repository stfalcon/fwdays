<?php

namespace App\Admin;

use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\EventBlock;
use App\Form\Type\MyGedmoTranslationsType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class EventBlockAdmin.
 */
class EventBlockAdmin extends AbstractTranslateAdmin
{
    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
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
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application.sonata.locales.required');
        $localAllFalse = $localsRequiredService->getLocalsRequiredArray(false);
        $formMapper
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => EventBlock::getTypeChoices(),
                    'label' => 'Тип',
                ]
            )
            ->add('event', 'text', ['disabled' => true, 'label' => 'событие'])
            ->add('visible', null, ['label' => 'включен'])
            ->add('position', null, ['label' => 'позиция'])
            ->add('translations', MyGedmoTranslationsType::class, [
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
