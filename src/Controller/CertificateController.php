<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\TicketBenefit;
use App\Entity\User;
use App\Helper\PdfGeneratorHelper;
use App\Repository\TicketRepository;
use League\Flysystem\Filesystem;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * CertificateController.
 */
class CertificateController extends AbstractController
{
    /** @var PdfGeneratorHelper */
    private $pdfGeneratorHelper;

    /** @var TicketRepository */
    private $ticketRepository;

    /** @var Filesystem */
    private $eventCertificateFlySystem;
    /** @var PropertyMappingFactory */
    private $vichUploader;

    /**
     * @param PdfGeneratorHelper     $pdfGeneratorHelper
     * @param TicketRepository       $ticketRepository
     * @param Filesystem             $eventCertificateFlySystem
     * @param PropertyMappingFactory $vichUploader
     */
    public function __construct(PdfGeneratorHelper $pdfGeneratorHelper, TicketRepository $ticketRepository, Filesystem $eventCertificateFlySystem, PropertyMappingFactory $vichUploader)
    {
        $this->pdfGeneratorHelper = $pdfGeneratorHelper;
        $this->ticketRepository = $ticketRepository;
        $this->eventCertificateFlySystem = $eventCertificateFlySystem;
        $this->vichUploader = $vichUploader;
    }

    /**
     * @Route("/event/{slug}/certificate/{type}", name="event_certificate_download", requirements={"type": App\Entity\TicketCost::TYPES})
     *
     * @ParamConverter("event", options={"mapping": {"slug": "slug"}})
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param Event  $event
     * @param string $type
     *
     * @return Response
     */
    public function downloadAction(Event $event, string $type): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $ticket = $this->ticketRepository->findOneForEventAndUser($event, $user, $type);

        if (!$ticket instanceof Ticket || !$ticket->isPaid()) {
            return new Response(\sprintf('Вы не оплачивали участие в "%s"', $event->getName()), Response::HTTP_PAYMENT_REQUIRED);
        }
        $ticketBenefit = $event->findTicketBenefitForType($type);

        if (!$ticketBenefit instanceof TicketBenefit) {
            throw new NotFoundHttpException('ticket benefit not founded');
        }

        $path = $this->vichUploader->fromField($ticketBenefit, 'certificateFile');
        $fileName = $ticketBenefit->getCertificate();
        $path = $path->getUriPrefix().'/'.$fileName;

        if (!$this->eventCertificateFlySystem->has($fileName)) {
            throw new FileNotFoundException($path);
        }

        $pdf = $this->pdfGeneratorHelper->loadPdfFromFilename($path);
        if (!$pdf instanceof Mpdf) {
            throw new FileNotFoundException($path);
        }
        $this->pdfGeneratorHelper->addTextToPdf($user->getName(), $pdf, 229, 161);
        $this->pdfGeneratorHelper->addTextToPdf($user->getSurname(), $pdf, 229, 200);

        $newFilename = \sprintf('certificate-%s.pdf', $event->getSlug());

        return new Response(
            $pdf->Output($newFilename, Destination::STRING_RETURN),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => \sprintf('attach; filename="%s"', $newFilename),
            ]
        );
    }
}
