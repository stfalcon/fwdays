<?php

namespace Application\Bundle\DefaultBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class FixtureWithDir extends AbstractFixture
{
    private $kernelDir;

    public function __construct($kernelDir)
    {
        $this->kernelDir = $kernelDir;
    }

    public function getKernelDir()
    {
        return $this->kernelDir;
    }
    public function load(ObjectManager $manager)
    {
        // TODO: Implement load() method.
    }
}