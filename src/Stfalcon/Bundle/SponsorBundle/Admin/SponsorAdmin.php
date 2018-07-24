<?php

namespace Stfalcon\Bundle\SponsorBundle\Admin;

use A2lix\TranslationFormBundle\Util\GedmoTranslatable;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * SponsorAdmin Class.
 */
class SponsorAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $this->removeNullTranslate($object);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $this->removeNullTranslate($object);
    }

    /**
     * @return array|void
     */
    public function getBatchActions()
    {
        $actions = [];
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name', null, ['label' => 'Название'])
            ->add('site', null, ['label' => 'Сайт'])
            ->add('about', null, ['label' => 'Описание'])
            ->add('onMain', null, ['label' => 'Использовать на главной'])
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
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequredArray();
        $localOptionsAllFalse = $localsRequiredService->getLocalsRequredArray(false);
        $formMapper
            ->with('Переводы')
                ->add('translations', 'a2lix_translations_gedmo', [
                        'label' => 'Переводы',
                        'translatable_class' => $this->getClass(),
                        'fields' => [
                            'name' => [
                                'label' => 'Название',
                                'locale_options' => $localOptions,
                            ],
                            'about' => [
                                'label' => 'Описание',
                                'locale_options' => $localOptionsAllFalse,
                            ],
                        ],
                ])
            ->end()
            ->with('Общие')
                ->add('slug')
                ->add('onMain', null, ['required' => false, 'label' => 'Использовать на главной'])
                ->add('site', null, ['label' => 'Сайт'])
                ->add(
                    'file',
                    'file',
                    [
                        'label' => 'Логотип',
                        'required' => false,
                        'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                        'property_path' => 'file',
                    ]
                )
                ->add('sortOrder', null, ['attr' => ['min' => 1], 'label' => 'Номер сортировки'])
            ->end()
            ->with('События')
                ->add(
                    'sponsorEvents',
                    'sonata_type_collection',
                    [
                        'label' => 'Спонсируємые события',
                        'by_reference' => false,
                    ],
                    [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ]
                )
            ->end();
    }

    /**
     * @param GedmoTranslatable $object
     */
    private function removeNullTranslate($object)
    {
        foreach ($object->getTranslations() as $key => $translation) {
            if (!$translation->getContent()) {
                $object->getTranslations()->removeElement($translation);
            }
        }
    }
}
