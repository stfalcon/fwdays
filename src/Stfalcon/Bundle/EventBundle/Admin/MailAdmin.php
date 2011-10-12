<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

//use Stfalcon\Bundle\EventBundle\Entity\Mail;

class MailAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('events')
            ->add('image', 'string', array('template' => 'StfalconEventBundle:Mail:test.html.twig'))
                
        ;
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('title')
                ->add('text')
                ->add('events', 'entity',  array(
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'multiple' => true, 'expanded' => true,
                ))                
                ->add('start', null, array('required' => false))
            ->end()
        ;
    }
    
    public function postUpdate($mail)
    {
        // @todo refact
        if ($mail->getComplete()) {
            return false;
        }
        
        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getEntityManager();
                
        $users = $em->getRepository('ApplicationUserBundle:User')->findAll();
        
        foreach ($users as $user) {
            $mail->replace(array('%fullname%' => $user->getFullname()));
            
            $message = \Swift_Message::newInstance()
                ->setSubject($mail->getTitle())
                // @todo refact
                ->setFrom('orgs@fwdays.com', 'Frameworks Days')
                ->setTo($user->getEmail())
                ->setBody($mail->getText());

            $container->get('mailer')->send($message);
        }
        
        $mail->setComplete(true);
        
        $em->persist($mail);
        $em->flush();
        
        return true;
    }
    
}