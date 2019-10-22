<?php

namespace Application\Bundle\DefaultBundle\Controller;

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
        $command = $this->get('user_mail_command_service');
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
        $mail = $this->admin->getObject($id);
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');
        if (!$mail) {
            $session->getFlashBag()->add('sonata_flash_error', 'Почтовая рассылка не найдена');

            return new RedirectResponse($this->admin->generateUrl('list')); // Redirect to edit mode
        }
        if ($mail->getId()) {
            /**
             * @var \Doctrine\ORM\EntityManager
             * @var \Swift_Mailer                                                 $mailer
             * @var \Application\Bundle\DefaultBundle\Helper\StfalconMailerHelper $mailerHelper
             */
            $em = $this->get('doctrine')->getEntityManager('default');
            $mailer = $this->get('mailer');
            $mailerHelper = $this->get('application.mailer_helper');
            $users = $em->getRepository('ApplicationDefaultBundle:User')->getAdmins();
            $isTestMessage = true;
            $error = false;
            foreach ($users as $user) {
                if (!$mailer->send($mailerHelper->formatMessage($user, $mail, $isTestMessage))) {
                    $error = true;
                }
            }
            if ($error) {
                $session->getFlashBag()->add('sonata_flash_error', 'При отправлении почтовой рассылки администраторам случилась ошибка');

                return new RedirectResponse($this->admin->generateUrl('list'));
            }
        }
        $this->get('session')->getFlashBag()->add('sonata_flash_success', 'Почтовая рассылка администраторам успешно выполнена');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
