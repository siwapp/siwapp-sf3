<?php
namespace Siwapp\CoreBundle\Extension;

/**
 * This is a Twig extension with methods common to all the application.
 */
class SiwappCoreTwigExtension extends \Twig_Extension
{
    protected $bundles;

    public function __construct(array $bundles) {
        $this->bundles = $bundles;
    }

    public function getName()
    {
        return 'siwapp_core_twig_extension';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('menu_active_tab', [$this, 'menu_active_tab']),
        );
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('siwapp_version', [$this, 'version']),
            new \Twig_SimpleFunction('bundle_exists', [$this, 'bundleExists']),
        ];
    }

    /**
     * @param string $routename Name of the route
     * @param string $prefix Prefix to test
     * @return string "active" if $routename is prefixed by $prefix
     * Ex: dashboard_index, dashboard_ => "active"
     * Ex: dashboard_index, invoice_   => ""
     */
    public function menu_active_tab($routename, $prefix)
    {
        return (strpos($routename, $prefix) === 0 ? "active" : "");
    }

    /**
     * @param string $bundle
     *
     * @return bool
     */
    public function bundleExists($bundle)
    {
        return array_key_exists($bundle, $this->bundles);
    }

    public function version(): string
    {
        return \Siwapp\CoreBundle\SiwappCoreBundle::version;
    }
}
