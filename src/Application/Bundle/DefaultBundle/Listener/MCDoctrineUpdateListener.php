<?php
namespace Application\Bundle\DefaultBundle\Listener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\UserBundle\Model\UserInterface;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * Doctrine PreUpdate listener for mailChimp service
 *
 * @param service $mailChimp
 */
class MCDoctrineUpdateListener
{
    //mailChimp service
    private $mailChimp;

    public function __construct($mailChimp)
    {
        $this->mailChimp = $mailChimp;
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        // move user to paid group when he pay for the ticket
        if ($entity instanceof Payment) {
            $list = $this->mailChimp->getList();
            $mergeVars = array('GROUPINGS' => array(
                array(
                    'name' => $entity->getTicket()->getEvent()->getSlug(),
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
                $list->setEmail($entity->getEmail());
                $list->setUpdateExisting(true);
                $list->Subscribe();

            // update some fields in mailChimp when user update profile
            } else {
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
}