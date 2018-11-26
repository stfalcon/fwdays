<?php

namespace Stfalcon\Bundle\EventBundle\Helper;

use Application\Bundle\DefaultBundle\Service\SvgToJpg;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use TFox\MpdfPortBundle\Service\MpdfService;
use Twig_Environment;
use Symfony\Component\Routing\Router;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class PdfGeneratorHelper.
 */
class NewPdfGeneratorHelper
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
     * @param Twig_Environment $templating      Twig
     * @param Router           $router          Router
     * @param QrCode           $qrCode          QrCode generator
     * @param Kernel           $kernel          Kernel
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
        define('_MPDF_SYSTEM_TTFONTS', realpath($this->kernel->getRootDir().'/../web/fonts/').'/');

        $this->mPdfPort->setAddDefaultConstructorArgs(false);

        $constructorArgs = array(
            'mode' => 'BLANK',
            'format' => [87, 151],
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
            'margin_header' => 2,
            'margin_footer' => 2,
        );

        $mPDF = $this->mPdfPort->getMpdf($constructorArgs);

        // Fwdays font settings
        $mPDF->fontdata['fwdays'] = array(
            'R' => 'FwDaysFont-Medium.ttf',
        );
        // phpcs:disable Zend.NamingConventions.ValidVariableName.NotCamelCaps
        $mPDF->sans_fonts[] = 'fwdays';
        $mPDF->available_unifonts[] = 'fwdays';
        $mPDF->default_available_fonts[] = 'fwdays';
        // phpcs:enable
        $mPDF->SetDisplayMode('fullpage');
        $mPDF->WriteHTML($html);
        $pdfFile = $mPDF->Output($ticket->generatePdfFilename(), 'S');

        return $pdfFile;
    }

    /**
     * @param Ticket $ticket
     *
     * @return string
     *
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionFailedException
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionUnknownException
     */
    public function generateHTML(Ticket $ticket)
    {
        $twig = $this->templating;

        $url = $this->router->generate(
            'event_ticket_registration',
            [
                'ticket' => $ticket->getId(),
                'hash' => $ticket->getHash(),
            ],
            true
        );

        $this->qrCode->setText($url);
        $this->qrCode->setSize(105);
        $this->qrCode->setPadding(0);
        $qrCodeBase64 = base64_encode($this->qrCode->get());
        $templateContent = $twig->load('ApplicationDefaultBundle:Ticket:_new_pdf.html.twig');
        $logoFile = $ticket->getEvent()->getSmallLogoFile() ?: $ticket->getEvent()->getLogoFile();
        $imageData = $this->svgToJpgService->convert($logoFile);

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
