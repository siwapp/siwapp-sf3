<?php

namespace Siwapp\InvoiceBundle\Tests\Entity;

use Siwapp\InvoiceBundle\Entity\Invoice;
use Siwapp\CoreBundle\Entity\Item;

class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckAmounts()
    {
        $invoice = new Invoice;
        $item = new Item;
        $item->setUnitaryCost(80);
        $item->setQuantity(1);
        $invoice->addItem($item);
        // test amounts calculation
        $invoice->checkAmounts();
        $this->assertEquals(80, $invoice->getBaseAmount());
        $this->assertEquals(80, $invoice->getNetAmount());
        $this->assertEquals(0, $invoice->getTaxAmount());
        $this->assertEquals(80, $invoice->getGrossAmount());
        // Add the same item again.
        $invoice->addItem($item);
        $invoice->checkAmounts();
        $this->assertEquals(0, $invoice->getTaxAmount());
        $this->assertEquals(160, $invoice->getGrossAmount());
    }
}
