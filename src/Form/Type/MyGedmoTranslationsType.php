<?php

namespace App\Form\Type;

use A2lix\TranslationFormBundle\Form\DataMapper\GedmoTranslationMapper;
use A2lix\TranslationFormBundle\Form\EventListener\GedmoTranslationsListener;
use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use A2lix\TranslationFormBundle\TranslationForm\GedmoTranslationForm;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * fix for symfony 3.4.
 *
 * Class MyGedmoTranslationsType
 */
class MyGedmoTranslationsType extends GedmoTranslationsType
{
    private $translationsListener;
    private $translationForm;

    /**
     * @param GedmoTranslationsListener $translationsListener
     * @param GedmoTranslationForm      $translationForm
     * @param mixed                     $locales
     * @param mixed                     $required
     */
    public function __construct(GedmoTranslationsListener $translationsListener, GedmoTranslationForm $translationForm, $locales, $required)
    {
        parent::__construct($translationsListener, $translationForm, $locales, $required);
        $this->translationsListener = $translationsListener;
        $this->translationForm = $translationForm;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Simple way is enough
        if (!$options['inherit_data']) {
            $builder->setDataMapper(new GedmoTranslationMapper());
            $builder->addEventSubscriber($this->translationsListener);
        } else {
            if (!$options['translatable_class']) {
                throw new \Exception("If you want include the default locale with translations locales, you need to fill the 'translatable_class' option");
            }

            $childrenOptions = $this->translationForm->getChildrenOptions($options['translatable_class'], $options);
            foreach ($childrenOptions as $keyLang => $lang) {
                foreach ($lang as $keyItem => $item) {
                    foreach ($item as $field => $value) {
                        if (\in_array($field, ['max_length', 'pattern'], true)) {
                            unset($item[$field]);
                            $item['attr'][$field] = $value;
                            $childrenOptions[$keyLang][$keyItem] = $item;
                        }
                    }
                }
            }
            $defaultLocale = (array) $this->translationForm->getGedmoTranslatableListener()->getDefaultLocale();

            $builder->add('defaultLocale', MyGedmoTranslationsLocalesType::class, [
                'locales' => $defaultLocale,
                'fields_options' => $childrenOptions,
                'inherit_data' => true,
            ]);

            $builder->add($builder->getName(), MyGedmoTranslationsLocalesType::class, [
                'locales' => array_diff($options['locales'], $defaultLocale),
                'fields_options' => $childrenOptions,
                'inherit_data' => false,
                'translation_class' => $this->translationForm->getTranslationClass($options['translatable_class']),
            ]);
        }
    }
}
