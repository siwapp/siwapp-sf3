<?php
namespace Siwapp\ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Parser;
use Doctrine\Common\Persistence\ObjectManager;

use Siwapp\ProductBundle\Entity\Product;

class LoadProductData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $yaml = new Parser();
        // TODO: find a way of obtainin Bundle's path with the help of $this->container
        $bpath = $this->container->get('kernel')->getBundle('SiwappProductBundle')->getPath();
        $value = $yaml->parse(file_get_contents($bpath.'/DataFixtures/products.yml'));
        foreach ($value['Product'] as $ref => $values) {
            $product = new Product();
            foreach ($values as $fname => $fvalue) {
                $method = Inflector::camelize('set_' . $fname);
                if (is_callable(array($product, $method))) {
                    call_user_func(array($product, $method), $fvalue);
                }
            }
            $manager->persist($product);
            $manager->flush();
            $this->addReference($ref, $product);
        }
    }

    public function getOrder()
    {
        return '0';
    }
}
