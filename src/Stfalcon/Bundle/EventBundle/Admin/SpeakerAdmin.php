<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

//use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Speaker;

class SpeakerAdmin extends Admin 
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('slug')
            ->add('name')
        ;
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('slug')
                ->add('name')
                ->add('email')
                ->add('company')
                ->add('about')
                ->add('file', 'file')
            ->end()
        ;
    }
    
    /**
     * Saves an uploaded photo of speakers
     * 
     * @param Speaker $speaker
     * @return void 
     */
    public function uploadLogo($speaker)
    {
        if (null === $speaker->getFile()) {
            return;
        }
        
        $uploadDir = '/uploads/speakers';
        $pathToUploads = realpath($this->getConfigurationPool()->getContainer()->get('kernel')->getRootDir() . '/../web' . $uploadDir);
        $newFileName = $speaker->getSlug() . '.' . pathinfo($speaker->getFile()->getClientOriginalName(), PATHINFO_EXTENSION);
        
        $speaker->getFile()->move($pathToUploads, $newFileName);
        $speaker->setPhoto($uploadDir . '/' . $newFileName);
        
        $speaker->setFile(null);
    }
    
    public function prePersist($speaker)
    {
        $this->uploadLogo($speaker);
    }
    
    public function preUpdate($speaker) {
        $this->uploadLogo($speaker);
    }
}