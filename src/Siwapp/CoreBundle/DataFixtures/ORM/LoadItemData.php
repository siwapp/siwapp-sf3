<?php
namespace Siwapp\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Siwapp\CoreBundle\Entity\Item;


use Symfony\Component\Yaml\Parser;
use Doctrine\Common\Util\Inflector;
use Doctrine\Common\Persistence\ObjectManager;

class LoadItemData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $bpath = $this->container->get('kernel')->getBundle('SiwappCoreBundle')->getPath();
        $value = $yaml->parse(file_get_contents($bpath.'/DataFixtures/items.yml'));
        foreach ($value['Item'] as $ref => $values) {
            $item = new Item();
            foreach ($values as $fname => $fvalue) {
                if (in_array($fname, ['Product'])) {
                    $fvalue = $manager->merge($this->getReference($fvalue));
                }
                $method = Inflector::camelize('set_' . $fname);
                if (is_callable(array($item, $method))) {
                    call_user_func(array($item, $method), $fvalue);
                }
            }
            $manager->persist($item);
            $manager->flush();
            $this->addReference($ref, $item);
        }

        foreach ($value['ItemTax'] as $ref => $values) {
            $item = $this->getReference($values['Item']);
            $tax = $this->getReference($values['Tax']);
            $item->addTax($tax);
            $manager->persist($item);
            $manager->flush();
        }

    }

    public function getOrder()
    {
        return '1';
    }
}
