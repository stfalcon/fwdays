<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Application mailchimp service webhook controller
 *
 */
class mailChimpController extends Controller
{

    /**
     * @Route("/mailchimp-postdata", name="mail_chimp_post")
     */
    public function indexAction()
    {
        $request = Request::createFromGlobals();
        // получили хук с отпиской
        if ($request->query->get('type') == 'unsubscribe') {
            echo "111!!!---!!1 TYPE IS";
            // получаем емейл отписавшегося
            $data = $request->query->get('data');
            $userMail = $data['email'];

            // снимаем подписку локально
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('ApplicationUserBundle:User')
                ->findBy(array('email' => $userMail));

            $user->setSubscribe(false);
            $em->persist($user);
            $em->flush();

            // подписываем сново
            $list = $this->get('mailchimp')->getList();
            $list->setDoubleOptin(false);

            $mergeVars = array(
                'FNAME' => $user->getFullname(),
                'SUBSCRIBE' => '0',
            );

            $list->setMerge($mergeVars);
            $list->setUpdateExisting(true);
            $list->Subscribe($user->getEmail());

            //помещаем в нужные групы
            $tickets = $em->getRepository('StfalconEventBundle:Ticket')
                ->findTicketsOfActiveEventsForUser($user);

            foreach ($tickets as $ticket) {
                $mergeVars = array('GROUPINGS' => array(
                    array(
                        'name' => $ticket->getEvent()->getSlug(),
                        'groups' => Payment::STATUS_PENDING
                    )));
                $list->setMerge($mergeVars);
                $list->MergeVars();
                $list->setEmail($user()->getEmail());
                $list->updateMember();
            }
        }
        // если не отдавать 200, mailchimp не будет слать
        return new Response();
    }

}