<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

use Stfalcon\Bundle\EventBundle\Entity\Event;

class EventAdmin extends Admin 
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('title')
        ;
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('slug')
                ->add('title')
                ->add('description')
                ->add('file', 'file')
            ->end()
        ;
    }
    
    public function prePersist($object)
    {
        if (null === $object->file) {
            return;
        }
    
        $uploadDir = '/uploads/event';
        $pathToUploads = realpath($this->getConfigurationPool()->getContainer()->get('kernel')->getRootDir() . '/../web' . $uploadDir);
        $newFileName = $object->getSlug() . '.' . pathinfo($object->file->getClientOriginalName(), PATHINFO_EXTENSION);
        
        $object->file->move($pathToUploads, $newFileName);
        $object->setLogo($uploadDir . '/' . $object->file->getClientOriginalName());
        
        unset($object->file);
    }
}