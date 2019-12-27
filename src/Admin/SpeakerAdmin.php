<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\Event;
use App\Entity\Speaker;
use App\Service\LocalsRequiredService;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\File;

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
    public function postUpdate($object): void
    {
        $this->prepareImageCache($object);
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object): void
    {
        $this->prepareImageCache($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get(LocalsRequiredService::class);
        $localOptions = $localsRequiredService->getLocalsRequiredArray();

        $eventFormOptions = [
            'class' => Event::class,
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'label' => 'События',
        ];

        $formMapper
            ->with('Переводы')
                ->add('translations', GedmoTranslationsType::class, [
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
                ->add('file', FileType::class, [
                    'required' => false,
                    'data_class' => File::class,
                    'property_path' => 'file',
                    'label' => 'Фото',
                ])
            ->end()
            ->with('Участвует в событиях', ['class' => 'col-md-3'])
                ->add('events', EntityType::class, $eventFormOptions)
            ->end()
            ->with('Кандидат на события', ['class' => 'col-md-3'])
                ->add('candidateEvents', EntityType::class, $eventFormOptions)
            ->end()
            ->with('Программный комитет', ['class' => 'col-md-3'])
                ->add('committeeEvents', EntityType::class, $eventFormOptions)
            ->end()
            ->with('Эксперт дискуссий', ['class' => 'col-md-3'])
                ->add('expertEvents', EntityType::class, $eventFormOptions)
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('slug')
            ->add('name')
            ->add('events', null, ['label' => 'Участвует в событиях'])
            ->add('candidateEvents', null, ['label' => 'Кандидат на события'])
            ->add('committeeEvents', null, ['label' => 'Программный комитет'])
            ->add('expertEvents', null, ['label' => 'Эксперт дискуссий'])
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
    private function prepareImageCache(Speaker $speaker): void
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
