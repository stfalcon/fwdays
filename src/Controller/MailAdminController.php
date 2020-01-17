<?php

namespace App\Controller;

use App\Entity\Mail;
use App\Entity\User;
use App\Helper\MailerHelper;
use App\Service\TranslatedMailService;
use App\Traits\SessionTrait;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MailAdminController.
 */
class MailAdminController extends CRUDController
{
    use SessionTrait;

    private $mailer;
    private $mailerHelper;
    private $translatedMailService;

    /**
     * @param \Swift_Mailer         $mailer
     * @param MailerHelper          $mailerHelper
     * @param TranslatedMailService $translatedMailService
     */
    public function __construct(\Swift_Mailer $mailer, MailerHelper $mailerHelper, TranslatedMailService $translatedMailService)
    {
        $this->mailer = $mailer;
        $this->mailerHelper = $mailerHelper;
        $this->translatedMailService = $translatedMailService;
    }

    /**
     * Send messages only for admins.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function adminSendAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());
        /** @var Mail $mail */
        $mail = $this->admin->getObject($id);

        if (!$mail instanceof Mail) {
            $this->session->getFlashBag()->add('sonata_flash_error', 'Почтовая рассылка не найдена');

            return new RedirectResponse($this->admin->generateUrl('list')); // Redirect to edit mode
        }

        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository(User::class)->getAdmins();
        $isTestMessage = true;
        $error = false;
        $translatedMails = $this->translatedMailService->getTranslatedMailArray($mail);
        /** @var User $user */
        foreach ($users as $user) {
            if (!isset($translatedMails[$user->getEmailLanguage()]) ||
                !$this->mailer->send($this->mailerHelper->formatMessage($user, $translatedMails[$user->getEmailLanguage()], $isTestMessage))) {
                $error = true;
            }
        }
        if ($error) {
            $this->session->getFlashBag()->add('sonata_flash_error', 'При отправлении почтовой рассылки администраторам случилась ошибка');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $this->session->getFlashBag()->add('sonata_flash_success', 'Почтовая рассылка администраторам успешно выполнена');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
