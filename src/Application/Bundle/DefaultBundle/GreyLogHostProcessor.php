<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 11/20/12
 * Time: 1:25 PM
 * To change this template use File | Settings | File Templates.
 */
namespace Application\Bundle\DefaultBundle;

class GreyLogHostProcessor
{

    public function processRecord(array $record)
    {
        $record['extra']['host'] = $_SERVER['HTTP_HOST'];

        return $record;
    }
}