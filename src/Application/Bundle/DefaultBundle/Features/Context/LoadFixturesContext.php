<?php

namespace Application\Bundle\DefaultBundle\Features\Context;

use Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Behat\Behat\Context\BehatContext;
/**
 * Provides some steps/methods which are useful for recursive loading Depended Fixtures
 */
class LoadFixturesContext extends BehatContext
{
    /**
     * Load a data fixture class.
     *
     * @param \Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader $loader
     * @param string $className or array
     */
    public function loadFixtureClass($loader, $className)
    {
        if (is_array($className)) {
            foreach ($className as $class) {
                $this->loadFixtureClass($loader, $class);
            }
        } else {

            $fixture = new $className();

            if ($loader->hasFixture($fixture)) {
                unset($fixture);
                return;
            }
            if ($fixture instanceof DependentFixtureInterface) {

                foreach ($fixture->getDependencies() as $dependency) {
                    $this->loadFixtureClass($loader, $dependency);
                }
            }
            $loader->addFixture($fixture);
        }
    }

}
