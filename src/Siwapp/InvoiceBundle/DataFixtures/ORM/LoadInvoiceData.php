<?php
namespace Siwapp\InvoiceBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Siwapp\InvoiceBundle\Entity\Invoice;
use SIwapp\InvoiceBundle\SiwappInvoiceBundle;
use Symfony\Component\Yaml\Parser;
use Doctrine\Common\Util\Inflector;
use Doctrine\Common\Persistence\ObjectManager;

class LoadInvoiceData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $yaml = new Parser();
        $bpath = $this->container->get('kernel')->getBundle('SiwappInvoiceBundle')->getPath();
        $value = $yaml->parse(file_get_contents($bpath.'/DataFixtures/invoices.yml'));

        foreach ($value['Invoice'] as $ref => $values) {
            $invoice = new Invoice();
            foreach ($values as $fname => $fvalue) {
                if (in_array($fname, ['Series'])) {
                    $fvalue = $manager->merge($this->getReference($fvalue));
                }
                elseif (in_array($fname, ['created_at', 'updated_at'])) {
                    $fvalue = new \DateTime($fvalue);
                }

                $method = Inflector::camelize('set_' . $fname);
                if (is_callable(array($invoice, $method))) {
                    call_user_func(array($invoice, $method), $fvalue);
                }
            }
            $manager->persist($invoice);
            $manager->flush();
            $this->addReference($ref, $invoice);
        }

        foreach ($value['InvoiceItem'] as $ref => $values) {
            $item = $this->getReference($values['Item']);
            $invoice = $this->getReference($values['Invoice']);
            $invoice->addItem($item);
            $manager->persist($invoice);
            $manager->flush();
        }

        foreach ($value['InvoicePayment'] as $ref => $values) {
            $payment = $this->getReference($values['Payment']);
            $invoice = $this->getReference($values['Invoice']);
            $invoice->addPayment($payment);
            $manager->persist($invoice);
            $manager->flush();
        }
    }

    public function getOrder()
    {
        return '2';
    }
}
