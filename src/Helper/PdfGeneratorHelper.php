<?php

namespace App\Helper;

use App\Entity\Ticket;
use App\Service\SvgToJpg;
use App\Traits\RouterTrait;
use Endroid\QrCode\Exceptions\ImageFunctionFailedException;
use Endroid\QrCode\Exceptions\ImageFunctionUnknownException;
use Endroid\QrCode\QrCode;
use League\Flysystem\Filesystem;
use Mpdf\Mpdf;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * PdfGeneratorHelper.
 */
class PdfGeneratorHelper
{
    use RouterTrait;

    private $templating;
    private $qrCode;
    private $projectDir;
    private $svgToJpgService;
    private $filesystem;
    private $vichUploader;

    /**
     * @param Environment            $templating      Twig
     * @param QrCode                 $qrCode          QrCode generator
     * @param string                 $projectDir
     * @param SvgToJpg               $svgToJpgService
     * @param Filesystem             $eventFlySystem
     * @param PropertyMappingFactory $vichUploader
     */
    public function __construct(Environment $templating, QrCode $qrCode, string $projectDir, SvgToJpg $svgToJpgService, Filesystem $eventFlySystem, PropertyMappingFactory $vichUploader)
    {
        $this->templating = $templating;
        $this->qrCode = $qrCode;
        $this->projectDir = $projectDir;
        $this->svgToJpgService = $svgToJpgService;
        $this->filesystem = $eventFlySystem;
        $this->vichUploader = $vichUploader;
    }

    /**
     * @param Ticket $ticket
     * @param string $html
     *
     * @return string
     *
     * @throws \Mpdf\MpdfException
     */
    public function generatePdfFile(Ticket $ticket, string $html)
    {
        $constructorArgs = [
            'mode' => 'BLANK',
            'format' => [87, 151],
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
            'margin_header' => 2,
            'margin_footer' => 2,
            'tempDir' => '/tmp',
        ];

        $mPDF = new Mpdf($constructorArgs);
        $mPDF->AddFontDirectory(\realpath($this->projectDir.'/public/fonts/').'/');

        $mPDF->fontdata['fwdays'] = ['R' => 'FwDaysTicket-Font.ttf'];
        // phpcs:disable Zend.NamingConventions.ValidVariableName.NotCamelCaps
        $mPDF->sans_fonts[] = 'fwdays';
        $mPDF->available_unifonts[] = 'fwdays';
        $mPDF->default_available_fonts[] = 'fwdays';
        // phpcs:enable

        $mPDF->SetDisplayMode('fullpage');
        $mPDF->WriteHTML($html);

        return $mPDF->Output($ticket->generatePdfFilename(), 'S');
    }

    /**
     * @param Ticket $ticket
     *
     * @return string
     *
     * @throws ImageFunctionFailedException
     * @throws ImageFunctionUnknownException
     */
    public function getTicketQrCode(Ticket $ticket)
    {
        $url = $this->router->generate(
            'event_ticket_registration',
            [
                'ticket' => $ticket->getId(),
                'hash' => $ticket->getHash(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->qrCode->setText($url);
        $this->qrCode->setSize(105);
        $this->qrCode->setPadding(0);

        return $this->qrCode->get();
    }

    /**
     * @param Ticket $ticket
     *
     * @return string
     *
     * @throws ImageFunctionFailedException
     * @throws ImageFunctionUnknownException
     */
    public function generateHTML(Ticket $ticket)
    {
        $twig = $this->templating;

        $qrCode = $this->getTicketQrCode($ticket);
        $qrCodeBase64 = \base64_encode($qrCode);
        $templateContent = $twig->load('Ticket/_new_pdf.html.twig');

        $event = $ticket->getEvent();
        $fieldFileName = $event->getSmallLogo() ? 'smallLogoFile' : 'logoFile';

        try {
            $path = $this->vichUploader->fromField($event, $fieldFileName);
            $fileName = $event->getSmallLogo() ?: $event->getLogo();
            if (null !== $path && $this->filesystem->has($fileName)) {
                $fileName = $path->getUriPrefix().'/'.$fileName;
                $imageData = $this->svgToJpgService->convert($fileName);
            } else {
                $imageData = null;
            }
            $base64EventSmallLogo = \base64_encode($imageData);
        } catch (\Exception $e) {
            $base64EventSmallLogo = '';
        }
        $content = \file_get_contents('build/img/email/circle_left.png');
        $base64CircleLeftImg = \is_string($content) ? \base64_encode($content) : '';
        $content = \file_get_contents('build/img/email/circle_right.png');
        $base64CircleRightImg = \is_string($content) ? \base64_encode($content) : '';

        return $templateContent->render(
            [
                'ticket' => $ticket,
                'qrCodeBase64' => $qrCodeBase64,
                'event_logo' => $base64EventSmallLogo,
                'circle_left' => $base64CircleLeftImg,
                'circle_right' => $base64CircleRightImg,
            ]
        );
    }
}
