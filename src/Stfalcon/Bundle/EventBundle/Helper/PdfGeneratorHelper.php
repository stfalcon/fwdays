<?php
namespace Stfalcon\Bundle\EventBundle\Helper;

use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Twig_Environment;
use Symfony\Component\Routing\Router;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpKernel\Kernel;
use Container;

/**
 * Class PdfGeneratorHelper
 */
class PdfGeneratorHelper
{
    /**
     * @var Twig_Environment $templating
     */
    protected $templating;

    /**
     * @var Router $router
     */
    protected $router;

    /**
     * @var QrCode $qrCode
     */
    protected $qrCode;

    /**
     * @var Kernel $kernel
     */
    protected $kernel;

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * Constructor
     *
     * @param Twig_Environment $templating Twig
     * @param Router           $router     Router
     * @param QrCode           $qrCode     QrCode generator
     * @param Kernel           $kernel     Kernel
     * @param Container        $container
     */
    public function __construct($templating, $router, $qrCode, $kernel, $container)
    {
        $this->templating = $templating;
        $this->router = $router;
        $this->qrCode = $qrCode;
        $this->kernel = $kernel;
        $this->container = $container;
    }

    /**
     * Generate PDF-file of ticket
     *
     * @param Ticket $ticket
     * @param string $html
     *
     * @return mixed
     */
    public function generatePdfFile(Ticket $ticket, $html)
    {
        // Override default fonts directory for mPDF
        define('_MPDF_SYSTEM_TTFONTS', realpath($this->kernel->getRootDir() . '/../web/fonts/open-sans/') . '/');

        /** @var \TFox\MpdfPortBundle\Service\MpdfService $mPDFService */
        $mPDFService = $this->container->get('tfox.mpdfport');
        $mPDFService->setAddDefaultConstructorArgs(false);

        $constructorArgs = array(
            'mode'          => 'BLANK',
            'format'        => 'A5-L',
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0
        );

        $mPDF = $mPDFService->getMpdf($constructorArgs);

        // Open Sans font settings
        $mPDF->fontdata['opensans'] = array(
            'R'  => 'OpenSans-Regular.ttf',
            'B'  => 'OpenSans-Bold.ttf',
            'I'  => 'OpenSans-Italic.ttf',
            'BI' => 'OpenSans-BoldItalic.ttf',
        );
        $mPDF->sans_fonts[]              = 'opensans';
        $mPDF->available_unifonts[]      = 'opensans';
        $mPDF->available_unifonts[]      = 'opensansI';
        $mPDF->available_unifonts[]      = 'opensansB';
        $mPDF->available_unifonts[]      = 'opensansBI';
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
     * Create HTML template for ticket invitation
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
                'hash'   => $ticket->getHash()
            ),
            true
        );

        $this->qrCode->setText($url);
        $this->qrCode->setSize(105);
        $this->qrCode->setPadding(0);
        $qrCodeBase64 = base64_encode($this->qrCode->get());
        $templateContent = $twig->loadTemplate('StfalconEventBundle:Ticket:_pdf.html.twig');
        $body = $templateContent->render(array(
                'ticket'       => $ticket,
                'qrCodeBase64' => $qrCodeBase64,
                'path'         => realpath($this->kernel->getRootDir() . '/../web') . '/'
            ));

        return $body;
    }

}
