<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Tests\Application;

use Setono\SyliusAgeVerificationPlugin\Controller\CriiptoCallbackAction;
use Setono\SyliusAgeVerificationPlugin\Tests\Application\UrlGenerator\DummyUrlGenerator;
use Setono\SyliusAgeVerificationPlugin\UrlGenerator\AuthorizationUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                $redirectUri = $_ENV['CRIIPTO_REDIRECT_URI'] ?? null;
                if (!is_string($redirectUri) || '' === $redirectUri) {
                    return;
                }

                $container->register(DummyUrlGenerator::class, DummyUrlGenerator::class)
                    ->setArgument('$url', $redirectUri)
                ;

                $container->findDefinition(AuthorizationUrlGenerator::class)
                    ->setArgument('$urlGenerator', new Reference(DummyUrlGenerator::class))
                ;

                $container->findDefinition(CriiptoCallbackAction::class)
                    ->setArgument('$urlGenerator', new Reference(DummyUrlGenerator::class))
                ;
            }
        });
    }

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    public function registerBundles(): iterable
    {
        foreach ($this->getConfigurationDirectories() as $confDir) {
            $bundlesFile = $confDir . '/bundles.php';
            if (false === is_file($bundlesFile)) {
                continue;
            }
            yield from $this->registerBundlesFromFile($bundlesFile);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        foreach ($this->getConfigurationDirectories() as $confDir) {
            $this->loadRoutesConfiguration($routes, $confDir);
        }
    }

    private function loadRoutesConfiguration(RoutingConfigurator $routes, string $confDir): void
    {
        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS);
        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS);
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS);
    }

    /**
     * @return BundleInterface[]
     */
    private function registerBundlesFromFile(string $bundlesFile): iterable
    {
        $contents = require $bundlesFile;
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    /**
     * @return string[]
     */
    private function getConfigurationDirectories(): iterable
    {
        yield $this->getProjectDir() . '/config';
    }
}
