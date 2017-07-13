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
            ->addIdentifier('slug')
            ->add('name')
            ->add('active')
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

        $formMapper
            ->with('General')
            ->add('translations', 'a2lix_translations_gedmo', [
                'translatable_class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                'fields' => [
                    'name'=> [
                        'label' => 'name',
                        'locale_options' => [
                            'uk' => ['required' => true],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                    'city'=> [
                        'label' => 'city',
                        'locale_options' => [
                            'uk' => ['required' => true],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                    'place'=> [
                        'label' => 'place',
                        'locale_options' => [
                            'uk' => ['required' => true],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                    'description'=> [
                        'label' => 'description',
                        'locale_options' => [
                            'uk' => ['required' => true],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                    'about'=> [
                        'label' => 'about',
                        'locale_options' => [
                            'uk' => ['required' => true],
                            'ru' => ['required' => false],
                            'en' => ['required' => false],
                        ]
                    ],
                ],
                'label' => 'Перевод',
            ])
            ->add('slug')
            ->add('date')
            ->add('active', null, ['required' => false])
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
