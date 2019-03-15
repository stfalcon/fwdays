<?php

namespace Stfalcon\Bundle\SponsorBundle\Admin;

use A2lix\TranslationFormBundle\Util\GedmoTranslatable;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * SponsorAdmin Class.
 */
final class CategoryAdmin extends AbstractAdmin
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
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('name', null, ['label' => 'Название'])
            ->add('sortOrder', null, ['label' => 'Номер сортировки'])
            ->add('isWideContainer', null, ['label' => 'Главная категория'])
            ->add('_action', 'actions', [
                'label' => 'Действие',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
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
                ->add('isWideContainer', null, ['required' => false, 'label' => 'Главная категория (широкий контейнер)'])
                ->add('sortOrder', null, ['label' => 'Номер сортировки'])
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
