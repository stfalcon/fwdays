<?php
namespace Stfalcon\Bundle\EventBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Bundle\MenuBundle\MenuItem;

class MailAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('event')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('title')
                ->add('text')
                ->add('event', 'entity',  array(
                    'class' => 'Stfalcon\Bundle\EventBundle\Entity\Event',
                    'multiple' => false, 'expanded' => false, 'required' => false
                ))
                ->add('start', null, array('required' => false))
            ->end()
        ;
    }

    public function postUpdate($mail)
    {
        if (!$mail->getStart()) {
            return false;
        }

        // @todo refact
        if ($mail->getComplete()) {
            throw new \Exception('Эта рассылка уже разослана');
        }

        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getEntityManager();

        if ($mail->getEvent()) {
            // @todo сделать в репо метод для выборки пользователей, которые отметили ивент
            $tickets = $em->getRepository('StfalconEventBundle:Ticket')
                ->findBy(array('event' => $mail->getEvent()->getId()));

            foreach ($tickets as $ticket) {
                $users[] = $ticket->getUser();
            }
        } else {
            $users = $em->getRepository('ApplicationUserBundle:User')->findAll();
        }

        foreach ($users as $user) {
            if (!$user->isSubscribe()) {
                continue;
            }

            $text = $mail->replace(
                array(
                    '%fullname%' => $user->getFullname(),
                    '%user_id%' => $user->getId(),
                )
            );

            $message = \Swift_Message::newInstance()
                ->setSubject($mail->getTitle())
                // @todo refact
                ->setFrom('orgs@fwdays.com', 'Frameworks Days')
                ->setTo($user->getEmail())
                ->setBody($text);

            // @todo каждый вызов отнимает память
            $container->get('mailer')->send($message);
        }

        $mail->setComplete(true);

        $em->persist($mail);
        $em->flush();

        return true;
    }

}