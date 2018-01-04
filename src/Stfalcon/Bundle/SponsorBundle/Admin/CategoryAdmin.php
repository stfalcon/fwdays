<?php

namespace Stfalcon\Bundle\SponsorBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * SponsorAdmin Class.
 */
class CategoryAdmin extends Admin
{
    public function preUpdate($object)
    {
        $this->removeNullTranslate($object);
    }

    public function prePersist($object)
    {
        $this->removeNullTranslate($object);
    }

    private function removeNullTranslate($object)
    {
        foreach ($object->getTranslations() as $key => $translation) {
            if (!$translation->getContent()) {
                $object->getTranslations()->removeElement($translation);
            }
        }
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name', null, ['label' => 'Название'])
            ->add('sortOrder', null, ['label' => 'Номер сортировки'])
            ->add('isWideContainer', null, ['label' => 'Главная категория'])
            ->add('_action', 'actions', [
                'label' => 'Действия',
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
        $formMapper
            ->with('Переводы')
                ->add(
                    'translations',
                    'a2lix_translations_gedmo',
                    [
                        'translatable_class' => $this->getClass(),
                        'label' => 'Переводы',
                        'fields' => [
                            'name' => [
                                'label' => 'Название',
                                'locale_options' => $localOptions,
                            ],
                        ],
                    ]
                )
            ->end()
            ->with('Общие')
                ->add('isWideContainer', null, ['required' => false, 'label' => 'Головна категория (широкий контейнер)'])
                ->add('sortOrder', null, ['label' => 'Номер сортировки'])
            ->end();
    }
}
