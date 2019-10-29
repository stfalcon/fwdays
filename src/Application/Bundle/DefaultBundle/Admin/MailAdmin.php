<?php

namespace Application\Bundle\DefaultBundle\Admin;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\EventAudience;
use Application\Bundle\DefaultBundle\Entity\Mail;
use Application\Bundle\DefaultBundle\Entity\MailQueue;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Repository\MailQueueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class MailAdmin.
 */
final class MailAdmin extends AbstractAdmin
{
    private $savedEvents;
    private $savedAudiences;
    /**
     * Default values to the datagrid.
     *
     * @var array
     */
    protected $datagridValues = [
        '_sort_by' => 'id',
        '_sort_order' => 'DESC',
    ];

    /**
     * @return array
     */
    public function getBatchActions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($mail)
    {
        $users = $this->getUsersForEmail($mail);

        $this->addUsersToEmail($mail, $users);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        /** @var Mail $object */
        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getManager();
        /** @var UnitOfWork $uow */
        $uow = $em->getUnitOfWork();
        $originalObject = $uow->getOriginalEntityData($object);

        $eventsChange = \count($this->savedEvents) !== $object->getEvents()->count();
        $audiencesChange = \count($this->savedAudiences) !== $object->getAudiences()->count();

        if (!$eventsChange) {
            foreach ($this->savedEvents as $savedEvent) {
                $founded = false;
                foreach ($object->getEvents() as $event) {
                    $founded = $savedEvent === $event->getId();
                    if ($founded) {
                        break;
                    }
                }
                $eventsChange = !$founded;
                if ($eventsChange) {
                    break;
                }
            }
        }

        if (!$audiencesChange) {
            foreach ($this->savedAudiences as $savedAudience) {
                $founded = false;
                /** @var EventAudience $audience */
                foreach ($object->getAudiences() as $audience) {
                    $founded = $savedAudience === $audience->getId();
                    if ($founded) {
                        break;
                    }
                }
                $audiencesChange = !$founded;
                if ($audiencesChange) {
                    break;
                }
            }
        }

        if ($eventsChange ||
            $audiencesChange ||
            $originalObject['wantsVisitEvent'] !== $object->isWantsVisitEvent() ||
            $originalObject['paymentStatus'] !== $object->getPaymentStatus() ||
            $originalObject['ignoreUnsubscribe'] !== $object->isIgnoreUnsubscribe()
        ) {
            $objectStatus = $object->getStart();
            if (true === $objectStatus) {
                $object->setStart(false);
                $em->flush();
            }
            /** @var MailQueueRepository $queueRepository */
            $queueRepository = $em->getRepository('ApplicationDefaultBundle:MailQueue');
            $deleteCount = $queueRepository->deleteAllNotSentMessages($object);
            $object->setTotalMessages($object->getTotalMessages() - $deleteCount);
            $usersInMail = $em->getRepository('ApplicationDefaultBundle:User')->getUsersFromMail($object);
            $newUsers = $this->getUsersForEmail($object);
            $addUsers = array_diff($newUsers, $usersInMail);

            $this->addUsersToEmail($object, $addUsers);

            if (true === $objectStatus) {
                $object->setStart(true);
                $em->flush();
            }
        }
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('admin_send', $this->getRouterIdParameter().'/admin-send');
        $collection->add('user_send', 'user-send');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, ['label' => 'id'])
            ->addIdentifier('title', null, ['label' => 'Название'])
            ->add('statistic', 'string', ['label' => 'всего/отправлено/открыли/отписались'])
            ->add('audiences', null, ['label' => 'Аудитории'])
            ->add('events', null, ['label' => 'События'])
            ->add('_action', 'actions', [
                'label' => 'Действие',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                    'ispremium' => [
                        'template' => 'ApplicationDefaultBundle:Admin:list__action_adminsend.html.twig',
                    ],
                    'start' => [
                        'template' => 'ApplicationDefaultBundle:Admin:list__action_start.html.twig',
                    ],
                ],
            ]);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Mail $object */
        $object = $this->getSubject();
        $this->savedEvents = [];
        foreach ($object->getEvents() as $event) {
            $this->savedEvents[] = $event->getId();
        }
        $this->savedAudiences = [];
        foreach ($object->getAudiences() as $audience) {
            $this->savedAudiences[] = $audience->getId();
        }

        $formMapper
            ->with('Общие')
                ->add('title', null, ['label' => 'Название'])
                ->add('text', null, ['label' => 'Текст'])
                ->add('audiences', null, ['label' => 'Аудитории'])
                ->add('events', 'entity', [
                    'class' => Event::class,
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                    'label' => 'События',
                ])
                ->add('start', null, ['required' => false, 'label' => 'Запустить'])
                ->add('wantsVisitEvent', null, ['label' => 'Подписанным на события', 'required' => false])
                ->add('paymentStatus', ChoiceType::class, [
                    'choices' => Payment::getPaymentStatusChoice(),
                    'required' => false,
                    'label' => 'Статус оплаты',
                ])
                ->add('ignoreUnsubscribe', null, ['label' => 'Отправлять отписанным от розсылки', 'required' => false])
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Общие')
                ->add('title', null, ['label' => 'Название'])
                ->add('text', null, ['label' => 'Текст'])
                ->add('audiences', null, ['label' => 'Аудитории'])
                ->add('events', 'entity', ['label' => 'События'])
                ->add('start', null, ['required' => false, 'label' => 'Запустить'])
                ->add('wantsVisitEvent', null, ['label' => 'Подписанным на события'])
                ->add('paymentStatus', null, ['label' => 'Статус оплаты'])
                ->add('ignoreUnsubscribe', null, ['label' => 'Отправлять отписанным от розсылки'])
            ->end()
        ;
    }

    /**
     * @param MenuItemInterface $menu       Menu
     * @param string            $action     Action
     * @param AdminInterface    $childAdmin Child admin
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !\in_array($action, ['edit', 'show'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild('Mail', ['uri' => $admin->generateUrl('edit', ['id' => $id])]);
        $menu->addChild('Line items', ['uri' => $admin->generateUrl('app.admin.mail|app.admin.mail_queue.list', ['id' => $id])]);
    }

    /**
     * @param Mail $mail
     *
     * @return array
     */
    private function getUsersForEmail($mail)
    {
        $container = $this->getConfigurationPool()->getContainer();

        $eventCollection = $mail->getEvents()->toArray();

        /** @var EventAudience $audience */
        foreach ($mail->getAudiences() as $audience) {
            $eventCollection = array_merge($eventCollection, $audience->getEvents()->toArray());
        }
        $events = [];
        foreach ($eventCollection as $event) {
            $events[$event->getId()] = $event;
        }
        $events = new ArrayCollection($events);
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        if ($events->count() > 0 && $mail->isWantsVisitEvent()) {
            $users = $em->getRepository('ApplicationDefaultBundle:User')->getRegisteredUsers($events, $mail->isIgnoreUnsubscribe());
        } elseif ($events->count() > 0 || $mail->getPaymentStatus()) {
            $users = $em->getRepository('ApplicationDefaultBundle:Ticket')
                ->
                findUsersSubscribedByEventsAndStatus($events, $mail->getPaymentStatus(), $mail->isIgnoreUnsubscribe());
        } else {
            $users = $em->getRepository('ApplicationDefaultBundle:User')->getAllSubscribed($mail->isIgnoreUnsubscribe());
        }

        return $users;
    }

    /**
     * @param Mail  $mail
     * @param array $users
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function addUsersToEmail($mail, $users)
    {
        $container = $this->getConfigurationPool()->getContainer();
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        if (isset($users)) {
            $countSubscribers = $mail->getTotalMessages();
            /** @var User $user */
            foreach ($users as $user) {
                if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) &&
                    $user->isEnabled() &&
                    $user->isEmailExists()
                ) {
                    $mailQueue = new MailQueue();
                    $mailQueue->setUser($user);
                    $mailQueue->setMail($mail);
                    $em->persist($mailQueue);
                    ++$countSubscribers;
                }
            }
            $mail->setTotalMessages($countSubscribers);
            $em->persist($mail);
            $em->flush();
        }
    }
}
