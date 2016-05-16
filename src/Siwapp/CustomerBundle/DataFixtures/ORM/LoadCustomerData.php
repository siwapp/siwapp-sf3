<?php
namespace Siwapp\CustomerBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Util\Inflector;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Parser;

use Siwapp\CustomerBundle\Entity\Customer;

class LoadCustomerData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $yaml = new Parser();
        $bpath = $this->container->get('kernel')->getBundle('SiwappCustomerBundle')->getPath();
        $value = $yaml->parse(file_get_contents($bpath.'/DataFixtures/customers.yml'));

        foreach ($value['Customer'] as $ref => $values) {
            $customer = new Customer();
            foreach ($values as $fname => $fvalue) {
                $method = 'set'.Inflector::camelize($fname);
                if (is_callable(array($customer, $method))) {
                    call_user_func(array($customer, $method), $fvalue);
                }
            }
            $manager->persist($customer);
            $manager->flush();
            $this->addReference($ref, $customer);
        }

    }

    public function getOrder()
    {
        return '1';
    }
}
