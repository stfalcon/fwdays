<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\GedmoTranslationsType;
use App\Entity\Event;
use App\Entity\EventAudience;
use App\Entity\Mail;
use App\Entity\MailQueue;
use App\Entity\Payment;
use App\Entity\User;
use App\Repository\MailQueueRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Traits\EntityManagerTrait;
use App\Traits\LocalsRequiredServiceTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\UnitOfWork;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class MailAdmin.
 */
final class MailAdmin extends AbstractAdmin
{
    use EntityManagerTrait;
    use LocalsRequiredServiceTrait;

    /** @var array */
    private $savedEvents;
    /** @var array */
    private $savedAudiences;

    private $userRepository;
    private $ticketRepository;

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
     * MailAdmin constructor.
     *
     * @param string           $code
     * @param string           $class
     * @param string           $baseControllerName
     * @param UserRepository   $userRepository
     * @param TicketRepository $ticketRepository
     */
    public function __construct($code, $class, $baseControllerName, UserRepository $userRepository, TicketRepository $ticketRepository)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->userRepository = $userRepository;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * @return array
     */
    public function getBatchActions(): array
    {
        return [];
    }

    /**
     * @param Mail $mail
     *
     * @throws OptimisticLockException
     */
    public function postPersist($mail): void
    {
        $users = $this->getUsersForEmail($mail);

        $this->addUsersToEmail($mail, $users);
    }

    /**
     * @param Mail $object
     *
     * @throws OptimisticLockException
     */
    public function preUpdate($object): void
    {
        /** @var UnitOfWork $uow */
        $uow = $this->em->getUnitOfWork();
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
                $this->em->flush();
            }
            /** @var MailQueueRepository $queueRepository */
            $queueRepository = $this->em->getRepository(MailQueue::class);
            $deleteCount = $queueRepository->deleteAllNotSentMessages($object);
            $object->setTotalMessages($object->getTotalMessages() - $deleteCount);
            $usersInMail = $this->userRepository->getUsersFromMail($object);
            $newUsers = $this->getUsersForEmail($object);
            $addUsers = \array_diff($newUsers, $usersInMail);

            $this->addUsersToEmail($object, $addUsers, true);

