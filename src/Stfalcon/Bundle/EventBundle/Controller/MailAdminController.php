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
 *
 * @package Stfalcon\Bundle\EventBundle\Controller
 */
class MailAdminController extends CRUDController
{
    /**
     * Action for Behat test. Send mail to user
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
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function adminSendAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());

        $mail = $this->admin->getObject($id);

        if (!$mail) {
            $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_success');

            return new RedirectResponse($this->admin->generateUrl('list')); // Redirect to edit mode
        }

        if ($mail->getId()) {
            /** @var $em \Doctrine\ORM\EntityManager */
            $em     = $this->get('doctrine')->getEntityManager('default');
            $mailer = $this->get('mailer');

            $users = $em->getRepository('ApplicationUserBundle:User')->getAdmins();

            foreach ($users as $user) {
                if (!$mailer->send(StfalconMailerHelper::formatMessage($user, $mail))) {
                    $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_success');

                    return new RedirectResponse($this->admin->generateUrl('list'));
                }
            }
        }

        $this->get('session')->setFlash('sonata_flash_success', 'flash_edit_success');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
