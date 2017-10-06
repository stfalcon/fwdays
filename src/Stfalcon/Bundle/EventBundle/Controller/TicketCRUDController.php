<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sonata\AdminBundle\Controller\CRUDController;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketCRUDController extends CRUDController
{
    /**
     * @param $id
     * @return RedirectResponse
     */
    public function removeTicketFromPaymentAction($id)
    {
        /** @var Ticket $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        if ($object instanceof Ticket && $payment = $object->getPayment()) {
            $payment->removePaidTicket($object);
        }
        $this->get('doctrine.orm.default_entity_manager')->flush();
        $this->addFlash('sonata_flash_success', 'Removed successfully');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}