<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Admin\AbstractClass\AbstractTranslateAdmin;
use App\Entity\Sponsor;
use App\Traits\LiipImagineTrait;
use App\Traits\LocalsRequiredServiceTrait;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

/**
 * SponsorAdmin Class.
 */
class SponsorAdmin extends AbstractTranslateAdmin
{
    use LiipImagineTrait;
    use LocalsRequiredServiceTrait;

    /**
     * @return array
     */
    public function getBatchActions(): array
    {
        return [];
    }

    /**
     * @param Sponsor $object
     */
    public function postUpdate($object): void
    {
        $this->prepareImageCache($object);
    }

    /**
     * @param Sponsor $object
     */
    public function postPersist($object): void
    {
        $this->prepareImageCache($object);
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
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
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        /** @var Sponsor $subject */
        $subject = $this->getSubject();
        $localOptionsAllFalse = $this->localsRequiredService->getLocalsRequiredArray(false);
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
                    FileType::class,
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
                    CollectionType::class,
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
    private function prepareImageCache(Sponsor $sponsor): void
    {
        $filter = 'partner';
        $target = $sponsor->getLogo();
        if (empty($target)) {
            return;
        }

        if (!$this->liipImagineCacheManager->isStored($target, $filter)) {
            $this->liipImagineCacheManager->store($this->liipImagineFilterManager->applyFilter($this->liipImagineDataManager->find($filter, $target), $filter), $target, $filter);
        }
    }
}
