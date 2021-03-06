<?php

namespace Teachers\Bundle\InvoiceBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\InvoiceBundle\Model\ExtendPayment;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\InvoiceBundle\Entity\Repository\PaymentRepository")
 * @ORM\Table(
 *      name="teachers_payment",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="teachers_payment_index",
 *      routeView="teachers_payment_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-credit-card"
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
 *              "category"="payment"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="teachers_payment_activity_view",
 *              "acl"="teachers_payment_view",
 *              "action_button_widget"="teachers_payment_button",
 *              "action_link_widget"="teachers_payment_link"
 *          },
 *          "grid"={
 *              "default"="payments-grid"
 *          }
 *      }
 * )
 */
class Payment extends ExtendPayment implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var int|null $id
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="teachers_payment_id_seq", initialValue=1000)
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
     * @var float|null $amountPaid
     * @ORM\Column(name="amount_paid", type="money", nullable=false)
     */
    protected $amountPaid;
    /**
     * @var float|null $amountPaidAfterRefund
     * @ORM\Column(name="amount_paid_after_refund", type="money", nullable=true)
     */
    protected $amountPaidAfterRefund;
    /**
     * @var float|null $amountRefunded
     * @ORM\Column(name="amount_refunded", type="money", nullable=true)
     */
    protected $amountRefunded;
    /**
     * @var string|null $transaction
     * @ORM\Column(name="transaction", type="string", nullable=true)
     */
    protected $transaction;
    /**
     * @var string|null $manualPaymentReason
     * @ORM\Column(name="manual_payment_reason", type="string", nullable=true)
     */
    protected $manualPaymentReason;
    /**
     * @var Invoice|null
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\InvoiceBundle\Entity\Invoice")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $invoice;
    /**
     * @var Refund[]|Collection|null
     * @ORM\OneToMany(targetEntity="Teachers\Bundle\InvoiceBundle\Entity\Refund", mappedBy="payment")
     */
    protected $refunds;
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

    const STATUS_ENUM_CLASS = 'payment_status';
    const WORKFLOW_NAME = 'payment_flow';

    /**
     * transitions
     */
    const TRANSITION_PAID_IN_FULL = 'paid_in_full';
    const TRANSITION_PARTIAL_PAYMENT = 'partial_payment';

    /*
     * outdated statuses
     */
    const STATUS_CREATED = 'created';
    const WORKFLOW_STEP_CREATED = 'created';

    /*
     * relevant statuses
     */
    const STATUS_PARTIAL_PAYMENT = 'partial_payment';
    const STATUS_PAID_IN_FULL = 'paid_in_full';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    const STATUS_FULLY_REFUNDED = 'fully_refunded';

    const WORKFLOW_STEP_PARTIAL_PAYMENT = 'partial_payment';
    const WORKFLOW_STEP_PAID_IN_FULL = 'paid_in_full';
    const WORKFLOW_STEP_PARTIALLY_REFUNDED = 'partially_refunded';
    const WORKFLOW_STEP_FULLY_REFUNDED = 'fully_refunded';

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PARTIAL_PAYMENT => [
                'name' => 'Partial Payment',
                'is_default' => true
            ],
            self::STATUS_PAID_IN_FULL => [
                'name' => 'Paid in Full',
                'is_default' => false
            ],
            self::STATUS_PARTIALLY_REFUNDED => [
                'name' => 'Partially Refunded',
                'is_default' => false
            ],
            self::STATUS_FULLY_REFUNDED => [
                'name' => 'Fully Refunded',
                'is_default' => false
            ],
        ];
    }

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
    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    /**
     * @param float|null $amountPaid
     */
    public function setAmountPaid(?float $amountPaid): void
    {
        $this->amountPaid = $amountPaid;
    }

    /**
     * @return float|null
     */
    public function getAmountPaidAfterRefund(): ?float
    {
        return $this->amountPaidAfterRefund;
    }

    /**
     * @param float|null $amountPaidAfterRefund
     */
    public function setAmountPaidAfterRefund(?float $amountPaidAfterRefund): void
    {
        $this->amountPaidAfterRefund = $amountPaidAfterRefund;
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
     * @return string|null
     */
    public function getTransaction(): ?string
    {
        return $this->transaction;
    }

    /**
     * @param string|null $transaction
     */
    public function setTransaction(?string $transaction): void
    {
        $this->transaction = $transaction;
    }

    /**
     * @return string|null
     */
    public function getManualPaymentReason(): ?string
    {
        return $this->manualPaymentReason;
    }

    /**
     * @param string|null $manualPaymentReason
     */
    public function setManualPaymentReason(?string $manualPaymentReason): void
    {
        $this->manualPaymentReason = $manualPaymentReason;
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
     * @return Collection|Refund[]|null
     */
    public function getRefunds()
    {
        return $this->refunds;
    }

    /**
     * @param Collection|Refund[]|null $refunds
     */
    public function setRefunds($refunds): void
    {
        $this->refunds = $refunds;
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

    public function isFullyRefunded(): bool
    {
        return $this->getAmountRefunded() === $this->getAmountPaid();
    }

    public function isStatusFullyRefunded(): bool
    {
        return $this->getStatus()->getId() === self::STATUS_FULLY_REFUNDED;
    }

    public function isPartiallyRefunded(): bool
    {
        return $this->getAmountRefunded() > 0 && !$this->isFullyRefunded();
    }

    public function isStatusPartiallyRefunded(): bool
    {
        return $this->getStatus()->getId() === self::STATUS_PARTIALLY_REFUNDED;
    }
}
