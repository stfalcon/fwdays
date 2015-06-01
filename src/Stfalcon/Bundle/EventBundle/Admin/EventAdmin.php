<?php

namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class EventAdmin
 */
class EventAdmin extends Admin
{

    /**
     * Default Datagrid values
     *
     * @var array
     */
    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'date'
    );

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
// @todo можна додати інфу про к-ть зареєстрованих-оплачених-відмічених участників і загальний баланс конф

        $listMapper
            ->add('id')
            ->addIdentifier('name')
            ->add('date')
            ->add('active')
            ->add('cost')
            ->add('receivePayments')
            ->add('useDiscounts')
            ->add('city')
            ->add('place');
// @todo якщо Ірі не потрібні ці картинки, тоді видалити цей код і шаблон
//            ->add(
//                'images',
//                'string',
//                array(
//                    'template' => 'StfalconEventBundle:Admin:images_thumb_layout.html.twig'
//                )
//            );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $formMapper
            ->with('General')
            ->add('name')
            ->add('slug')
            ->add('city')
            ->add('place')
            ->add('date')
            ->add('description')
            ->add('about')
            ->add('active', null, array('required' => false))
            ->add('receivePayments', null, array('required' => false))
            ->add('useDiscounts', null, array('required' => false))
            ->add('cost', null, array('required' => true))
            ->end()
            ->with('Images')
            ->add(
                'logoFile',
                'file',
                array(
                    'label' => 'Logo',
                    'required' => is_null($subject->getLogo())
                )
            )
            ->add(
                'pdfBackgroundFile',
                'file',
                array(
                    'label' => 'Background image',
                    'required' => false,
                )
            )
            ->add(
                'emailBackgroundFile',
                'file',
                array(
                    'label' => 'Email background',
                    'required' => false,
                )
            )
            ->end();
    }

    /**
     * @return array|void
     */
    public function getBatchActions()
    {
        $actions = array();
    }
}
