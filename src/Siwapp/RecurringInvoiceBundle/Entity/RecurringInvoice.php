<?php

namespace Siwapp\RecurringInvoiceBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Inflector;
use Siwapp\CoreBundle\Entity\AbstractInvoice;
use Siwapp\InvoiceBundle\Entity\Invoice;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice
 *
 * @ORM\Table(indexes={
 *    @ORM\Index(name="recurring_cstnm_idx", columns={"customer_name"}),
 *    @ORM\Index(name="recurring_cstid_idx", columns={"customer_identification"}),
 *    @ORM\Index(name="recurring_cstml_idx", columns={"customer_email"}),
 *    @ORM\Index(name="recurring_cntct_idx", columns={"contact_person"})
 * })
 * @ORM\Entity(repositoryClass="Siwapp\RecurringInvoiceBundle\Repository\RecurringInvoiceRepository")
 */
class RecurringInvoice extends AbstractInvoice
{
    /**
     * @var integer $days_to_due
     *
     * @ORM\Column(name="days_to_due", type="integer", nullable=true)
     * @Assert\Length(min=0)
     */
    private $days_to_due;

    /**
     * @var boolean $enabled
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var integer $max_occurrences
     *
     * @ORM\Column(name="max_occurrences", type="integer", nullable=true)
     * @Assert\Length(min=1)
     */
    private $max_occurrences;

    /**
     * @var integer $must_occurrences
     *
     * @ORM\Column(name="must_occurrences", type="integer", nullable=true)
     * @Assert\Length(min=1)
     */
    private $must_occurrences;

    /**
     * @var integer $period
     *
     * @ORM\Column(name="period", type="integer")
     * @Assert\Length(min=1)
     * @Assert\NotBlank()
     */
    private $period;

    /**
     * @var string $period_type
     *
     * @ORM\Column(name="period_type", type="string", length=8)
     * @Assert\Choice(
     *     choices = { "year", "month", "week", "day" },
     * )
     */
    private $period_type;

    /**
     * @var date $starting_date
     * @Assert\Date()
     * @ORM\Column(name="starting_date", type="date")
     */
    private $starting_date;

    /**
     * @var date $finishing_date
     *
     * @ORM\Column(name="finishing_date", type="date", nullable=true)
     * @Assert\Date()
     */
    private $finishing_date;

    /**
     * @var date $last_execution_date
     *
     * @ORM\Column(name="last_execution_date", type="date", nullable=true)
     * @Assert\Date()
     */
    private $last_execution_date;

    /**
     * @ORM\ManyToMany(targetEntity="Siwapp\CoreBundle\Entity\Item", cascade={"all"}, inversedBy="recurring_invoice")
     * @ORM\JoinTable(name="recurring_invoices_items",
     *      joinColumns={@ORM\JoinColumn(name="recurring_invoice_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     * )
     * @Assert\NotBlank()
     */
    protected $items;

    /**
     * @ORM\ManyToMany(targetEntity="Siwapp\InvoiceBundle\Entity\Invoice", cascade={"persist"})
     * @ORM\JoinTable(name="recurring_invoices_invoices",
     *      joinColumns={@ORM\JoinColumn(
     *          name="recurring_invoice_id", referencedColumnName="id", onDelete="CASCADE"
     *      )},
     *      inverseJoinColumns={@ORM\JoinColumn(
     *          name="invoice_id", referencedColumnName="id", onDelete="CASCADE", unique=true
     *      )}
     * )
     */
    protected $invoices;


    public function __construct()
    {
        parent::__construct();
        $this->invoices = new ArrayCollection;
    }

    /**
     * Set days_to_due
     *
     * @param integer $daysToDue
     */
    public function setDaysToDue($daysToDue)
    {
        $this->days_to_due = $daysToDue;
    }

