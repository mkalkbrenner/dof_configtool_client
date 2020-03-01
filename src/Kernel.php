<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().DIRECTORY_SEPARATOR.'config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir().DIRECTORY_SEPARATOR.'config';

        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        if (isset($_SERVER['PROGRAM_DATA'])) {
            $cache_dir = $_SERVER['PROGRAM_DATA'].DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$this->environment;
            $filesystem = new Filesystem();
            $version = '';
            $version_txt = $_SERVER['PROGRAM_DATA'].DIRECTORY_SEPARATOR.'version.txt';
            if ($filesystem->exists($version_txt)) {
                $version = file_get_contents($version_txt);
            }
            if (DOFCTC_VERSION !== $version) {
                $filesystem->remove($cache_dir);
                $filesystem->dumpFile($version_txt, DOFCTC_VERSION);
            }
            if (!$filesystem->exists(ini_get('session.save_path'))) {
                $filesystem->mkdir(ini_get('session.save_path'));
            }
            if (!$filesystem->exists($_SERVER['PROGRAM_DATA'].DIRECTORY_SEPARATOR.'tmp')) {
                $filesystem->mkdir($_SERVER['PROGRAM_DATA'].DIRECTORY_SEPARATOR.'tmp');
            }
            return $cache_dir;
        }
        return parent::getCacheDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        if (isset($_SERVER['PROGRAM_DATA'])) {
            return $_SERVER['PROGRAM_DATA'].DIRECTORY_SEPARATOR.'log';
        }
        return parent::getLogDir();
    }
}
