<?php

namespace Stfalcon\Bundle\EventBundle\Admin\AbstractClass;

use A2lix\TranslationFormBundle\Util\GedmoTranslatable;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class AbstractNewsAdmin.
 */
abstract class AbstractNewsAdmin extends Admin
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
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        $localOptionsAllFalse = $localsRequiredService->getLocalsRequiredArray(false);
        $formMapper
            ->with('Переводы')
                ->add('translations', 'a2lix_translations_gedmo', [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'title' => [
                            'label' => 'title',
                            'locale_options' => $localOptions,
                        ],
                        'text' => [
                            'label' => 'текст',
                            'locale_options' => $localOptions,
                        ],
                        'preview' => [
                            'label' => 'preview',
                            'locale_options' => $localOptions,
                        ],
                        'metaKeywords' => [
                            'label' => 'metaKeywords',
                            'locale_options' => $localOptionsAllFalse,
                        ],
                        'metaDescription' => [
                            'label' => 'metaDescription',
                            'locale_options' => $localOptionsAllFalse,
                        ],
                    ],
                ])
            ->end()
            ->with('General')
                ->add('slug')
                ->add('created_at')
            ->end()
        ;

        return $formMapper;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     *
     * @return \Sonata\AdminBundle\Datagrid\ListMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('title')
            ->add('created_at');

        return $listMapper;
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
