<?php

namespace Siwapp\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Inflector;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Siwapp\ProductBundle\Entity\Product;

/**
 * Siwapp\InvoiceBundle\Entity\Item
 *
 * @ORM\Entity(repositoryClass="Siwapp\CoreBundle\Repository\ItemRepository")
 * @ORM\Table(indexes={
 *    @ORM\Index(name="invoice_item_desc_idx", columns={"description"})
 * })
 */
class Item implements \JsonSerializable
{

    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer $quantity
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $quantity;

    /**
     * @var decimal $discount
     *
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $discount;

    /**
     * @var string $description
     *
     * @ORM\Column()
     */
    private $description;

    /**
     * @var decimal $unitary_cost
     *
     * @ORM\Column(type="decimal", precision=15, scale=3)
     * @Assert\NotBlank()
     */
    private $unitary_cost;

    /**
     * @ORM\ManyToMany(targetEntity="Siwapp\CoreBundle\Entity\Tax")
     * @ORM\JoinTable(name="items_taxes")
     *
     * unidirectional many-to-many
     */
    private $taxes;

    /**
     * @ORM\ManyToOne(targetEntity="Siwapp\ProductBundle\Entity\Product", inversedBy="items")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $product;

    /**
     * @ORM\ManyToMany(targetEntity="Siwapp\InvoiceBundle\Entity\Invoice", mappedBy="items")
     */
    private $invoice;

    /**
     * @ORM\ManyToMany(targetEntity="Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice", mappedBy="items")
     */
    private $recurring_invoice;

    /**
     * @ORM\ManyToMany(targetEntity="Siwapp\EstimateBundle\Entity\Estimate", mappedBy="items")
     */
    private $estimate;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set Quantity
     *
     * @param integer $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Get discount
     *
     * @return decimal
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set Discount
     *
     * @param decimal $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * Get discount
     *
     * @return decimal
     */
    public function getDiscountPercent()
    {
        return $this->discount/100;
    }

    /**
     * Set Discount
     *
     * @param decimal $discount
     */
    public function setDiscountPercent($discount)
    {
        $this->discount = $discount*100;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get unitary cost
     *
     * @return decimal
     */
    public function getUnitaryCost()
    {
        return $this->unitary_cost;
    }

    /**
     * Set unitary cost
     *
     * @param decimal $unitary_cost
     */
    public function setUnitaryCost($unitary_cost)
    {
        $this->unitary_cost = $unitary_cost;
    }

    /**
     * Get product
     *
     * @return \Siwapp\ProductBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set product
     *
     * @param \Siwapp\ProductBundle\Entity\Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /** **************** CUSTOM METHODS ************* */

    /**
     * Get base amount
     *
     * @return float gross price of the item (times quantity)
     */
    public function getBaseAmount()
    {
        return $this->unitary_cost*$this->quantity;
    }

    /**
     * Get net amount
     *
     * @return float price with discount
     */
    public function getNetAmount()
    {
        return $this->getBaseAmount() - $this->getDiscountAmount();
    }

    /**
     * Get discount amount
     *
     * @return float amount to discount
     */
    public function getDiscountAmount()
    {
        return abs($this->getBaseAmount()) * $this->getDiscountPercent();
    }

    /**
     * Get tax amount
     *
     * @param array tax_names. if present, returns the amount for those taxes
     * @return float amount to tax
     */
    public function getTaxAmount($tax_names = array())
    {
        return $this->getNetAmount() * $this->getTaxesPercent($tax_names) / 100;
    }

    /**
     * Get gross amount
     *
     * @return float amount to pay after discount and taxes
     */
    public function getGrossAmount()
    {
        return $this->getNetAmount() + $this->getTaxAmount();
    }

    /**
     * Get taxes percent
     *
     * @param tax_names array if present shows only percent of those taxes
     * @return integer total percent of taxes to apply
     */
    public function getTaxesPercent($tax_names = array())
    {
        $tax_names = is_array($tax_names) ?
            array_map(array('Gedmo\Sluggable\Util\Urlizer', 'urlize'), $tax_names):
            array(Urlizer::urlize($tax_names)) ;

        $total = 0;
        foreach ($this->getTaxes() as $tax) {
            if (count($tax_names)==0 ||
               in_array(Urlizer::urlize(str_replace(' ', '', $tax->getName())), $tax_names)) {
                $total += $tax->getValue();
            }
        }
        return $total;
    }

    /**
     * Try to capture a "getTaxAmountTAXNAME" method.
     * This is to be able to use 'invoice.tax_amount_TAXNAME' in the templates
     *
     * @author JoeZ99 <jzarate@gmail.com>
     */
    public function __call($data, $argument)
    {
        if (strpos($data, 'getTaxAmount') === 0 && strlen($data)>12) {
            $tax_name = substr($data, 12);
            return $this->getTaxAmount($tax_name);
        }
        return false;
    }

    /**
     * Again, capture hipothetical {{invoice.base_amount}} and the like
     *
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
     * Twig template system needs this to answer true for the specified properties
     */
    public function __isset($name)
    {
        $classVars = array_keys(get_object_vars($this));
        $extraVars = ['discount_amount', 'base_amount', 'net_amount', 'tax_amount', 'gross_amount'];
        if (in_array($name, array_merge($classVars, $extraVars))) {
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return (string) $this->description.': '.$this->quantity;
    }

    public function __construct(array $taxes = [])
    {
        $this->taxes = new ArrayCollection();
        foreach ($taxes as $tax) {
            $this->addTax($tax);
        }

        $this->quantity = 1;
        $this->discount = 0;
    }

    /**
     * Add taxes
     *
     * @param Siwapp\CoreBundle\Entity\Tax $tax
     */
    public function addTax(\Siwapp\CoreBundle\Entity\Tax $tax)
    {
        $this->taxes[] = $tax;
    }

    /**
     * Get taxes
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     *
     * Remove Tax
     *
     * @param Siwapp\CoreBundle\Entity\Tax
     */
    public function removeTax(\Siwapp\CoreBundle\Entity\Tax $tax)
    {
        $this->taxes->removeElement($tax);
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'unitary_cost' => $this->getUnitaryCost(),
            'description' => $this->getDescription(),
        ];
    }
}
