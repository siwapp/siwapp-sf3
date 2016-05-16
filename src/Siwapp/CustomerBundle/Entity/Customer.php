<?php

namespace Siwapp\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Siwapp\InvoiceBundle\Entity\Invoice;

/**
 * Customer
 *
 * @ORM\Table(name="customer")
 * @ORM\Entity(repositoryClass="Siwapp\CustomerBundle\Repository\CustomerRepository")
 */
class Customer implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="identification", type="string", length=128, nullable=true, unique=true)
     */
    private $identification;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_person", type="string", length=255, nullable=true)
     */
    private $contactPerson;

    /**
     * @var string
     *
     * @ORM\Column(name="invoicing_address", type="text", nullable=true)
     */
    private $invoicingAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address", type="text", nullable=true)
     */
    private $shippingAddress;

    /**
     * @ORM\ManyToMany(targetEntity="Siwapp\InvoiceBundle\Entity\Invoice")
     * @ORM\JoinTable(name="customers_invoices",
     *      joinColumns={@ORM\JoinColumn(
     *          name="customer_id", referencedColumnName="id", onDelete="CASCADE"
     *      )},
     *      inverseJoinColumns={@ORM\JoinColumn(
     *          name="invoice_id", referencedColumnName="id", onDelete="CASCADE", unique=true
     *      )}
     * )
     */
    protected $invoices;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Customer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set identification
     *
     * @param string $identification
     *
     * @return Customer
     */
    public function setIdentification($identification)
    {
        $this->identification = $identification;

        return $this;
    }

    /**
     * Get identification
     *
     * @return string
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set contactPerson
     *
     * @param string $contactPerson
     *
     * @return Customer
     */
    public function setContactPerson($contactPerson)
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    /**
     * Get contactPerson
     *
     * @return string
     */
    public function getContactPerson()
    {
        return $this->contactPerson;
    }

    /**
     * Set invoicingAddress
     *
     * @param string $invoicingAddress
     *
     * @return Customer
     */
    public function setInvoicingAddress($invoicingAddress)
    {
        $this->invoicingAddress = $invoicingAddress;

        return $this;
    }

    /**
     * Get invoicingAddress
     *
     * @return string
     */
    public function getInvoicingAddress()
    {
        return $this->invoicingAddress;
    }

    /**
     * Set shippingAddress
     *
     * @param string $shippingAddress
     *
     * @return Customer
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * Get shippingAddress
     *
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * Adds an invoice.
     *
     * @param Siwapp\InvoiceBundle\Entity\Invoice $invoice
     */
    public function addInvoice(Invoice $invoice)
    {
        $this->invoices[] = $invoice;
    }

    /**
     * Removes an invoice.
     *
     * @param Siwapp\InvoiceBundle\Entity\Invoice $invoice
     */
    public function removeInvoice(Invoice $invoice)
    {
        foreach ($this->invoices as $key => $value) {
            if ($value === $invoice) {
                unset($this->invoices[$key]);
                break;
            }
        }
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email'=> $this->getEmail(),
            'identification' => $this->getIdentification(),
            'contact_person' => $this->getContactPerson(),
            'invoicing_address' => $this->getInvoicingAddress(),
            'shipping_address' => $this->getShippingAddress(),
        );
    }

    public function label()
    {
        return $this->getName();
    }

    public function getTotal()
    {
        $total = 0;
        foreach ($this->invoices as $invoice) {
            if ($invoice->isDraft()) {
                continue;
            }
            $total += $invoice->getGrossAmount();
        }

        return $total;
    }

    public function getDue()
    {
        $due = 0;
        foreach ($this->invoices as $invoice) {
            if ($invoice->isDraft()) {
                continue;
            }
            $due += $invoice->getDueAmount();
        }

        return $due;
    }
}
