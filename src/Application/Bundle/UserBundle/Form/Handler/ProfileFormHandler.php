<?php

namespace Application\Bundle\UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\ProfileFormHandler as BaseProfileFormHandler;
use FOS\UserBundle\Model\UserInterface;

class ProfileFormHandler extends BaseProfileFormHandler
{

    public function process(UserInterface $user)
    {
        $this->form->setData($user);

        if ('POST' == $this->request->getMethod()) {
            $this->form->bindRequest($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }

            // Reloads the user to reset its username. This is needed when the
            // username or password have been changed to avoid issues with the
            // security layer.
            $this->userManager->reloadUser($user);
        }

        return false;
    }

}
