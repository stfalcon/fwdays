<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class SpeakerAdmin extends Admin
{
    public function preUpdate($object)
    {
        $this->removeNullTranslate($object);
    }

    public function prePersist($object)
    {
        $this->removeNullTranslate($object);
    }

    private function removeNullTranslate($object)
    {
        foreach ($object->getTranslations() as $key => $translation) {
            if (!$translation->getContent()) {
                $object->getTranslations()->removeElement($translation);
            }
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name', null, ['label' => 'Имя'])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $localsRequiredService = $this->getConfigurationPool()->getContainer()->get('application_default.sonata.locales.required');
        $localOptions = $localsRequiredService->getLocalsRequredArray();
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
                ->add('sortOrder')
                // @todo rm array options https://github.com/dustin10/VichUploaderBundle/issues/27 and https://github.com/symfony/symfony/pull/5028
                ->add('file', 'file', [
                    'required' => false,
                    'data_class' => 'Symfony\Component\HttpFoundation\File\File',
                    'property_path' => 'file',
                    'label' => 'Фото',
                ])
            ->end()
            ->with('Участвует в событиях', ['class' => 'col-md-6'])
                ->add('events', 'entity', [
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'query_builder' =>
                        function (\Doctrine\ORM\EntityRepository $repository) {
                            $qb = $repository->createQueryBuilder('e');
                            $repository = $qb->where($qb->expr()->eq('e.active', '1'));

                            return  $repository;
                        },
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'События',
                ])
            ->end()
            ->with('Кандидат на события', ['class' => 'col-md-6'])
                ->add('candidateEvents', 'entity', [
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'query_builder' =>
                        function (\Doctrine\ORM\EntityRepository $repository) {
                            $qb = $repository->createQueryBuilder('e');
                            $repository = $qb->where($qb->expr()->eq('e.active', '1'));

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
