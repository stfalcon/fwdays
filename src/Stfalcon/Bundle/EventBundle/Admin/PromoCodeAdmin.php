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
            ->addIdentifier('title', null, ['label' => 'Название'])
            ->add('discountAmount', null, ['label' => 'Скидка (%)'])
            ->add('code', null, ['label' => 'Код'])
            ->add('event', null, ['label' => 'Событие'])
            ->add('usedCount', null, ['label' => 'Количество использований'])
            ->add('endDate', null, ['label' => 'Дата окончания']);
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
            ->with('Переводы')
                ->add('translations', 'a2lix_translations_gedmo', [
                    'label' => 'Переводы',
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'title' => [
                            'label' => 'Название',
                            'locale_options' => $localOptions,
                        ],
                    ],
                ])
            ->end()
            ->with('Общие')
                ->add('discountAmount', null, ['required' => true, 'label' => 'Скидка (%)'])
                ->add('code', null, ['label' => 'Код'])
                ->add('event', null, [
                    'label' => 'Событие',
                    'required' => true,
                    'placeholder' => 'Choose event',
                ])
                ->add(
                    'endDate',
                    'sonata_type_date_picker',
                    array_merge(
                        [
                            'required' => true,
                            'label' => 'Дата окончания',
                        ],
                        $datetimePickerOptions
                    )
                )
            ->end();
    }
}
