<?php

namespace Siwapp\InvoiceBundle\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Inflector;
use Siwapp\CoreBundle\Entity\AbstractInvoice;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Siwapp\InvoiceBundle\Entity\Invoice
 *
 * @ORM\Table(indexes={
 *    @ORM\Index(name="invoice_cstnm_idx", columns={"customer_name"}),
 *    @ORM\Index(name="invoice_cstid_idx", columns={"customer_identification"}),
 *    @ORM\Index(name="invoice_cstml_idx", columns={"customer_email"}),
 *    @ORM\Index(name="invoice_cntct_idx", columns={"contact_person"})
 * })
 * @ORM\Entity(repositoryClass="Siwapp\InvoiceBundle\Repository\InvoiceRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Invoice extends AbstractInvoice
{
    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="invoice", orphanRemoval=true, cascade={"all"})
     *
     */
    private $payments;

    /**
     * @var boolean $sent_by_email
     *
     * @ORM\Column(name="sent_by_email", type="boolean", nullable=true)
     */
    private $sent_by_email;

    /**
     * @var integer $number
     *
     * @ORM\Column(name="number", type="integer", nullable=true)
     */
    private $number;

    /**
     * @var date $issue_date
     *
     * @ORM\Column(name="issue_date", type="date", nullable=true)
     * @Assert\Date()
     */
    private $issue_date;

    /**
     * @var date $due_date
     *
     * @ORM\Column(name="due_date", type="date", nullable=true)
     * @Assert\Date()
     */
    private $due_date;

    /**
     * @ORM\ManyToMany(targetEntity="Siwapp\CoreBundle\Entity\Item", cascade={"persist"})
     * @ORM\JoinTable(name="invoices_items",
     *      joinColumns={@ORM\JoinColumn(name="invoice_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id", unique=true)}
     * )
     * @Assert\NotBlank()
     */
    protected $items;


    public function __construct()
    {
        parent::__construct();
        $this->payments = new ArrayCollection();
        $this->issue_date = new \DateTime();
        $this->due_date = new \DateTime();
    }

    /**
     * @return boolean
     */
    public function isClosed()
    {
        return $this->status === Invoice::CLOSED;
    }

    /**
     * @return boolean
     */
    public function isOpen()
    {
        return in_array($this->status, [Invoice::OPENED, Invoice::OVERDUE], true);
    }

    /**
     * @return boolean
     */
    public function isOverdue()
    {
        return $this->status === Invoice::OVERDUE;
    }

    /**
     * @return boolean
     */
    public function isDraft()
    {
        return $this->status === Invoice::DRAFT;
    }

    /**
     * Set sent_by_email
     *
     * @param boolean $sentByEmail
     */
    public function setSentByEmail($sentByEmail)
    {
        $this->sent_by_email = $sentByEmail;
    }

    /**
     * Get sent_by_email
     *
     * @return boolean
     */
    public function getSentByEmail()
    {
        return $this->sent_by_email;
    }

    /**
     * Set number
     *
     * @param integer $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set issue_date
     *
     * @param date $issueDate
     */
    public function setIssueDate($issueDate)
    {
        $this->issue_date = $issueDate instanceof \DateTime ?
        $issueDate: new \DateTime($issueDate);
    }

    /**
     * Get issue_date
     *
     * @return date
     */
    public function getIssueDate()
    {
        return $this->issue_date;
    }

    /**
     * Set due_date
     *
     * @param date $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->due_date = $dueDate instanceof \DateTime ?
        $dueDate : new \DateTime($dueDate);
    }

    /**
     * Get due_date
     *
     * @return date
     */
    public function getDueDate()
    {
        return $this->due_date;
    }

    /**
     * Add payments
     *
     * @param Siwapp\InvoiceBundle\Entity\Payment $payment
     */
    public function addPayment(\Siwapp\InvoiceBundle\Entity\Payment $payment)
    {
        $this->payments[] = $payment;
        $payment->setInvoice($this);
    }

    /**
     * Removes a payment.
     *
     * @param Siwapp\InvoiceBundle\Entity\Payment $payment
     */
    public function removePayment(\Siwapp\InvoiceBundle\Entity\Payment $payment)
    {
        foreach ($this->getPayments() as $key => $value) {
            if ($value === $payment) {
                unset($this->payments[$key]);
                break;
            }
        }
    }

    /**
     * Get payments
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /** **************** CUSTOM METHODS AND PROPERTIES **************  */

    /**
     * TODO: provide the serie .
     */
    public function __toString()
    {
        return $this->label();
    }

    public function label()
    {
        $series = $this->getSerie();
        $label = '';
        $label .= $series ? $series->getValue() : '';
        $label .= $this->isDraft() ? '[draft]' : $this->getNumber();

        return $label;
    }

    const DRAFT    = 0;
    const CLOSED   = 1;
    const OPENED   = 2;
    const OVERDUE  = 3;

    public function getDueAmount()
    {
        if ($this->isDraft()) {
            return null;
        }
        return $this->getGrossAmount() - $this->getPaidAmount();
    }

    /**
     * try to catch custom methods to be used in twig templates
     */
    public function __get($name)
    {
        if (strpos($name, 'tax_amount_') === 0) {
            return $this->calculate($name, true);
        }
        $method = Inflector::camelize("get_{$name}");
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return false;
    }

    public function __isset($name)
    {
        if (strpos($name, 'tax_amount_') === 0) {
            return true;
        }

        if (in_array($name, array_keys(get_object_vars($this)))) {
            return true;
        }

        return parent::__isset($name);
    }

    /**
     * checkStatus
     * checks and sets the status
     *
     * @return Siwapp\InvoiceBundle\Invoice $this
     */
    public function checkStatus()
    {
        if ($this->status == Invoice::DRAFT) {
            return $this;
        }
        if ($this->getDueAmount() == 0) {
            $this->setStatus(Invoice::CLOSED);
        } else {
            if ($this->getDueDate()->getTimestamp() > strtotime(date('Y-m-d'))) {
                $this->setStatus(Invoice::OPENED);
            } else {
                $this->setStatus(Invoice::OVERDUE);
            }
        }
        return $this;
    }

    public function getStatusString()
    {
        switch ($this->status) {
            case Invoice::DRAFT;
                $status = 'draft';
             break;
            case Invoice::CLOSED;
                $status = 'closed';
            break;
            case Invoice::OPENED;
                $status = 'opened';
            break;
            case Invoice::OVERDUE:
                $status = 'overdue';
                break;
            default:
                $status = 'unknown';
                break;
        }
        return $status;
    }

    public function setAmounts()
    {
        parent::setAmounts();
        $this->setPaidAmount($this->calculate('paid_amount'));

        return $this;
    }


    /* ********** LIFECYCLE CALLBACKS *********** */

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setNextNumber($event)
    {
        // compute the number of invoice
        if ((!$this->number && $this->status!=self::DRAFT) ||
            ($event instanceof PreUpdateEventArgs && $event->hasChangedField('serie') && $this->status!=self::DRAFT)
            ) {
            $this->setNumber($event->getEntityManager()->getRepository('SiwappInvoiceBundle:Invoice')->getNextNumber($this->getSerie()));
        }
    }
}
