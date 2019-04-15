<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Stfalcon\Bundle\EventBundle\Admin\AbstractClass\AbstractTranslateAdmin;

/**
 * Class SpeakerAdmin.
 */
class SpeakerAdmin extends AbstractTranslateAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name', null, ['label' => 'Имя'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        $formMapper
            ->with('Переводы')
                ->add('translations', 'a2lix_translations_gedmo', [
                        'translatable_class' => $this->getClass(),
                        'fields' => [
                            'name' => [
                                'label' => 'Имя',
                                'locale_options' => $localOptions,
                            ],
                            'about' => [
                                'label' => 'Описание',
                                'locale_options' => $localOptions,
                            ],
                        ],
                ])
            ->end()
            ->with('Общие')
                ->add('slug')
                ->add('email')
                ->add('company', null, ['label' => 'Место работы'])
                ->add('sortOrder', null, ['label' => 'Номер сортировки'])
                // @todo rm array options https://github.com/dustin10/VichUploaderBundle/issues/27 and https://github.com/symfony/symfony/pull/5028
                ->add('file', 'file', [
                    'required' => false,
                    'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                    'property_path' => 'file',
                    'label' => 'Фото',
                ])
            ->end()
            ->with('Участвует в событиях', ['class' => 'col-md-4'])
                ->add('events', 'entity', [
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $repository) {
                        $qb = $repository->createQueryBuilder('e');
                        $repository = $qb->orderBy('e.id', 'DESC');

                        return  $repository;
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'События',
                ])
            ->end()
            ->with('Кандидат на события', ['class' => 'col-md-4'])
                ->add('candidateEvents', 'entity', [
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $repository) {
                        $qb = $repository->createQueryBuilder('e');
                        $repository = $qb->orderBy('e.id', 'DESC');

                        return  $repository;
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'События',
                ])
            ->end()
            ->with('Программный комитет', ['class' => 'col-md-4'])
                ->add('committeeEvents', 'entity', [
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $repository) {
                        $qb = $repository->createQueryBuilder('e');
                        $repository = $qb->orderBy('e.id', 'DESC');

                        return  $repository;
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'События',
                ])
            ->end()
        ;
    }
}
