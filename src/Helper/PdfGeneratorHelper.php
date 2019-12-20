<?php

namespace App\Helper;

use App\Entity\Ticket;
use App\Service\SvgToJpg;
use Endroid\QrCode\Exceptions\ImageFunctionFailedException;
use Endroid\QrCode\Exceptions\ImageFunctionUnknownException;
use Endroid\QrCode\QrCode;
use League\Flysystem\Filesystem;
use Mpdf\Mpdf;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Twig\Environment;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * PdfGeneratorHelper.
 */
class PdfGeneratorHelper
{
    private $templating;
    private $router;
    private $qrCode;
    private $projectDir;
    private $svgToJpgService;
    private $filesystem;
    private $vichUploader;

    /**
     * @param Environment            $templating      Twig
     * @param Router                 $router          Router
     * @param QrCode                 $qrCode          QrCode generator
     * @param string                 $projectDir
     * @param SvgToJpg               $svgToJpgService
     * @param Filesystem             $filesystem
     * @param PropertyMappingFactory $vichUploader
     */
    public function __construct(Environment $templating, Router $router, QrCode $qrCode, string $projectDir, SvgToJpg $svgToJpgService, Filesystem $filesystem, PropertyMappingFactory $vichUploader)
    {
        $this->templating = $templating;
        $this->router = $router;
        $this->qrCode = $qrCode;
        $this->projectDir = $projectDir;
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
        $mPDF->AddFontDirectory(\realpath($this->projectDir.'/../public/fonts/').'/');

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
    public function generateHTML(Ticket $ticket)
    {
        $twig = $this->templating;

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
        $qrCodeBase64 = base64_encode($this->qrCode->get());
        $templateContent = $twig->load('AppBundle:Ticket:_new_pdf.html.twig');

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
            $base64EventSmallLogo = base64_encode($imageData);
        } catch (\Exception $e) {
            $base64EventSmallLogo = '';
        }

        try {
            $base64CircleLeftImg = base64_encode(\file_get_contents('assets/img/email/circle_left.png'));
        } catch (\Exception $e) {
            $base64CircleLeftImg = '';
        }
        try {
            $base64CircleRightImg = base64_encode(\file_get_contents('assets/img/email/circle_right.png'));
        } catch (\Exception $e) {
            $base64CircleRightImg = '';
        }

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
