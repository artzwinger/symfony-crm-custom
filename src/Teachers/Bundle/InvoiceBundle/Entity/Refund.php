<?php

namespace Teachers\Bundle\InvoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\InvoiceBundle\Model\ExtendRefund;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\InvoiceBundle\Entity\Repository\PaymentRepository")
 * @ORM\Table(
 *      name="teachers_refund",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="teachers_refund_index",
 *      routeView="teachers_refund_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-undo"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="refund"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="teachers_refund_activity_view",
 *              "acl"="teachers_refund_view"
 *          },
 *          "grid"={
 *              "default"="refunds-grid"
 *          }
 *      }
 * )
 */
class Refund extends ExtendRefund implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var int|null $id
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="teachers_refund_id_seq", initialValue=1000)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;
    /**
     * @var float|null $amountRefunded
     * @ORM\Column(name="amount_refunded", type="money", nullable=false)
     */
    protected $amountRefunded;
    /**
     * @var bool|null $refunded
     * @ORM\Column(name="refunded", type="boolean", nullable=false)
     */
    protected $refunded;
    /**
     * @var Invoice|null
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\InvoiceBundle\Entity\Invoice")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $invoice;
    /**
     * @var Payment|null
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\InvoiceBundle\Entity\Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $payment;
    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;
    /**
     * @var Organization|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return float|null
     */
    public function getAmountRefunded(): ?float
    {
        return $this->amountRefunded;
    }

    /**
     * @param float|null $amountRefunded
     */
    public function setAmountRefunded(?float $amountRefunded): void
    {
        $this->amountRefunded = $amountRefunded;
    }

    /**
     * @return bool|null
     */
    public function getRefunded(): ?bool
    {
        return $this->refunded;
    }

    /**
     * @param bool|null $refunded
     */
    public function setRefunded(?bool $refunded): void
    {
        $this->refunded = $refunded;
    }

    /**
     * @return Invoice|null
     */
    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    /**
     * @param Invoice|null $invoice
     */
    public function setInvoice(?Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    /**
     * @return Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @param Payment|null $payment
     */
    public function setPayment(?Payment $payment): void
    {
        $this->payment = $payment;
    }

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     */
    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * @param Organization|null $organization
     */
    public function setOrganization(?Organization $organization): void
    {
        $this->organization = $organization;
    }
}
