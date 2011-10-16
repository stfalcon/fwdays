<?php

namespace Application\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApplicationUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }    
}
