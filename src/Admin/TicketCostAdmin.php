<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\DateTimePickerType;

/**
 * TicketCostAdmin.
 */
final class TicketCostAdmin extends AbstractAdmin
{
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $datetimePickerOptions =
            [
                'dp_use_seconds' => false,
                'dp_language' => 'ru',
                'format' => 'dd.MM.y, HH:mm',
                'dp_minute_stepping' => 10,
            ];

        $formMapper
            ->add('name', null, ['label' => 'название'])
            ->add('amount', null, ['label' => 'цена'])
            ->add('altAmount', null, ['label' => 'цена в валюте'])
            ->add('count', null, ['label' => 'количество'])
            ->add('soldCount', null, ['disabled' => true, 'label' => 'продано'])
            ->add('sortOrder', null, ['label' => 'Сортировка'])
            ->add(
                'endDate',
                DateTimePickerType::class,
                \array_merge(
                    [
                        'required' => false,
                        'label' => 'Дата начала',
                    ],
                    $datetimePickerOptions
                )
            )
            ->add('enabled', null, ['label' => 'активный'])
            ->add('unlimited', null, ['label' => 'безлимитный'])
            ->add('ticketsRunOut', null, ['label' => 'заканчиваются'])
            ->add('comingSoon', null, ['label' => 'вскоре'])
            ->add('visible', null, ['label' => 'показывать'])
        ;
    }
}
