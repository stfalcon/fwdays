<?php

namespace FwDays\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FwDaysUserBundle extends Bundle
{
    public function getParent()
    {
        return 'SonataUserBundle';
    }    
}
