<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * RefererAdmin.
 */
final class RefererAdmin extends AbstractAdmin
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
    public function hasAccess($action, $object = null): bool
    {
        $result = parent::hasAccess($action, $object);
        if (\in_array($action, ['delete', 'create'], true)) {
            return false;
        }

        return $result;
    }

    /**
     * Allows you to customize batch actions.
     *
     * @param array $actions List of actions
     *
     * @return array
     */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('user.fullname', null, ['label' => 'Пользователь'])
            ->add('user.email', null, ['label' => 'E-mail'])
            ->add('date', null, ['label' => 'Дата перехода'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->add(
                'user',
                'string',
                [
                    'template' => 'Admin/user_or_cookie_link_field.html.twig',
                    'label' => 'Пользователь',
                ]
            )
            ->add('user.email')
            ->add('date', null, ['label' => 'Дата перехода'])
            ->add('fromUrl', null, ['label' => 'Referer'])
            ->add('toUrl', null, ['label' => 'Перешел на url'])
        ;
    }
}
