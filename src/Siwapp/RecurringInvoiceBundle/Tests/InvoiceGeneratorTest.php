<?php

namespace Siwapp\RecurringInvoiceBundle\Tests;

use Doctrine\ORM\EntityManager;
use Siwapp\InvoiceBundle\Entity\Invoice;
use Siwapp\RecurringInvoiceBundle\InvoiceGenerator;
use Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice;

class InvoiceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider generatePendingProvider
     */
    public function testGeneratePending(EntityManager $em, RecurringInvoice $recurring, $expectedCount)
    {
        $generator = new InvoiceGenerator($em);

        $this->assertEquals($expectedCount, $generator->generatePending($recurring));
    }

    public function generatePendingProvider()
    {
        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(['persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();

        $recurring = $this->getMock('Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice');
        $cases[] = [$em, $recurring, 0];

        $itemMock = $this->getMock('Siwapp\CoreBundle\Entity\Item');
        $itemMock->expects($this->once())
            ->method('getTaxes')
            ->will($this->returnValue([]));
        $recurring = $this->getMock('Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice');
        $recurring->expects($this->at(0))
            ->method('countPendingInvoices')
            ->will($this->returnValue(1));
        $recurring->expects($this->at(1))
            ->method('countPendingInvoices')
            ->will($this->returnValue(2));
        $recurring->expects($this->once())
            ->method('getSeries')
            ->will($this->returnValue($this->getMock('Siwapp\CoreBundle\Entity\Series')));
        $recurring->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$itemMock]));
        // Check that invoices are being added with status open.
        $recurring->expects($this->once())
            ->method('addInvoice')
            ->with($this->callback(function(Invoice $invoice) {
              return $invoice->getStatus() == Invoice::OPENED;
            }));
        $cases[] = [$em, $recurring, 1];

        return $cases;
    }
}
