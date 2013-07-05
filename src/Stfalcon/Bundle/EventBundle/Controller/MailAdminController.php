<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;

use Symfony\Component\Console\Input\ArrayInput,
    Symfony\Component\Console\Output\ConsoleOutput,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper;

/**
 * Class MailAdminController
 */
class MailAdminController extends CRUDController
{
    /**
     * Send messages for all users in mail queue (using console command)
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function userSendAction()
    {
        if (!in_array($this->get('kernel')->getEnvironment(), array('test'))) {
            throw new NotFoundHttpException("Page not found");
        }

        $command = $this->get('user_mail_command_service');
        $output  = new ConsoleOutput();

        $arguments = array(
            '--amount' => '5',
        );

        $input = new ArrayInput($arguments);
        $command->run($input, $output);

        return new Response('complete');
    }

    /**
     * Send messages only for admins
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
             * @var \Doctrine\ORM\EntityManager $em
             * @var \Swift_Mailer $mailer
             * @var \Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper $mailerHelper
             */
            $em = $this->get('doctrine')->getEntityManager('default');
            $mailer = $this->get('mailer');
            $mailerHelper = $this->get('stfalcon_event.mailer_helper');

            $users = $em->getRepository('ApplicationUserBundle:User')->getAdmins();

            foreach ($users as $user) {
                if (!$mailer->send($mailerHelper->formatMessage($user, $mail))) {
                    $session->getFlashBag()->add('sonata_flash_error', 'При отправлении почтовой рассылки администраторам случилась ошибка');

                    return new RedirectResponse($this->admin->generateUrl('list'));
                }
            }
        }

        $this->get('session')->setFlash('sonata_flash_success', 'Почтовая рассылка администраторам успешно выполнена');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
