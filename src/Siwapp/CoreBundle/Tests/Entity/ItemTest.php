<?php

namespace Siwapp\CoreBundle\Tests\Entity;

use Siwapp\CoreBundle\Entity\Item;
use Siwapp\CoreBundle\Entity\Tax;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBaseAmount()
    {
        $item = new Item;
        $item->setUnitaryCost(80);
        $item->setQuantity(1);
        $this->assertEquals(80, $item->getBaseAmount());
        $item->setQuantity(2);
        $this->assertEquals(160, $item->getBaseAmount());
        $item->setUnitaryCost(55);
        $this->assertEquals(110, $item->getBaseAmount());
    }

    public function testGetNetAmount()
    {
        $item = new Item;
        $item->setUnitaryCost(80);
        $item->setQuantity(1);
        $this->assertEquals(80, $item->getNetAmount());
        $item->setDiscount(10);
        $this->assertEquals(72, $item->getNetAmount());
        $item->setDiscount(2);
        $this->assertEquals(78.4, $item->getNetAmount());
    }

    public function testGetDiscountAmount()
    {
        $item = new Item;
        $item->setUnitaryCost(80);
        $item->setQuantity(1);
        $this->assertEquals(0, $item->getDiscountAmount());

        $item->setDiscount(10);
        $this->assertEquals(8, $item->getDiscountAmount());
        $item->setDiscount(2);
        $this->assertEquals(1.6, $item->getDiscountAmount());

        // Make sure that discount is always positive.
        $item->setUnitaryCost(-10);
        $item->setDiscount(2);
        $this->assertEquals(0.2, $item->getDiscountAmount());
    }

    public function testGetTaxAmount()
    {
        $item = new Item;
        $tax = new Tax;
        $tax->setValue(20);
        $item->setUnitaryCost(80);
        $item->setQuantity(1);
        $item->addTax($tax);
        $this->assertEquals(16, $item->getTaxAmount());
    }

    public function testGetGrossAmount()
    {
        $item = new Item;
        $tax = new Tax;
        $tax->setValue(20);
        $item->setUnitaryCost(80);
        $item->setQuantity(1);
        $item->addTax($tax);
        $this->assertEquals(96, $item->getGrossAmount());
    }
}
