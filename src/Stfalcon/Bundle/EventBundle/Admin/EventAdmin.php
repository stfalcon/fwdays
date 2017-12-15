<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class EventAdmin.
 */
class EventAdmin extends Admin
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
            ->addIdentifier('id')
            ->addIdentifier('slug')
            ->add('name')
            ->add('active')
            ->add('wantsToVisitCount')
            ->add('useDiscounts')
            ->add('receivePayments')
            ->add('cost')
            ->add(
                'images',
                'string',
                array(
                    'template' => 'StfalconEventBundle:Admin:images_thumb_layout.html.twig',
                )
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequredArray();
        $localAllFalse = $localsRequiredService->getLocalsRequredArray(false);
        $datetimePickerOptions =
            [
                'dp_use_seconds' => false,
                'dp_language' => 'ru',
                'format' => 'dd.MM.y, HH:mm',
                'dp_minute_stepping' => 10,
            ];

        $formMapper
            ->with('Переклади')
                ->add('translations', 'a2lix_translations_gedmo', [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'name' => [
                            'label' => 'Назва',
                            'locale_options' => $localOptions,
                        ],
                        'city' => [
                            'label' => 'Місто (використувується для пошуку координат на мапі)',
                            'locale_options' => $localOptions,
                        ],
                        'place' => [
                            'label' => 'Місце (використувується для пошуку координат на мапі)',
                            'locale_options' => $localOptions,
                        ],
                        'description' => [
                            'label' => 'Корткий опис',
                            'locale_options' => $localOptions,
                        ],
                        'about' => [
                            'label' => 'Опис',
                            'locale_options' => $localOptions,
                        ],
                        'approximateDate' => [
                            'label' => 'Приблизна дата',
                            'locale_options' => $localAllFalse,
                        ],
                        'metaDescription' => [
                            'label' => 'metaDescription',
                            'locale_options' => $localAllFalse,
                        ],
                    ],
                    'label' => 'Перевод',
                ])
            ->end()
            ->with('Дата початку та закінчення', ['class' => 'col-md-4'])
                ->add('useApproximateDate', null, ['required' => false, 'label' => 'Використовувати приблизну дату'])
                ->add(
                    'date',
                    'sonata_type_datetime_picker',
                    array_merge(
                        [
                            'required' => false,
                            'label' => 'Дата початку',
                        ],
                        $datetimePickerOptions
                    )
                )
                ->add(
                    'dateEnd',
                    'sonata_type_datetime_picker',
                    array_merge(
                        [
                            'required' => false,
                            'label' => 'Дата закінчення',
                        ],
                        $datetimePickerOptions
                    )
                )
            ->end()
            ->with('Налаштування', ['class' => 'col-md-4'])
                ->add('slug')
                ->add('cost', null, ['required' => true, 'label' => 'Вартість квитка'])
                ->add('active', null, ['required' => false])
                ->add('receivePayments', null, ['required' => false, 'label' => 'Приймати платежі'])
                ->add('useDiscounts', null, ['required' => false, 'label' => 'Можлива знижка'])
            ->end()
            ->with('Зображення та колір', ['class' => 'col-md-4'])
                ->add('backgroundColor', 'sonata_type_color_selector', ['required' => false, 'label' => 'Колір фону'])
                ->add(
                    'logoFile',
                    'file',
                    [
                        'label' => 'Logo. Ширина зображення повина дорівнювати висоті.',
                        'required' => is_null($subject->getLogo()),
                    ]
                )
                ->add(
                    'pdfBackgroundFile',
                    'file',
                    [
                        'label' => 'Background image',
                        'required' => false,
                    ]
                )
                ->add(
                    'emailBackgroundFile',
                    'file',
                    [
                        'label' => 'Email background',
                        'required' => false,
                    ]
                )
            ->end();
    }

    /**
     * @return array|void
     */
    public function getBatchActions()
    {
        $actions = array();
    }
}
