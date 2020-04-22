<?php

declare(strict_types=1);

namespace App\Traits;

use Twig\Environment;

/**
 * TwigTrait.
 */
trait TwigTrait
{
    /** @var Environment */
    protected $twig;

    /**
     * @param Environment $twig
     *
     * @required
     */
    public function setTwigEnvironment(Environment $twig): void
    {
        $this->twig = $twig;
    }
}
