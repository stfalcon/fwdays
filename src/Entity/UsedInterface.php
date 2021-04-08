<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * UsedInterface.
 */
interface UsedInterface
{
    /**
     * @return bool
     */
    public function isUsed(): bool;

    /**
     * @param bool $used
     *
     * @return mixed
     */
    public function setUsed(bool $used);
}
