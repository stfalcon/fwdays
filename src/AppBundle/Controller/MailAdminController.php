<?php

namespace App\Controller;

use App\Command\StfalconMailerCommand;
use App\Entity\Mail;
use App\Entity\User;
use App\Helper\StfalconMailerHelper;
use App\Service\TranslatedMailService;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class MailAdminController.
 */
class MailAdminController extends CRUDController
{
    /**
     * Send messages for all users in mail queue (using console command).
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function userSendAction()
    {
        if (!\in_array($this->get('kernel')->getEnvironment(), ['test'])) {
            throw new NotFoundHttpException('Page not found');
        }
        $command = $this->get(StfalconMailerCommand::class);
        $output = new ConsoleOutput();
        $arguments = [
            '--amount' => '5',
        ];
        $input = new ArrayInput($arguments);
        $command->run($input, $output);

        return new Response('complete');
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
        $session = $this->get('session');
        if (!$mail instanceof Mail) {
            $session->getFlashBag()->add('sonata_flash_error', 'Почтовая рассылка не найдена');

            return new RedirectResponse($this->admin->generateUrl('list')); // Redirect to edit mode
        }

        $em = $this->getDoctrine()->getManager();
        $mailer = $this->get('mailer');
        $mailerHelper = $this->get(StfalconMailerHelper::class);
        $users = $em->getRepository(User::class)->getAdmins();
        $isTestMessage = true;
        $error = false;
        $translatedMailService = $this->get(TranslatedMailService::class);
        $translatedMails = $translatedMailService->getTranslatedMailArray($mail);
        foreach ($users as $user) {
            if (!isset($translatedMails[$user->getEmailLanguage()]) ||
                !$mailer->send($mailerHelper->formatMessage($user, $translatedMails[$user->getEmailLanguage()], $isTestMessage))) {
                $error = true;
            }
        }
        if ($error) {
            $session->getFlashBag()->add('sonata_flash_error', 'При отправлении почтовой рассылки администраторам случилась ошибка');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $this->get('session')->getFlashBag()->add('sonata_flash_success', 'Почтовая рассылка администраторам успешно выполнена');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
