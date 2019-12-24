<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\Sponsor;
use App\Service\LocalsRequiredService;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * SponsorAdmin Class.
 */
class SponsorAdmin extends AbstractTranslateAdmin
{
    /**
     * @return array|void
     */
    public function getBatchActions()
    {
        return [];
    }

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
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, ['label' => 'Название'])
            ->add('site', null, ['label' => 'Сайт'])
            ->add('about', null, ['label' => 'Описание'])
            ->add('sortOrder', null, ['label' => 'Номер сортировки'])
            ->add('_action', 'actions', [
                'label' => 'Действие',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Sponsor $subject */
        $subject = $this->getSubject();
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get(LocalsRequiredService::class);
        $localOptionsAllFalse = $localsRequiredService->getLocalsRequiredArray(false);
        $formMapper
            ->with('Переводы')
            ->add('translations', GedmoTranslationsType::class, [
                'label' => 'Переводы',
                'translatable_class' => $this->getClass(),
                'fields' => [
                    'about' => [
                        'label' => 'Описание',
                        'locale_options' => $localOptionsAllFalse,
                    ],
                ],
            ])
            ->end()
            ->with('Общие')
                ->add('name')
                ->add('site', null, ['label' => 'Сайт'])
                ->add(
                    'file',
                    'file',
                    [
                        'label' => 'Логотип',
                        'required' => false,
                        'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                        'property_path' => 'file',
                        'help' => $subject->getLogo() ? $subject->getLogo() : '',
                    ]
                )
                ->add('sortOrder', null, ['attr' => ['min' => 1], 'label' => 'Номер сортировки'])
            ->end()
            ->with('События')
                ->add(
                    'sponsorEvents',
                    'sonata_type_collection',
                    [
                        'label' => 'Спонсируемые события',
                        'by_reference' => false,
                    ],
                    [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ]
                )
            ->end();
    }

    /**
     * @param Sponsor $sponsor
     */
    private function prepareImageCache(Sponsor $sponsor)
    {
        $filter = 'partner';
        $target = $sponsor->getLogo();
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
