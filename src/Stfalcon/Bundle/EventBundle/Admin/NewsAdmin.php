<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class NewsAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('event')
            ->addIdentifier('slug')
            ->add('title')
            ->add('created_at')
        ;
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('translations', 'a2lix_translations_gedmo', [
                    'translatable_class' => 'Stfalcon\Bundle\EventBundle\Entity\News',
                    'fields' => [
                        'title'=> [
                            'label' => 'title',
                            'locale_options' => [
                                'uk' => ['required' => true],
                                'ru' => ['required' => false],
                                'en' => ['required' => false],
                            ]
                        ],
                        'preview'=> [
                            'label' => 'preview',
                            'locale_options' => [
                                'uk' => ['required' => true],
                                'ru' => ['required' => false],
                                'en' => ['required' => false],
                            ]
                        ],
                        'text'=> [
                            'label' => 'text',
                            'locale_options' => [
                                'uk' => ['required' => true],
                                'ru' => ['required' => false],
                                'en' => ['required' => false],
                            ]
                        ],
                    ]
                ])
                ->add('slug')
                ->add('created_at')
            ->end()
            ->with('General')
                ->add('event', 'entity',  array(
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                ))
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event');
    }
}