<?php
namespace Stfalcon\Bundle\EventBundle\Helper;

use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Twig_Environment;

/**
 * Class StfalconPdfGenerator
 */
class StfalconPdfGenerator
{
    /**
     * @var EngineInterface $templating
     */
    protected $templating;

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * Constructor
     *
     * @param Twig_Environment $templating Twig
     * @param                  $container  Container
     */
    public function __construct(Twig_Environment $templating, $container)
    {
        $this->templating = $templating;
        $this->container = $container;
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

        $url = $this->container->get('router')->generate(
            'event_ticket_check',
            array(
                'ticket' => $ticket->getId(),
                'hash'   => $ticket->getHash()
            ),
            true
        );

        $qrCode = $this->container->get('stfalcon_event.qr_code');
        $qrCode->setText($url);
        $qrCode->setSize(105);
        $qrCode->setPadding(0);
        $qrCodeBase64 = base64_encode($qrCode->get());
        $templateContent = $twig->loadTemplate('StfalconEventBundle:Ticket:show_pdf.html.twig');
        $body = $templateContent->render(array(
                'ticket'       => $ticket,
                'qrCodeBase64' => $qrCodeBase64,
                'path'         => realpath($this->container->get('kernel')->getRootDir() . '/../web') . '/'
            ));

        return $body;
    }

    /**
     * Generate PDF-file of ticket
     *
     * @param string $html       HTML to generate pdf
     * @param string $outputFile Name of output file
     *
     * @return mixed
     */
    public function generatePdfFile($html, $outputFile)
    {
        // Override default fonts directory for mPDF
        define('_MPDF_SYSTEM_TTFONTS', realpath($this->container->get('kernel')->getRootDir() . '/../web/fonts/open-sans/') . '/');

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
        $pdfFile = $mPDF->Output($outputFile, 'S');

        return $pdfFile;
    }
}
