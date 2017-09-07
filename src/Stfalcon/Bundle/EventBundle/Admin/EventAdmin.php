<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class EventAdmin
 */
class EventAdmin extends Admin
{

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
                    'template' => 'StfalconEventBundle:Admin:images_thumb_layout.html.twig'
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

        $formMapper
            ->with('General')
            ->add('translations', 'a2lix_translations_gedmo', [
                'translatable_class' => $this->getClass(),
                'fields' => [
                    'name'=> [
                        'label' => 'name',
                        'locale_options' => $localOptions
                    ],
                    'city'=> [
                        'label' => 'city',
                        'locale_options' => $localOptions
                    ],
                    'place'=> [
                        'label' => 'place',
                        'locale_options' => $localOptions
                    ],
                    'description'=> [
                        'label' => 'description',
                        'locale_options' => $localOptions
                    ],
                    'about'=> [
                        'label' => 'about',
                        'locale_options' => $localOptions
                    ],
                ],
                'label' => 'Перевод',
            ])
            ->add('slug')
            ->add('date')
            ->add('dateEnd')
            ->add('active', null, ['required' => false])
            ->add('backgroundColor', 'sonata_type_color_selector')
            ->add('receivePayments', null, ['required' => false])
            ->add('useDiscounts', null, ['required' => false])
            ->add('cost', null, ['required' => true])
            ->end()
            ->with('Images')
            ->add(
                'logoFile',
                'file',
                array(
                    'label' => 'Logo',
                    'required' => is_null($subject->getLogo())
                )
            )
            ->add(
                'pdfBackgroundFile',
                'file',
                array(
                    'label' => 'Background image',
                    'required' => false,
                )
            )
            ->add(
                'emailBackgroundFile',
                'file',
                array(
                    'label' => 'Email background',
                    'required' => false,
                )
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
