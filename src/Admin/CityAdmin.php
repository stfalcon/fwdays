<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * CityAdmin.
 */
class CityAdmin extends AbstractTranslateAdmin
{
    use LocalsRequiredServiceTrait;

    /**
     * {@inheritDoc}
     */
    public function preUpdate($object): void
    {
        parent::preUpdate($object);

        $object->setUrlName(\strtolower($object->getUrlName()));
    }

    /**
     * {@inheritDoc}
     */
    public function prePersist($object): void
    {
        parent::prePersist($object);

        $object->setUrlName(\strtolower($object->getUrlName()));
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, ['label' => 'Название'])
            ->add('urlName', null, ['label' => 'Название в адресной строке'])
            ->add('default', null, ['label' => 'Использовать по умолчанию'])
            ->add('active', null, ['label' => 'Активный'])
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localOptions = $this->localsRequiredService->getLocalsRequiredArray();
        $formMapper
            ->with('Переводы')
                ->add('translations', GedmoTranslationsType::class, [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'name' => [
                            'label' => 'Название',
                            'locale_options' => $localOptions,
                        ],
                    ],
                    'label' => 'Перевод',
                ])
            ->end()
            ->with('Настройки')
                ->add('urlName', null, ['label' => 'Название в адресной строке'])
                ->add('default', null, ['label' => 'Использовать по умолчанию'])
                ->add('active', null, ['label' => 'Активный'])
                ->add('contactInfo', null, ['label' => 'Дополнительная информация'])
            ->end()
        ;
    }
}
