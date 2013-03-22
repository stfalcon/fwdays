<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     */
    public function userSendAction()
    {
        if(!in_array($this->get('kernel')->getEnvironment(), array('test'))) {
            throw new NotFoundHttpException("Page not found");
        }

       $command = $this->get('user_mail_command_service');
       $output = new ConsoleOutput();

        $arguments = array(
            '--amount'  => '5',
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


            // redirect to edit mode
            return new RedirectResponse($this->admin->generateUrl('list'));
        }


        if ($mail->getId()) {


            $em = $this->get('doctrine')->getEntityManager('default');
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

    private function getContainer ()
    {
    }
}