<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;
use Knp\Menu\ItemInterface as MenuItemInterface;

use Stfalcon\Bundle\EventBundle\Entity\MailQueue;
use Stfalcon\Bundle\EventBundle\Entity\Mail;

class MailAdmin extends Admin
{

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('start', 'boolean', array(
                'editable' => true,
                'label' => 'Mail active'
            ))
            ->add('statistic', 'string', array('label' => 'Statistic sent/total'))
            ->add('event')
            ->add('_action', 'actions', array(
                'label' => 'Show mail queue',
                'actions' => array(
                    'edit' => array(),
                ),
            ));
    }


    protected function configureFormFields(FormMapper $formMapper)
    {
        $isEdit = (bool) $this->getSubject()->getId();


        $formMapper
            ->with('General')
            ->add('title')
            ->add('text')
            ->add('event', 'entity', array(
                'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'read_only' => $isEdit
            ))
            ->add('start', null, array('required' => false))
            ->add('paymentStatus', 'choice', array(
                'choices' => array('paid' => 'Оплачено', 'pending' => 'Не оплачено'),
                'required' => false,
                'read_only' => $isEdit))
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

    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit', 'show'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild('Mail', array('uri' => $admin->generateUrl('edit', array('id' => $id))));
        $menu->addChild('Line items', array('uri' => $admin->generateUrl('stfalcon_event.admin.mail_queue.list', array('id' => $id))));
    }


}