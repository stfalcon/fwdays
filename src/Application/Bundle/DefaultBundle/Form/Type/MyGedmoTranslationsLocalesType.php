<?php

namespace Application\Bundle\DefaultBundle\Form\Type;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsLocalesType;
use A2lix\TranslationFormBundle\Form\Type\TranslationsFieldsType;
use Symfony\Component\Form\FormBuilderInterface;
use A2lix\TranslationFormBundle\Form\DataMapper\GedmoTranslationMapper;

/**
 * fix for symfony 3.4.
 */
class MyGedmoTranslationsLocalesType extends GedmoTranslationsLocalesType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isDefaultTranslation = ('defaultLocale' === $builder->getName());

        // Custom mapper for translations
        if (!$isDefaultTranslation) {
            $builder->setDataMapper(new GedmoTranslationMapper());
        }

        foreach ($options['locales'] as $locale) {
            if (isset($options['fields_options'][$locale])) {
                $builder->add($locale, TranslationsFieldsType::class, array(
                    'fields' => $options['fields_options'][$locale],
                    'translation_class' => $options['translation_class'],
                    'inherit_data' => $isDefaultTranslation,
                ));
            }
        }
    }
}
