<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * ValidatorTrait.
 */
trait ValidatorTrait
{
    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ValidatorInterface $validator
     *
     * @required
     */
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }
}
