<?php

namespace Application\Bundle\NewsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApplicationNewsBundle extends Bundle
{

    public function getParent()
    {
        return 'StfalconNewsBundle';
    }    
}
