<?php

namespace Stfalcon\Bundle\SponsorBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class SponsorAdmin extends Admin
{

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name')
            ->add('site')
            ->add('about')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
            ->add('name')
            ->add('slug')
            ->add('site')
            ->add('about')
            ->add('file', 'file', array('required' => false))
            ->with('Sponsors')
            ->add('events', 'sonata_type_model', array('required' => false), array('edit'     => 'standart', 'expanded' => true, 'multiple' => true))
            ->end()
        ;
    }

    /**
     * Saves an uploaded logo of sponsor
     *
     * @param Stfalcon\Bundle\SponsorBundle\Entity\Sponsor $sponsor
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

    public function prePersist($sponsor)
    {
        $this->uploadLogo($sponsor);
    }

    public function preUpdate($sponsor)
    {
        $this->uploadLogo($sponsor);
    }

    public function getBatchActions()
    {
        $actions = array();
    }
}