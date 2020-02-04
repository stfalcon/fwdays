<?php

namespace App\Admin;

use App\Entity\MailQueue;
use App\Traits\EntityManagerTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class MailQueueAdmin.
 */
final class MailQueueAdmin extends AbstractAdmin
{
    use EntityManagerTrait;

    /**
     * @var string
     */
    protected $parentAssociationMapping = 'mail';

    /**
     * @param MailQueue $mailQueue
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postPersist($mailQueue): void
    {
        /** @var MailQueue $mailQueue */
        $mail = $mailQueue->getMail();
        $mail->incTotalMessages();
        $this->em->flush();
    }

    /**
     * @param MailQueue $mailQueue
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postRemove($mailQueue): void
    {
        $mail = $mailQueue->getMail();
        $mail->decTotalMessages();
        if ($mailQueue->getIsSent()) {
            $mail->decSentMessage();
        }
        $this->em->flush();
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
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
                        'edit' => [],
                        'delete' => [],
                    ],
            ]);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('mail.id', null, ['label' => 'Id письма'])
            ->add('isSent', null, ['label' => 'Отправлено'])
            ->add('isOpen', null, ['label' => 'Открыто'])
            ->add('isUnsubscribe', null, ['label' => 'Отписались'])
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Общие')
                ->add('user', null, ['label' => 'Пользователь'])
                ->add('mail', null, ['label' => 'Почта'])
                ->add('isSent', null, ['required' => false, 'label' => 'Отправлено'])
            ->end();
    }
}
