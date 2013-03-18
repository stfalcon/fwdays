<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zion
 * Date: 18.03.13
 * Time: 15:59
 * To change this template use File | Settings | File Templates.
 */

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class MailAdminController extends CRUDController
{

    public function adminSendAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_success');


            // redirect to edit mode
            return new RedirectResponse($this->admin->generateUrl('list'));
        }


        if ($object->getId()) {


            $em = $this->get('doctrine')->getEntityManager('default');
            $mailer = $this->get('mailer');

            $mail = $object;
            $users = $em->getRepository('ApplicationUserBundle:User')->getAdmins();

            foreach ($users as $user) {
                $text = $mail->replace(
                    array(
                        '%fullname%' => $user->getFullname(),
                        '%user_id%' => $user->getId(),
                    )
                );

                $message = \Swift_Message::newInstance()
                    ->setSubject($mail->getTitle())
                    // @todo refact
                    ->setFrom('orgs@fwdays.com', 'Frameworks Days')
                    ->setTo($user->getEmail())
                    ->setBody($text, 'text/html');

                if (!$mailer->send($message)) {
                    $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_success');
                    return new RedirectResponse($this->admin->generateUrl('list'));
                }
            }
        }
        $this->get('session')->setFlash('sonata_flash_success', 'flash_edit_success');
        return new RedirectResponse($this->admin->generateUrl('list'));

    }

    private function getContainer()
    {
    }
}