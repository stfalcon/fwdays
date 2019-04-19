<?php

namespace Stfalcon\Bundle\EventBundle\Helper;

use Application\Bundle\DefaultBundle\Service\SvgToJpg;
use League\Flysystem\Filesystem;
use Mpdf\Mpdf;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Twig_Environment;
use Symfony\Component\Routing\Router;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpKernel\Kernel;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

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
     * @var SvgToJpg
     */
    protected $svgToJpgService;

    private $filesystem;
    private $vichUploader;

    /**
     * Constructor.
     *
     * @param Twig_Environment       $templating      Twig
     * @param Router                 $router          Router
     * @param QrCode                 $qrCode          QrCode generator
     * @param Kernel                 $kernel          Kernel
     * @param SvgToJpg               $svgToJpgService
     * @param Filesystem             $filesystem
     * @param PropertyMappingFactory $vichUploader
     */
    public function __construct($templating, $router, $qrCode, $kernel, $svgToJpgService, $filesystem, $vichUploader)
    {
        $this->templating = $templating;
        $this->router = $router;
        $this->qrCode = $qrCode;
        $this->kernel = $kernel;
        $this->svgToJpgService = $svgToJpgService;
        $this->filesystem = $filesystem;
        $this->vichUploader = $vichUploader;
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
        $mPDF->AddFontDirectory(realpath($this->kernel->getRootDir().'/../web/fonts/').'/');

        $mPDF->fontdata['fwdays'] = ['R' => 'FwDaysFont-Medium.ttf'];
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

        $event = $ticket->getEvent();
        $fieldFileName = $event->getSmallLogo() ? 'smallLogoFile' : 'logoFile';
        $path = $this->vichUploader->fromField($event, $fieldFileName);
        $fileName = $event->getSmallLogo() ?: $event->getLogo();
        if ($this->filesystem->has($fileName)) {
            $fileName = $path->getUriPrefix().'/'.$fileName;
            $imageData = $this->svgToJpgService->convert($fileName);
        } else {
            $imageData = null;
        }

        $base64EventSmallLogo = base64_encode($imageData);
        $base64CircleLeftImg = base64_encode(\file_get_contents('assets/img/email/circle_left.png'));
        $base64CircleRightImg = base64_encode(\file_get_contents('assets/img/email/circle_right.png'));

        $body = $templateContent->render(
            [
                'ticket' => $ticket,
                'qrCodeBase64' => $qrCodeBase64,
                'event_logo' => $base64EventSmallLogo,
                'circle_left' => $base64CircleLeftImg,
                'circle_right' => $base64CircleRightImg,
            ]
        );

        return $body;
    }
}
