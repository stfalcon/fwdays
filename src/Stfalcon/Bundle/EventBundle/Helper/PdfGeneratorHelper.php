<?php

namespace Stfalcon\Bundle\EventBundle\Helper;

use Application\Bundle\DefaultBundle\Service\SvgToJpg;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use TFox\MpdfPortBundle\Service\MpdfService;
use Twig_Environment;
use Symfony\Component\Routing\Router;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class PdfGeneratorHelper.
 */
class PdfGeneratorHelper
{
    /**
     * @var Twig_Environment
     */
    protected $templating;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var QrCode
     */
    protected $qrCode;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var MpdfService
     */
    protected $mPdfPort;

    /**
     * @var SvgToJpg
     */
    protected $svgToJpgService;

    /**
     * Constructor.
     *
     * @param Twig_Environment $templating
     * @param Router           $router
     * @param QrCode           $qrCode
     * @param Kernel           $kernel
     * @param MpdfService      $mPdfPort
     * @param SvgToJpg         $svgToJpgService
     */
    public function __construct($templating, $router, $qrCode, $kernel, $mPdfPort, $svgToJpgService)
    {
        $this->templating = $templating;
        $this->router = $router;
        $this->qrCode = $qrCode;
        $this->kernel = $kernel;
        $this->mPdfPort = $mPdfPort;
        $this->svgToJpgService = $svgToJpgService;
    }

    /**
     * Generate PDF-file of ticket.
     *
     * @param Ticket $ticket
     * @param string $html
     *
     * @return mixed
     */
    public function generatePdfFile(Ticket $ticket, $html)
    {
        // Override default fonts directory for mPDF
        define('_MPDF_SYSTEM_TTFONTS', realpath($this->kernel->getRootDir().'/../web/fonts/open-sans/').'/');

        $this->mPdfPort->setAddDefaultConstructorArgs(false);

        $constructorArgs = array(
            'mode' => 'BLANK',
            'format' => 'A5-L',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
        );

        $mPDF = $this->mPdfPort->getMpdf($constructorArgs);

        // Open Sans font settings
        $mPDF->fontdata['opensans'] = array(
            'R' => 'OpenSans-Regular.ttf',
            'B' => 'OpenSans-Bold.ttf',
            'I' => 'OpenSans-Italic.ttf',
            'BI' => 'OpenSans-BoldItalic.ttf',
        );
        $mPDF->sans_fonts[] = 'opensans';
        $mPDF->available_unifonts[] = 'opensans';
        $mPDF->available_unifonts[] = 'opensansI';
        $mPDF->available_unifonts[] = 'opensansB';
        $mPDF->available_unifonts[] = 'opensansBI';
        $mPDF->default_available_fonts[] = 'opensans';
        $mPDF->default_available_fonts[] = 'opensansI';
        $mPDF->default_available_fonts[] = 'opensansB';
        $mPDF->default_available_fonts[] = 'opensansBI';

        $mPDF->SetDisplayMode('fullpage');
        $mPDF->WriteHTML($html);
        $pdfFile = $mPDF->Output($ticket->generatePdfFilename(), 'S');

        return $pdfFile;
    }

    /**
     * Create HTML template for ticket invitation.
     *
     * @param Ticket $ticket
     *
     * @return string
     */
    public function generateHTML(Ticket $ticket)
    {
        $twig = $this->templating;

        $url = $this->router->generate(
            'event_ticket_registration',
            array(
                'ticket' => $ticket->getId(),
                'hash' => $ticket->getHash(),
            ),
            true
        );

        $this->qrCode->setText($url);
        $this->qrCode->setSize(105);
        $this->qrCode->setPadding(0);
        $qrCodeBase64 = base64_encode($this->qrCode->get());
        $templateContent = $twig->load('ApplicationDefaultBundle:Ticket:_pdf.html.twig');

        $logoFile = $ticket->getEvent()->getSmallLogoFile() ?: $ticket->getEvent()->getLogoFile();
        $imageData = $this->svgToJpgService->convert($logoFile, '#FFFFFF');
        $base64EventSmallLogo = base64_encode($imageData);

        $body = $templateContent->render(
            [
                'ticket' => $ticket,
                'qrCodeBase64' => $qrCodeBase64,
                'path' => realpath($this->kernel->getRootDir().'/../web').'/',
                'event_logo' => $base64EventSmallLogo,
            ]
        );

        return $body;
    }
}
