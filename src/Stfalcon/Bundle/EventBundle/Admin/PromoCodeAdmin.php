<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use A2lix\TranslationFormBundle\Util\GedmoTranslatable;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class PromoCodeAdmin.
 */
class PromoCodeAdmin extends Admin
{
    /**
     * @var array
     */
    protected $datagridValues =
        [
            '_page' => 1,
            '_sort_order' => 'DESC',
            '_sort_by' => 'id',
        ];

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
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, ['label' => 'Название'])
            ->add('discountAmount', null, ['label' => 'Скидка (%)'])
            ->add('code', null, ['label' => 'Код'])
            ->add('event', null, ['label' => 'Событие'])
            ->add('used', null, ['label' => 'Использований'])
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
                ->add('maxUseCount', null, ['label' => 'Максимальное количество использований', 'help' => '(0 - безлимитный)'])
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

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('event');
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
