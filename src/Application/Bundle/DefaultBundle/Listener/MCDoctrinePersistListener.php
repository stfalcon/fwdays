<?php
namespace Application\Bundle\DefaultBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use FOS\UserBundle\Model\UserInterface;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * Doctrine PostPresist listener for mailChimp service
 *
 * @param service $mailChimp
 */
class MCDoctrinePersistListener
{
    //mailChimp service
    private $mailChimp;

    public function __construct($mailChimp)
    {
        $this->mailChimp = $mailChimp;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        /**
         * Create new group with subgroups
         * in mailChimp account
         * when new event added
         *
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

        /**
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
}
