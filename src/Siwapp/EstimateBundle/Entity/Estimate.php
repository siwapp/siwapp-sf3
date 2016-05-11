<?php

namespace Siwapp\EstimateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Siwapp\CoreBundle\Entity\AbstractInvoice;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Siwapp\EstimateBundle\Entity\Estimate
 *
 * @ORM\Table(indexes={
 *    @ORM\index(name="estimate_cstnm_idx", columns={"customer_name"}),
 *    @ORM\index(name="estimate_cstid_idx", columns={"customer_identification"}),
 *    @ORM\index(name="estimate_cstml_idx", columns={"customer_email"}),
 *    @ORM\index(name="estimate_cntct_idx", columns={"contact_person"})
 * })
 * @ORM\Entity(repositoryClass="Siwapp\EstimateBundle\Entity\EstimateRepository")
 */
class Estimate extends AbstractInvoice
{
    /**
     * @ORM\ManyToMany(targetEntity="Siwapp\CoreBundle\Entity\Item", cascade={"persist"})
     * @ORM\JoinTable(name="estimates_items",
     *      joinColumns={@ORM\JoinColumn(name="estimate_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id", unique=true)}
     * )
     * @Assert\NotBlank()
     */
    protected $items;

    /**
     * @var boolean $draft
     *
     * @ORM\Column(name="draft", type="boolean")
     */
    private $draft;

    /**
     * @var integer $number
     *
     * @ORM\Column(name="number", type="integer", nullable=true)
     */
    private $number;

    /**
     * @var boolean $sent_by_email
     *
     * @ORM\Column(name="sent_by_email", type="boolean")
     */
    private $sent_by_email;


    /**
     * Set draft
     *
     * @param boolean $draft
     */
    public function setDraft($draft)
    {
        $this->draft = $draft;
    }

    /**
     * Get draft
     *
     * @return boolean
     */
    public function getDraft()
    {
        return $this->draft;
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

    /** ********** CUSTOM METHODS AND PROPERTIES ************* */

    const DRAFT    = 0;
    const PENDING  = 1;
    const APPROVED = 2;
    const REJECTED = 3;

    public function checkStatus()
    {
        if($this->getDraft())
        {
            $this->setStatus(Estimate::DRAFT);
        }
    }

}
