<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

class SpeakerAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
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
                            'about'=> [
                                'label' => 'about',
                                'locale_options' => $localOptions
                            ],
                        ]
                ])
                ->add('slug')
                ->add('email')
                ->add('company')
                // @todo rm array options https://github.com/dustin10/VichUploaderBundle/issues/27 and https://github.com/symfony/symfony/pull/5028
                ->add('file', 'file', array(
                        'required' => false,
                        'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                        'property_path' => 'file'
                ))
                ->add('events', 'entity',  array(
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'multiple' => true, 'expanded' => true,
                ))
            ->end()
        ;
    }
}