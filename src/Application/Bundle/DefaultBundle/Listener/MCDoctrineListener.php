<?php
namespace Application\Bundle\DefaultBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\UserBundle\Model\UserInterface;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * Doctrine PostPresist listener for mailChimp service
 *
 * @param service $mailChimp
 */
class MCDoctrineListener
{
    /**
     * @var \MZ\MailChimpBundle\Services\MailChimp
     */
    private $mailChimp;

    public function __construct($mailChimp)
    {
        $this->mailChimp = $mailChimp;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        /*
         * Create new group with subgroups
         * in mailChimp account
         * when new event added
         */
        if ($entity instanceof Event) {
            $list = $this->mailChimp->getList();
            $list->listInterestGroupingAdd(
                $entity->getSlug(), 'hidden',
                array(
                    Payment::STATUS_PAID,
                    Payment::STATUS_PENDING)
            );
        }

        /*
         * Added user to subgroup of event in mailChimp
         * when he was take-part event
         */
        if ($entity instanceof Ticket) {
            $list = $this->mailChimp->getList();
            $mergeVars = array('GROUPINGS' => array(
                array(
                    'name' => $entity->getEvent()->getSlug(),
                    'groups' => Payment::STATUS_PENDING
                )));
            $list->setMerge($mergeVars);
            $list->MergeVars();
            $list->setEmail($entity->getUser()->getEmail());
            $list->updateMember();
        }
    }

    /**
     * Doctrine PreUpdate listener for mailChimp service
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        // move user to paid group when he pay for the ticket
        if ($entity instanceof Payment) {
            $list = $this->mailChimp->getList();
            $em = $args->getEntityManager();
            $ticket = $em->getRepository('StfalconEventBundle:Ticket')
                ->findOneByPayment($entity->getId());

            $mergeVars = array('GROUPINGS' => array(
                array(
                    'name' => $ticket->getEvent()->getSlug(),
                    'groups' => $entity->isPaid() ? Payment::STATUS_PAID : Payment::STATUS_PENDING
                )));
            $list->setMerge($mergeVars);
            $list->MergeVars();
            $list->setEmail($entity->getUser()->getEmail());
            $list->updateMember();
        }

        // user update some fields in personal profile
        if ($entity instanceof UserInterface) {

            $list = $this->mailChimp->getList();
            $list->setDoubleOptin(false);


            // Subscribe user when he activate profile by email
            if ($args->hasChangedField('enabled')) {

                $mergeVars = array(
                    'FNAME' => $entity->getFullname(),
                    'SUBSCRIBE' => $entity->isSubscribe(),
                );

                $list->setMerge($mergeVars);
                $list->setUpdateExisting(true);
                $args->getNewValue('enabled') ?
                    $list->Subscribe($entity->getEmail()) :
                    $list->UnSubscribe($entity->getEmail());

                // update some fields in mailChimp when user update profile
            }

            if ($args->hasChangedField('email')     ||
                $args->hasChangedField('fullname')  ||
                $args->hasChangedField('subscribe')
            ) {
                $args->hasChangedField('email') ?
                    $list->setEmail($args->getOldValue('email')) :
                    $list->setEmail($entity->getEmail());

                $mergeVars = array(
                    'FNAME' => $entity->getFullname(),
                    'SUBSCRIBE' => $entity->isSubscribe(),
                );

                $list->setMerge($mergeVars);
                $list->MergeVars($entity->getEmail());
                $list->UpdateMember();
            }
        }
    }

    /**
     * Unsubscribe user when removed
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof UserInterface) {
            $list = $this->mailChimp->getList();
            $list->UnSubscribe($entity->getEmail());
        }
    }
}
