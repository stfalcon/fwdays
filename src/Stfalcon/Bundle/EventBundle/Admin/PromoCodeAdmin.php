<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class PromoCodeAdmin.
 */
class PromoCodeAdmin extends Admin
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
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('discountAmount')
            ->add('code')
            ->add('event')
            ->add('usedCount')
            ->add('endDate');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequredArray();
        $datetimePickerOptions =
            [
                'format' => 'dd.MM.y',
            ];
        $formMapper
            ->with('Переклади')
                ->add('translations', 'a2lix_translations_gedmo', [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'title' => [
                            'label' => 'title',
                            'locale_options' => $localOptions,
                        ],
                    ],
                ])
            ->end()
            ->with('Загальні')
                ->add('discountAmount', null, ['required' => true, 'label' => 'Знижка (%)'])
                ->add('code')
                ->add('event', null, [
                    'required' => true,
                    'placeholder' => 'Choose event',
                ])
                ->add(
                    'endDate',
                    'sonata_type_date_picker',
                    array_merge(
                        [
                            'required' => true,
                            'label' => 'Дата закінчення дії',
                        ],
                        $datetimePickerOptions
                    )
                )
            ->end();
    }
}
