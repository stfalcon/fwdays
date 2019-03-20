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

            new Sonata\IntlBundle\SonataIntlBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),

            new Stfalcon\Bundle\EventBundle\StfalconEventBundle(),
            new Stfalcon\Bundle\SponsorBundle\StfalconSponsorBundle(),

            new Application\Bundle\DefaultBundle\ApplicationDefaultBundle(),
            new Application\Bundle\UserBundle\ApplicationUserBundle(),

            new Vich\UploaderBundle\VichUploaderBundle(),
            new Ornicar\GravatarBundle\OrnicarGravatarBundle(),

            new TFox\MpdfPortBundle\TFoxMpdfPortBundle(),
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

    /**
     * Register container configuration
     *
     * @param LoaderInterface $loader
     */
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
