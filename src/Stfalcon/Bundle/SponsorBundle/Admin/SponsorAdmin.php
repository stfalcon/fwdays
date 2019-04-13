<?php

namespace Stfalcon\Bundle\SponsorBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Stfalcon\Bundle\SponsorBundle\Entity\Sponsor;
use Stfalcon\Bundle\EventBundle\Admin\AbstractClass\AbstractTranslateAdmin;

/**
 * SponsorAdmin Class.
 */
class SponsorAdmin extends AbstractTranslateAdmin
{
    /**
     * @return array|void
     */
    public function getBatchActions()
    {
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, ['label' => 'Название'])
            ->add('site', null, ['label' => 'Сайт'])
            ->add('about', null, ['label' => 'Описание'])
            ->add('sortOrder', null, ['label' => 'Номер сортировки'])
            ->add('_action', 'actions', [
                'label' => 'Действие',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Sponsor $subject */
        $subject = $this->getSubject();
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptionsAllFalse = $localsRequiredService->getLocalsRequiredArray(false);
        $formMapper
            ->with('Переводы')
            ->add('translations', 'a2lix_translations_gedmo', [
                'label' => 'Переводы',
                'translatable_class' => $this->getClass(),
                'fields' => [
                    'about' => [
                        'label' => 'Описание',
                        'locale_options' => $localOptionsAllFalse,
                    ],
                ],
            ])
            ->end()
            ->with('Общие')
                ->add('name')
                ->add('site', null, ['label' => 'Сайт'])
                ->add(
                    'file',
                    'file',
                    [
                        'label' => 'Логотип',
                        'required' => false,
                        'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                        'property_path' => 'file',
                        'help' => $subject->getLogo() ? $subject->getLogo() : '',
                    ]
                )
                ->add('sortOrder', null, ['attr' => ['min' => 1], 'label' => 'Номер сортировки'])
            ->end()
            ->with('События')
                ->add(
                    'sponsorEvents',
                    'sonata_type_collection',
                    [
                        'label' => 'Спонсируемые события',
                        'by_reference' => false,
                    ],
                    [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ]
                )
            ->end();
    }
}
