<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Application\Bundle\DefaultBundle\Admin\AbstractClass\AbstractTranslateAdmin;
use Application\Bundle\DefaultBundle\Form\Type\MyGedmoTranslationsType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class PromoCodeAdmin.
 */
class PromoCodeAdmin extends AbstractTranslateAdmin
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
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        $datetimePickerOptions =
            [
                'format' => 'dd.MM.y',
            ];
        $formMapper
            ->with('Переводы')
                ->add('translations', MyGedmoTranslationsType::class, [
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
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Переводы')
                ->add('title')
            ->end()
            ->with('Общие')
                ->add('discountAmount', null, ['required' => true, 'label' => 'Скидка (%)'])
                ->add('code', null, ['label' => 'Код'])
                ->add('event', null, ['label' => 'Событие'])
                ->add('maxUseCount', null, ['label' => 'Максимальное количество использований'])
                ->add('endDate')
            ->end();
    }
}
