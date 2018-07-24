<?php

namespace Application\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ApplicationUserBundle.
 */
class ApplicationUserBundle extends Bundle
{
    /**
     * @return null|string
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
