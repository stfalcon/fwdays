<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Application\Bundle\DefaultBundle\Admin\AbstractClass\AbstractTranslateAdmin;
use Application\Bundle\DefaultBundle\Entity\Speaker;
use Application\Bundle\DefaultBundle\Form\Type\MyGedmoTranslationsType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class SpeakerAdmin.
 */
class SpeakerAdmin extends AbstractTranslateAdmin
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
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        $this->prepareImageCache($object);
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        $this->prepareImageCache($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        $formMapper
            ->with('Переводы')
                ->add('translations', MyGedmoTranslationsType::class, [
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
            ->with('Участвует в событиях', ['class' => 'col-md-3'])
                ->add('events', 'entity', [
                    'class' => 'Application\Bundle\DefaultBundle\Entity\Event',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) {
                        $qb = $repository->createQueryBuilder('e');
                        $repository = $qb->orderBy('e.id', 'DESC');

                        return  $repository;
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'События',
                ])
            ->end()
            ->with('Кандидат на события', ['class' => 'col-md-3'])
                ->add('candidateEvents', 'entity', [
                    'class' => 'Application\Bundle\DefaultBundle\Entity\Event',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) {
                        $qb = $repository->createQueryBuilder('e');
                        $repository = $qb->orderBy('e.id', 'DESC');

                        return  $repository;
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'События',
                ])
            ->end()
            ->with('Программный комитет', ['class' => 'col-md-3'])
                ->add('committeeEvents', 'entity', [
                    'class' => 'Application\Bundle\DefaultBundle\Entity\Event',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) {
                        $qb = $repository->createQueryBuilder('e');
                        $repository = $qb->orderBy('e.id', 'DESC');

                        return  $repository;
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'События',
                ])
            ->end()
            ->with('Эксперт дискуссий', ['class' => 'col-md-3'])
            ->add('expertEvents', 'entity', [
                'class' => 'Application\Bundle\DefaultBundle\Entity\Event',
                'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) {
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
     * @param Speaker $speaker
     */
    private function prepareImageCache(Speaker $speaker)
    {
        $filter = 'speaker';
        $target = $speaker->getPhoto();
        if (empty($target)) {
            return;
        }
        $container = $this->getConfigurationPool()->getContainer();
        $cacheManager = $container->get('liip_imagine.cache.manager');
        if (!$cacheManager->isStored($target, $filter)) {
            $filterManager = $container->get('liip_imagine.filter.manager');
            $dataManager = $container->get('liip_imagine.data.manager');
            $cacheManager->store($filterManager->applyFilter($dataManager->find($filter, $target), $filter), $target, $filter);
        }
    }
}
