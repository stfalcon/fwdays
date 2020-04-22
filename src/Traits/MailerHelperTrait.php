<?php

declare(strict_types=1);

namespace App\Traits;

use App\Helper\MailerHelper;

/**
 * MailerHelperTrait.
 */
trait MailerHelperTrait
{
    /** @var MailerHelper */
    protected $mailerHelper;

    /**
     * @param MailerHelper $mailerHelper
     *
     * @required
     */
    public function setMailerHelper(MailerHelper $mailerHelper): void
    {
        $this->mailerHelper = $mailerHelper;
    }
}
