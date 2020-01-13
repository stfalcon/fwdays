<?php

namespace App\Admin;

use App\Admin\AbstractClass\AbstractPageAdmin;
use App\Entity\Event;
use App\Form\Type\MyGedmoTranslationsType;
use App\Service\LocalsRequiredService;
use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class EventPageAdmin.
 */
final class EventPageAdmin extends AbstractPageAdmin
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
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     *
     * @return \Sonata\AdminBundle\Datagrid\ListMapper|void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper = parent::configureListFields($listMapper);
        $listMapper
            ->add('event', null, ['label' => 'Событие'])
            ->add('sortOrder', null, ['label' => 'Номер сортировки']);
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get(LocalsRequiredService::class);
        $localOptions = $localsRequiredService->getLocalsRequiredArray();
        $localOptionsAllFalse = $localsRequiredService->getLocalsRequiredArray(false);
        $formMapper
            ->with('Переводы')
                ->add('translations', MyGedmoTranslationsType::class, [
                    'translatable_class' => $this->getClass(),
                    'fields' => [
                        'title' => [
                            'label' => 'Название',
                            'locale_options' => $localOptions,
                        ],
                        'text' => [
                            'label' => 'текст',
                            'locale_options' => $localOptions,
                        ],
                        'textNew' => [
                            'label' => 'текст для нового дизайна',
                            'locale_options' => $localOptionsAllFalse,
                        ],
                        'metaKeywords' => [
                            'label' => 'metaKeywords',
                            'locale_options' => $localOptionsAllFalse,
                        ],
                        'metaDescription' => [
                            'label' => 'metaDescription',
                            'locale_options' => $localOptionsAllFalse,
                        ],
                    ],
                ])
            ->end()
            ->with('Общие')
                ->add('slug', ChoiceType::class, [
                    'choices' => $this->getSlugChoice(),
                ])
                ->add('event', 'entity', [
                    'class' => Event::class,
                ])
                ->add('showInMenu', null, ['required' => false, 'label' => 'Показывать страницу'])
                ->add('sortOrder', null, [
                    'label' => 'Номер сортировки',
                    'attr' => ['min' => 1],
                ])
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'event',
                null,
                [],
                EntityType::class,
                ['choices' => $this->getEvents()]
            );
    }

    /**
     * @return array
     */
    private function getSlugChoice(): array
    {
        return ['program' => 'program', 'venue' => 'venue'];
    }

    /**
     * @return array
     */
    private function getEvents(): array
    {
        $eventRepository = $this->getConfigurationPool()->getContainer()->get('doctrine')->getRepository(Event::class);

        return $eventRepository->findBy([], ['id' => Criteria::DESC]);
    }
}
