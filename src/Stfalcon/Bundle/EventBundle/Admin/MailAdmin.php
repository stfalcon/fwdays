<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;
use Knp\Menu\ItemInterface as MenuItemInterface;

use Stfalcon\Bundle\EventBundle\Entity\MailQueue;
use Stfalcon\Bundle\EventBundle\Entity\Mail;

/**
 * Class MailAdmin
 */
class MailAdmin extends Admin
{
    /**
     * @return array
     */
    public function getBatchActions()
    {
        return array();
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('admin_send', $this->getRouterIdParameter() . '/admin-send');
        $collection->add('user_send', 'user-send');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('start', 'boolean', array(
                'editable' => true,
                'label'    => 'Mail active',
                'template' => 'StfalconEventBundle:Admin:list_boolean.html.twig'
            ))
            ->add('statistic', 'string', array('label' => 'Statistic sent/total'))
            ->add('event')
            ->add('_action', 'actions', array(
                'actions'   => array(
                'edit'      => array(),
                'delete'    => array(),
                'ispremium' => array('template' => 'StfalconEventBundle:Admin:list__action_adminsend.html.twig'),
                ),
            ));
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $isEdit = (bool) $this->getSubject()->getId();

        $formMapper
            ->with('General')
            ->add('title')
            ->add('text')
            ->add('event', 'entity', array(
                'class'     => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                'multiple'  => false,
                'expanded'  => false,
                'required'  => false,
                'read_only' => $isEdit
            ))
            ->add('start', null, array('required' => false))
            ->add('paymentStatus', 'choice', array(
                'choices'   => array(
                  'paid'    => 'Оплачено',
                  'pending' => 'Не оплачено'
                ),
                'required'  => false,
                'read_only' => $isEdit
            ))
            ->end();
    }

    /**
     * @param mixed $mail
     *
     * @return mixed|void
     */
    public function postPersist($mail)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getManager();

        if ($mail->getEvent() || $mail->getPaymentStatus()) {
            $users = $em->getRepository('StfalconEventBundle:Ticket')
                ->findUsersByEventAndStatus($mail->getEvent(), $mail->getPaymentStatus());
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

    /**
     * @param MenuItemInterface $menu       Menu
     * @param string            $action     Action
     * @param AdminInterface    $childAdmin Child admin
     */
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
