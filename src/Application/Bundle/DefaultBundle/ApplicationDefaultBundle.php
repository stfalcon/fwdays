<?php

namespace Application\Bundle\DefaultBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Stepan Tanasiychuk <ceo@stfalcon.com>
 */
class ApplicationDefaultBundle extends Bundle
{
    /**
     * @return null|string
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