    /**
     * Get days_to_due
     *
     * @return integer
     */
    public function getDaysToDue()
    {
        return $this->days_to_due;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set max_occurrences
     *
     * @param integer $maxOccurrences
     */
    public function setMaxOccurrences($maxOccurrences)
    {
        $this->max_occurrences = $maxOccurrences;
    }

    /**
     * Get max_occurrences
     *
     * @return integer
     */
    public function getMaxOccurrences()
    {
        return $this->max_occurrences;
    }

    /**
     * Set must_occurrences
     *
     * @param integer $mustOccurrences
     */
    public function setMustOccurrences($mustOccurrences)
    {
        $this->must_occurrences = $mustOccurrences;
    }

    /**
     * Get must_occurrences
     *
     * @return integer
     */
    public function getMustOccurrences()
    {
        return $this->must_occurrences;
    }

    /**
     * Set period
     *
     * @param integer $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * Get period
     *
     * @return integer
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Set period_type
     *
     * @param string $periodType
     */
    public function setPeriodType($periodType)
    {
        $this->period_type = $periodType;
    }

    /**
     * Get period_type
     *
     * @return string
     */
    public function getPeriodType()
    {
        return $this->period_type;
    }

    /**
     * Set starting_date
     *
     * @param date $startingDate
     */
    public function setStartingDate($startingDate)
    {
        $this->starting_date = $startingDate instanceof \DateTime ?
        $startingDate: new \DateTime($startingDate);
    }

    /**
     * Get starting_date
     *
     * @return date
     */
    public function getStartingDate()
    {
        return $this->starting_date;
    }

    /**
     * Set finishing_date
     *
     * @param date $finishingDate
     */
    public function setFinishingDate($finishingDate)
    {
        if ($finishingDate) {
            $this->finishing_date = $finishingDate instanceof \DateTime
                ? $finishingDate
                : new \DateTime($finishingDate);
        }
    }

    /**
     * Get finishing_date
     *
     * @return date
     */
    public function getFinishingDate()
    {
        return $this->finishing_date;
    }

    /**
     * Set last_execution_date
     *
     * @param date $lastExecutionDate
     */
    public function setLastExecutionDate($lastExecutionDate)
    {
        $this->last_execution_date = $lastExecutionDate instanceof \DateTime
        ? $lastExecutionDate
        : new \DateTime($lastExecutionDate);
    }

    /**
     * Get invoices
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * Add invoice
     *
     * @param Siwapp\InvoiceBundle\Entity\Invoice $invoice
     */
    public function addInvoice(Invoice $invoice)
    {
        $this->invoices[] = $invoice;
    }

    /**
     * Get last_execution_date
     *
     * @return date
     */
    public function getLastExecutionDate()
    {
        return $this->last_execution_date;
    }

    /** ********** CUSTOM METHODS AND PROPERTIES ************** **/

    /**
     * try to catch custom methods to be used in twig templates
     */
    public function __get($name)
    {
        $method = Inflector::camelize("get_{$name}");
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return false;
    }

    /**
     * TODO: provide the series.
     */
    public function __toString()
    {
        return $this->label();
    }

    public function label()
    {
        $label = '';
        if ($this->getSeries()) {
            $label .= $this->getSeries()->getName();
        }
        $label .= ' ' . $this->getCustomerName();

        return $label;
    }

    const INACTIVE = 0;
    const FINISHED = 1;
    const ACTIVE = 2;
    const PENDING = 3;

    public function getStatusString()
    {
        switch ($this->status) {
            case self::INACTIVE;
                $status = 'inactive';
             break;
            case self::FINISHED;
                $status = 'finished';
            break;
            case self::ACTIVE;
                $status = 'active';
            break;
            case self::PENDING:
                $status = 'pending';
                break;
            default:
                $status = 'unknown';
                break;
        }
        return $status;
    }

    /**
     * get occurrences. get the number of invoices this recurring has generated
     *
     * @return integer the number of invoices generated
     */
    public function getOccurrences()
    {
        return count($this->getInvoices());
    }

    /**
     * Gets the number of the pending invoices to be generated
     * by this recurring at the actual date
     *
     * @return integer the number of pending invoices
     */
    public function countPendingInvoices()
    {
        return ($this->must_occurrences - $this->getOccurrences());
    }

    /**
     * checks and sets the number of invoices that should have been
     * generated until the day specified in args
     *
     * @param \DateTime $today
     */
    public function checkMustOccurrences(\DateTime $today = null)
    {
        if (!$today) {
            $today = new \DateTime();
        }
        $starting_date = $this->getStartingDate();
        $finishing_date = $this->getFinishingDate();
        // TODO : FINISH THIS METHODD!!!
        if ($today > $starting_date) {
            $check_date = $finishing_date ?
                ($today > $finishing_date ? $finishing_date: $today) : $today;

            switch ($this->period_type) {
                case 'year':
                    $unit = 'y';
                    break;
                case 'month':
                    $unit = 'm';
                    break;
                case 'week':
                case 'day':
                    $unit = 'a';
                    break;
            }

            $difference = $check_date->diff($starting_date)->format($unit);

            if ($this->period_type == 'week') {
                $difference /= 7;
            }
            $must_occurrences = floor($difference / $this->period) +1;


            // if there's already a must_occurreces and is greater
            // then set this as must_occurences
            if ($this->must_occurrences &&
               $must_occurrences > $this->must_occurrences) {
                $must_occurrences = $this->must_occurrences;
            }
            $this->must_occurrences = $must_occurrences;
        } else {
            $this->must_occurrences = 0;
        }
    }

    /**
     * checks and sets the status
     *
     * @return RecurringInvoice $this
     */
    public function checkStatus()
    {
        if (!$this->getEnabled()) {
            $this->setStatus(RecurringInvoice::INACTIVE);
        } else {
            if (($this->getMaxOccurrences() && $this->getOccurrences() >= $this->getMaxOccurrences())
                  || ($this->getFinishingDate()
                    && $this->getLastExecutionDate() >= $this->getFinishingDate()
                    && $this->countPendingInvoices() <= 0)) {
                $this->setStatus(RecurringInvoice::FINISHED);
            } else {
                if ($this->countPendingInvoices() > 0) {
                    $this->setStatus(RecurringInvoice::PENDING);
                } else {
                    $this->setStatus(RecurringInvoice::ACTIVE);
                }
            }
        }
    }

    /* ********** LIFECYCLE CALLBACKS *********** */

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function preSave(LifecycleEventArgs $args)
    {
        $this->checkAmounts();
        parent::presave($args);
        $this->checkMustOccurrences();
    }
}
