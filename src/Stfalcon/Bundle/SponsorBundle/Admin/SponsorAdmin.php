<?php

namespace Stfalcon\Bundle\SponsorBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * SponsorAdmin Class
 */
class SponsorAdmin extends Admin
{
    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name')
            ->add('site')
            ->add('about')
            ->add('onMain')
            ->add('sortOrder');
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('name')
                ->add('slug')
                ->add('site')
                ->add('about')
                ->add('file', 'file', array('required' => false))
                ->add('sortOrder', null, array(
                    'attr' => array(
                        'min' => 1
                    )
                ))
                ->add('onMain', null, array('required' => false))
            ->with('Events')
            ->add('sponsorEvents', 'sonata_type_collection',
                array(
                    'label' => 'Events',
                    'by_reference' => false
                ), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                ))
            ->end();
    }

    /**
     * Saves an uploaded logo of sponsor
     *
     * @param \Stfalcon\Bundle\SponsorBundle\Entity\Sponsor $sponsor
     *
     * @return void
     */
    public function uploadLogo($sponsor)
    {
        if (null === $sponsor->getFile()) {
            return;
        }

        $uploadDir     = '/uploads/sponsors';
        $pathToUploads = realpath($this->getConfigurationPool()->getContainer()->get('kernel')->getRootDir() . '/../web' . $uploadDir);
        $newFileName   = $sponsor->getSlug() . '.' . pathinfo($sponsor->getFile()->getClientOriginalName(), PATHINFO_EXTENSION);

        $sponsor->getFile()->move($pathToUploads, $newFileName);
        $sponsor->setLogo($uploadDir . '/' . $newFileName);

        $sponsor->setFile(null);
    }

    /**
     * @param mixed $sponsor
     *
     * @return mixed|void
     */
    public function prePersist($sponsor)
    {
        $this->uploadLogo($sponsor);
    }

    /**
     * @param mixed $sponsor
     *
     * @return mixed|void
     */
    public function preUpdate($sponsor)
    {
        $this->uploadLogo($sponsor);
    }

    /**
     * @return array|void
     */
    public function getBatchActions()
    {
        $actions = array();
    }
}
