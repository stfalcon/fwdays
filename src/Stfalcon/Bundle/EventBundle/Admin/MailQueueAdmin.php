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
            ->add('isSent', null, ['label' => 'Отправлено'])
            ->add('isOpen', null, ['label' => 'Открыто'])
            ->add('isUnsubscribe', null, ['label' => 'Отписался'])
            ->add('user.fullname', null, ['label' => 'Имя пользователя'])
            ->add('mail.title', null, ['label' => 'Название'])
            ->add('_action', 'actions', [
                 'label' => 'Действие',
                 'actions' => [
                     'edit'   => [],
                     'delete' => [],
                 ],
            ]);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('mail.id', null, ['label' => 'Рассылка']);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Общие')
                ->add('user', null, ['label' => 'Пользователь'])
                ->add('mail', null, ['label' => 'Почта'])
                ->add('isSent', null, ['required' => false, 'label' => 'Отправлено'])
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
