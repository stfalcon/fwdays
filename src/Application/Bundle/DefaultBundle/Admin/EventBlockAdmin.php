<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Application\Bundle\DefaultBundle\Admin\AbstractClass\AbstractTranslateAdmin;
use Application\Bundle\DefaultBundle\Entity\EventBlock;

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
                'choice',
                [
                    'choices' => EventBlock::getTypeChoices(),
                    'label' => 'Тип',
                ]
            )
            ->add('event', 'text', ['disabled' => true, 'label' => 'событие'])
            ->add('visible', null, ['label' => 'включен'])
            ->add('position', null, ['label' => 'позиция'])
            ->add('translations', 'a2lix_translations_gedmo', [
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
