<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;
use Stfalcon\Bundle\EventBundle\Entity\MailQueue;

class MailAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('start', 'boolean', array(
                'template' => 'StfalconEventBundle:Event:sonata_list_boolean.html.twig',
                'editable' => true,
                'label' => 'Mail active'
            ))
            ->add('statistic', 'string', array('label' => 'Statistic sent/total'))
            ->add('event')
            ->add('_action', 'actions', array(
                'label' => 'Show mail queue',
                'actions' => array(
                    'view' => array(),
                )
            ));
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
            ->add('title')
            ->add('text')
            ->add('event', 'entity', array(
                'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                'multiple' => false, 'expanded' => false, 'required' => false
            ))
            ->add('start', null, array('required' => false))
            ->add('paymentStatus', 'choice', array(
                'choices' => array('paid' => 'Оплачено', 'pending' => 'Не оплачено'),
                'required' => false))
            ->end();
    }

    public function postPersist($mail)
    {

        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getEntityManager();

        if ($mail->getEvent()) {
            // @todo сделать в репо метод для выборки пользователей, которые отметили ивент
            $tickets = $em->getRepository('StfalconEventBundle:Ticket')
                ->findBy(array('event' => $mail->getEvent()->getId()));

            foreach ($tickets as $ticket) {
                // @todo тяжелая цепочка
                // нужно сделать выборку билетов с платежами определенного статуса
                if ($mail->getPaymentStatus()) {
                    if ($ticket->getPayment() && $ticket->getPayment()->getStatus() == $mail->getPaymentStatus()) {
                        $users[] = $ticket->getUser();
                    }
                } else {
                    $users[] = $ticket->getUser();
                }
            }
        } else {
            $users = $em->getRepository('ApplicationUserBundle:User')->findAll();
        }

        if (isset($users)) {

            $mail->setTotalMessages(count($users));
            $em->persist($mail);

            foreach ($users as $user) {
                $mailQueue = new MailQueue();
                $mailQueue->setUser($user);
                $mailQueue->setMail($mail);
                $em->persist($mailQueue);
            }
        }

        $em->flush();

    }

}