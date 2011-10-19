<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

//use Knp\Bundle\MenuBundle\MenuItem;
//
//use Stfalcon\Bundle\EventBundle\Entity\Event;

class EventAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->addIdentifier('slug')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('name')
                ->add('slug')
                ->add('city')
                ->add('place')
                ->add('dateStart')
                ->add('dateEnd')
                ->add('description')
                ->add('about')
                ->add('file', 'file', array('required' => false))
                ->add('active')
            ->end()
        ;
    }

    /**
     * Saves an uploaded logo of event
     *
     * @param Event $event
     * @return void
     */
    public function uploadLogo($event)
    {
        if (null === $event->getFile()) {
            return;
        }

        $uploadDir = '/uploads/events';
        $pathToUploads = realpath($this->getConfigurationPool()->getContainer()->get('kernel')->getRootDir() . '/../web' . $uploadDir);
        $newFileName = $event->getSlug() . '.' . pathinfo($event->getFile()->getClientOriginalName(), PATHINFO_EXTENSION);

        $event->getFile()->move($pathToUploads, $newFileName);
        $event->setLogo($uploadDir . '/' . $newFileName);

        $event->setFile(null);
    }

    public function prePersist($event)
    {
        $this->uploadLogo($event);
    }

    public function preUpdate($event) {
        $this->uploadLogo($event);
    }

    public function getBatchActions()
    {
        $actions = array();
    }
}