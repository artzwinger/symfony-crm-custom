<?php

namespace Teachers\Bundle\InvoiceBundle\Entity;

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
     * @var Invoice|null
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\InvoiceBundle\Entity\Invoice")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $invoice;
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
