<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

use Stfalcon\Bundle\EventBundle\Entity\MailQueue;

/**
 * Class MailQueueAdmin
 */
class MailQueueAdmin extends Admin
{
    /**
     * @var string
     */
    protected $parentAssociationMapping = 'mail';

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('isSent')
            ->add('isOpen')
            ->add('isUnsubscribe')
            ->add('user.fullname')
            ->add('mail.title')
            ->add('_action', 'actions', array(
                 'actions' => array(
                     'edit'   => array(),
                     'delete' => array(),
                 ),
            ));
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('mail.id', null, array('label' => 'Рассылка'));
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('user')
                ->add('mail')
                ->add('isSent', null, array('required' => false))
            ->end();
    }

    /**
     * @param mixed $mailQueue
     */
    public function postPersist($mailQueue)
    {
        $container = $this->getConfigurationPool()->getContainer();
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $container->get('doctrine')->getManager();

        /** @var MailQueue $mailQueue */
        $mail = $mailQueue->getMail();
        $mail->setTotalMessages($mail->getTotalMessages() + 1);
        $em->persist($mail);
        $em->flush();
    }

    /**
     * @param mixed $mailQueue
     */
    public function postRemove($mailQueue)
    {
        $container = $this->getConfigurationPool()->getContainer();
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $container->get('doctrine')->getManager();

        /** @var MailQueue $mailQueue */
        $mail = $mailQueue->getMail();
        $mail->setTotalMessages($mail->getTotalMessages() - 1);
        $em->persist($mail);
        $em->flush();
    }
}