            if (true === $objectStatus) {
                $object->setStart(true);
                $this->em->flush();
            }
        }
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->add('admin_send', $this->getRouterIdParameter().'/admin-send');
        $collection->add('user_send', 'user-send');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('id', null, ['label' => 'id'])
            ->add('startDate', null, ['label' => 'Дата запуска'])
            ->addIdentifier('title', null, ['label' => 'Название'])
            ->add('statistic', 'string', ['label' => 'всего/отправлено/открыли/отписались'])
            ->add('usersLocalsStatistic', 'string', ['label' => 'получатели украинской / английской версий'])
            ->add('audiences', null, ['label' => 'Аудитории'])
            ->add('events', null, ['label' => 'События'])
            ->add('_action', 'actions', [
                'label' => 'Действие',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                    'ispremium' => [
                        'template' => 'Admin/list__action_adminsend.html.twig',
                    ],
                    'start' => [
                        'template' => 'Admin/list__action_start.html.twig',
                    ],
                ],
            ]);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $localOptions = $this->localsRequiredService->getLocalsRequiredArray(true);

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
            ->with('Переводы')
            ->add('translations', GedmoTranslationsType::class, [
                'translatable_class' => $this->getClass(),
                'fields' => [
                    'title' => [
                        'label' => 'Название',
                        'locale_options' => $localOptions,
                    ],
                    'text' => [
                        'label' => 'Текст',
                        'locale_options' => $localOptions,
                    ],
                ],
            ])
            ->end()
            ->with('События', ['class' => 'col-md-6'])
                ->add('audiences', null, ['label' => 'Аудитории', 'help' => 'События, на которые есть любой билет, либо регистрация при указаной опции "Подписанным на события" ("любое из")'])
                ->add('events', EntityType::class, [
                    'class' => Event::class,
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                    'label' => 'События',
                    'help' => 'События, для которых действует фильтр статус оплаты. Если указано больше чем одно событие - "любое из"',
                ])
            ->end()
            ->with('Фильтры', ['class' => 'col-md-6'])
                ->add('wantsVisitEvent', null, ['label' => 'Подписанным на события', 'required' => false, 'help' => 'действует на аудиторию либо на аудиторию + события, если не указан статус оплаты'])
                ->add('paymentStatus', ChoiceType::class, [
                    'choices' => Payment::getPaymentStatusChoice(),
                    'required' => false,
                    'label' => 'Статус оплаты',
                    'help' => 'проверяет статус билета на ивент(-ы) указаные в поле "События" ("любое из")',
                ])
                ->add('ignoreUnsubscribe', null, ['label' => 'Отправлять отписанным от рассылки', 'required' => false])
            ->end()
            ->with('Запустить')
                ->add('start', null, ['required' => false, 'label' => 'Запустить'])
            ->end();
    }

    /**
     * @param MenuItemInterface $menu       Menu
     * @param string            $action     Action
     * @param AdminInterface    $childAdmin Child admin
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !\in_array($action, ['edit', 'show'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild('Mail', ['uri' => $admin->generateUrl('edit', ['id' => $id])]);
        $menu->addChild('Line items', ['uri' => $admin->generateUrl('app.admin.mails|app.admin.mail_queue.list', ['id' => $id])]);
    }

    /**
     * @param Mail $mail
     *
     * @return array
     */
    private function getUsersForEmail($mail): array
    {
        $paymentStatus = $mail->getPaymentStatus();

        $eventCollection = $paymentStatus ? [] : $mail->getEvents()->toArray();
        /** @var EventAudience $audience */
        foreach ($mail->getAudiences() as $audience) {
            $eventCollection = \array_merge($eventCollection, $audience->getEvents()->toArray());
        }

        $allEvents = [];
        foreach ($eventCollection as $event) {
            $allEvents[$event->getId()] = $event;
        }
        $allEvents = new ArrayCollection($allEvents);

        $selectedEvents = $mail->getEvents();

        $isIgnoreUnsubscribe = $mail->isIgnoreUnsubscribe();

        if ($allEvents->count() > 0 || $selectedEvents->count() > 0) {
            $users = $this->userRepository->getUsersForEmail($mail->isWantsVisitEvent(), $allEvents, $selectedEvents, $isIgnoreUnsubscribe, $paymentStatus);
        } else {
            $users = $this->userRepository->getAllSubscribed($isIgnoreUnsubscribe);
        }

        return $users;
    }

    /**
     * @param Mail  $mail
     * @param array $users
     * @param bool  $recalculateLocals
     *
     * @throws OptimisticLockException
     */
    private function addUsersToEmail($mail, $users, bool $recalculateLocals = false): void
    {
        if (\count($users) > 0) {
            $countSubscribers = $mail->getTotalMessages();
            /** @var User $user */
            foreach ($users as $user) {
                if ($user->isEnabled() &&
                    $user->isEmailExists()
                ) {
                    $mailQueue = new MailQueue();
                    $mailQueue->setUser($user);
                    $mailQueue->setMail($mail);
                    $this->em->persist($mailQueue);
                    ++$countSubscribers;
                    if (!$recalculateLocals) {
                        $mail->processIncrementUserLocal($user->getEmailLanguage());
                    }
                }
            }
            $mail->setTotalMessages($countSubscribers);
            $this->persistAndFlush($mail);

            if ($recalculateLocals) {
                $mail->setUsersWithEnLocal(0);
                $mail->setUsersWithUkLocal(0);

                foreach ($mail->getMailQueues() as $mailQueue) {
                    $user = $mailQueue->getUser();
                    if ($user instanceof User) {
                        $mail->processIncrementUserLocal($user->getEmailLanguage());
                    }
                }
                $this->em->flush();
            }
        }
    }
}
