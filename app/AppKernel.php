<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class AppKernel
 */
class AppKernel extends Kernel
{
    /**
     * Register bundles
     *
     * @return array|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new JMS\AopBundle\JMSAopBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),

            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            new FOS\UserBundle\FOSUserBundle(),

            new Knp\Bundle\MenuBundle\KnpMenuBundle(),

            new Exporter\Bridge\Symfony\Bundle\SonataExporterBundle(),
            new Sonata\IntlBundle\SonataIntlBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),

            new Application\Bundle\DefaultBundle\ApplicationDefaultBundle(),

            new Oneup\FlysystemBundle\OneupFlysystemBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),

            new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),

            new Accord\MandrillSwiftMailerBundle\AccordMandrillSwiftMailerBundle(),

            new Lexik\Bundle\TranslationBundle\LexikTranslationBundle(),
            new Ibrows\SonataTranslationBundle\IbrowsSonataTranslationBundle(),
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),

            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),

            new SunCat\MobileDetectBundle\MobileDetectBundle(),

            new Liip\ImagineBundle\LiipImagineBundle(),
            new Maxmind\Bundle\GeoipBundle\MaxmindGeoipBundle(),

            new CMEN\GoogleChartsBundle\CMENGoogleChartsBundle(),
            new Snc\RedisBundle\SncRedisBundle(),

        );

        if (in_array($this->getEnvironment(), ['prod', 'stag'], true)) {
            $bundles[] = new Sentry\SentryBundle\SentryBundle();
        }

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }
        if ($this->getEnvironment() === 'test') {
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }
    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }
    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * @return string
     */
    protected function getContainerBaseClass()
    {
        if ('test' === $this->getEnvironment()) {
            return '\PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer';
        }

        return parent::getContainerBaseClass();
    }
}
