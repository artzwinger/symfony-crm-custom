<?php

namespace Teachers\Bundle\InvoiceBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\InvoiceBundle\Model\ExtendInvoice;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\InvoiceBundle\Entity\Repository\InvoiceRepository")
 * @ORM\Table(
 *      name="teachers_invoice",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="teachers_invoice_index",
 *      routeView="teachers_invoice_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-file"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="student",
 *              "owner_column_name"="student_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="invoice"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="teachers_invoice_activity_view",
 *              "acl"="teachers_invoice_view",
 *              "action_button_widget"="teachers_invoice_button",
 *              "action_link_widget"="teachers_invoice_link"
 *          },
 *          "grid"={
 *              "default"="invoices-grid"
 *          }
 *      }
 * )
 */
class Invoice extends ExtendInvoice implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var int|null $id
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="teachers_invoice_id_seq", initialValue=1000)
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
     * @var float|null $amountOwed
     * @ORM\Column(name="amount_owed", type="money", nullable=false)
     */
    protected $amountOwed;
    /**
     * @var float|null $amountPaid
     * @ORM\Column(name="amount_paid", type="money", nullable=false)
     */
    protected $amountPaid;
    /**
     * @var float|null $amountRemaining
     * @ORM\Column(name="amount_remaining", type="money", nullable=false)
     */
    protected $amountRemaining;
    /**
     * @var DateTime|null
     * @ORM\Column(name="due_date", type="datetime", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $dueDate;
    /**
     * @var User|null $student
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="student_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $student;
    /**
     * @var Contact|null $teacherContact
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactBundle\Entity\Contact", cascade={"persist"})
     * @ORM\JoinColumn(name="student_contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $studentContact;
    /**
     * @var Account|null $teacherAccount
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AccountBundle\Entity\Account", cascade={"persist"})
     * @ORM\JoinColumn(name="student_account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $studentAccount;
    /**
     * @var Assignment|null
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\AssignmentBundle\Entity\Assignment")
     * @ORM\JoinColumn(name="assignment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $assignment;
    /**
     * @var Payment[]|Collection|null
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="invoice")
     */
    protected $payments;
    /**
     * @var Organization|null
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    const INTERNAL_STATUS_CODE = 'invoice_status';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIALLY_PAID = 'partially_paid';
    const WORKFLOW_STEP_UNPAID = 'unpaid';
    const WORKFLOW_STEP_PAID = 'paid';
    const WORKFLOW_STEP_PARTIALLY_PAID = 'partially_paid';

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_UNPAID => [
                'name' => 'Unpaid',
                'is_default' => true
            ],
            self::STATUS_PAID => [
                'name' => 'Paid',
                'is_default' => false
            ],
            self::STATUS_PARTIALLY_PAID => [
                'name' => 'Partially Paid',
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
    public function getAmountOwed(): ?float
    {
        return $this->amountOwed;
    }

    /**
     * @param float|null $amountOwed
     */
    public function setAmountOwed(?float $amountOwed): void
    {
        $this->amountOwed = $amountOwed;
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
    public function getAmountRemaining(): ?float
    {
        return $this->amountRemaining;
    }

    /**
     * @param float|null $amountRemaining
     */
    public function setAmountRemaining(?float $amountRemaining): void
    {
        $this->amountRemaining = $amountRemaining;
    }

    /**
     * @return DateTime|null
     */
    public function getDueDate(): ?DateTime
    {
        return $this->dueDate;
    }

    /**
     * @param DateTime|null $dueDate
     */
    public function setDueDate(?DateTime $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return User|null
     */
    public function getStudent(): ?User
    {
        return $this->student;
    }

    /**
     * @param User|null $student
     */
    public function setStudent(?User $student): void
    {
        $this->student = $student;
    }

    /**
     * @return Contact|null
     */
    public function getStudentContact(): ?Contact
    {
        return $this->studentContact;
    }

    /**
     * @param Contact|null $studentContact
     */
    public function setStudentContact(?Contact $studentContact): void
    {
        $this->studentContact = $studentContact;
    }

    /**
     * @return Account|null
     */
    public function getStudentAccount(): ?Account
    {
        return $this->studentAccount;
    }

    /**
     * @param Account|null $studentAccount
     */
    public function setStudentAccount(?Account $studentAccount): void
    {
        $this->studentAccount = $studentAccount;
    }

    /**
     * @return Assignment|null
     */
    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    /**
     * @param Assignment|null $assignment
     */
    public function setAssignment(?Assignment $assignment): void
    {
        $this->assignment = $assignment;
    }

    /**
     * @return Collection|Payment[]|null
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param Collection|Payment[]|null $payments
     */
    public function setPayments($payments): void
    {
        $this->payments = $payments;
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

    public function hasPayments(): bool
    {
        return $this->getPayments()->count() !== 0;
    }

    public function canReceivePayments(): bool
    {
        return $this->getAmountRemaining() > 0;
    }
}
