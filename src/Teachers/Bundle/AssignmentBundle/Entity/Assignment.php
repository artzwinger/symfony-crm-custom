<?php

namespace Teachers\Bundle\AssignmentBundle\Entity;

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
use Teachers\Bundle\ApplicationBundle\Entity\Application;
use Teachers\Bundle\AssignmentBundle\Model\ExtendAssignment;
use Teachers\Bundle\BidBundle\Entity\Bid;
use Teachers\Bundle\UsersBundle\Entity\TeacherGroup;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\AssignmentBundle\Entity\Repository\AssignmentRepository")
 * @ORM\Table(
 *      name="teachers_assignment",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="teachers_assignment_index",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-paperclip"
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
 *              "group_name"="assignment",
 *              "category"="assignment"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="teachers_assignment_activity_view",
 *              "acl"="teachers_assignment_view",
 *              "action_button_widget"="teachers_assignment_button",
 *              "action_link_widget"="teachers_assignment_link"
 *          },
 *          "grid"={
 *              "default"="teachers-assignments-grid"
 *          }
 *      }
 * )
 */
class Assignment extends ExtendAssignment implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var int $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="teachers_assignment_id_seq", initialValue=1000)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;

    protected $subject;

    protected $term;

    /**
     * @var string|null $firstName
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     */
    protected $firstName;

    /**
     * @var string|null $lastName
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false)
     */
    protected $lastName;

    /**
     * @var string|null $courseName
     *
     * @ORM\Column(name="course_name", type="string", length=255, nullable=false)
     */
    protected $courseName;

    /**
     * @var string|null $coursePrefixes
     *
     * @ORM\Column(name="course_prefixes", type="string", length=255, nullable=false)
     */
    protected $coursePrefixes;

    /**
     * @var string|null $description
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var boolean|null $workToday
     *
     * @ORM\Column(name="work_today", type="boolean", nullable=false)
     */
    protected $workToday;

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
     * @var string|null $courseUrl
     *
     * @ORM\Column(name="course_url", type="string", length=255, nullable=false)
     */
    protected $courseUrl;

    /**
     * @var string|null $userLogin
     *
     * @ORM\Column(name="user_login", type="string", length=255, nullable=false)
     */
    protected $userLogin;

    /**
     * @var string|null $userPassword
     *
     * @ORM\Column(name="user_password", type="string", length=255, nullable=false)
     */
    protected $userPassword;

    /**
     * @var string|null $instructions
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $instructions;

    /**
     * @var double|null $amountDueToday
     *
     * @ORM\Column(name="amount_due_today", type="money", nullable=true)
     */
    protected $amountDueToday;

    /**
     * @var double|null $assignmentValue
     *
     * @ORM\Column(name="assignment_value", type="money", nullable=true)
     */
    protected $assignmentValue;

    /**
     * @var boolean $invoiceDueTodayPaid
     *
     * @ORM\Column(name="invoice_due_today_paid", type="boolean", nullable=true)
     */
    protected $invoiceDueTodayPaid = false;

    /**
     * @var Application|null $application
     *
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\ApplicationBundle\Entity\Application")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $application;

    /**
     * @var User $teacher
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="teacher_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $teacher;
    /**
     * @var User $student
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="student_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $student;
    /**
     * @var Contact|null $studentContact
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactBundle\Entity\Contact", cascade={"persist"})
     * @ORM\JoinColumn(name="student_contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $studentContact;
    /**
     * @var Account|null $studentContact
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AccountBundle\Entity\Account", cascade={"persist"})
     * @ORM\JoinColumn(name="student_account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $studentAccount;
    /**
     * @var Contact|null $teacherContact
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactBundle\Entity\Contact", cascade={"persist"})
     * @ORM\JoinColumn(name="teacher_contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $teacherContact;
    /**
     * @var Account|null $teacherAccount
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AccountBundle\Entity\Account", cascade={"persist"})
     * @ORM\JoinColumn(name="teacher_account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $teacherAccount;
    /**
     * @var Collection|TeacherGroup[] $teacherGroups
     *
     * @ORM\ManyToMany(targetEntity="Teachers\Bundle\UsersBundle\Entity\TeacherGroup", inversedBy="assignments")
     * @ORM\JoinTable(name="teachers_asmg_to_tg",
     *      joinColumns={@ORM\JoinColumn(name="assignment_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="teacher_group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $teacherGroups;

    /**
     * @var Collection|Bid[] $bids
     * @ORM\OneToMany(targetEntity="Teachers\Bundle\BidBundle\Entity\Bid", mappedBy="assignment")
     */
    protected $bids;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="course_manager_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $courseManager;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    const STATUS_NEW = 'new';
    const STATUS_UP_FOR_BID = 'up_for_bid';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_COMPLETE = 'complete';
    const STATUS_PAUSED_DUE_NONPAYMENT = 'paused_due_nonpayment';

    const WORKFLOW_NAME = 'assignment_flow';
    const WORKFLOW_TRANSITION_START_BIDDING = 'start_accepting_bids';
    const WORKFLOW_STEP_NEW = 'new';
    const WORKFLOW_STEP_UP_FOR_BID = 'up_for_bid';
    const WORKFLOW_STEP_ASSIGNED = 'assigned';
    const WORKFLOW_STEP_PAUSED_DUE_NONPAYMENT = 'paused_due_nonpayment';
    const WORKFLOW_STEP_COMPLETE = 'complete';

    public static function getAvailableReps(): array
    {
        return Application::getAvailableReps();
    }

    public static function getAvailableWorkflowSteps(): array
    {
        return [
            self::WORKFLOW_STEP_NEW,
            self::WORKFLOW_STEP_UP_FOR_BID,
            self::WORKFLOW_STEP_ASSIGNED,
            self::WORKFLOW_STEP_COMPLETE,
            self::WORKFLOW_STEP_PAUSED_DUE_NONPAYMENT
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_NEW => [
                'name' => 'New',
                'is_default' => true
            ],
            self::STATUS_UP_FOR_BID => [
                'name' => 'Up for bid',
                'is_default' => false
            ],
            self::STATUS_ASSIGNED => [
                'name' => 'Assigned',
                'is_default' => false
            ],
            self::STATUS_COMPLETE => [
                'name' => 'Complete',
                'is_default' => false
            ],
            self::STATUS_PAUSED_DUE_NONPAYMENT => [
                'name' => 'Paused Due to Nonpayment',
                'is_default' => false
            ],
        ];
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isAssignedToUser(User $user): bool
    {
        return $this->getTeacher() && $this->getTeacher()->getId() == $user->getId();
    }

    public function isUpForBids(): bool
    {
        return $this->getStatus()->getId() === self::STATUS_UP_FOR_BID;
    }

    public function isAssigned(): bool
    {
        return $this->getStatus()->getId() === self::STATUS_ASSIGNED;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string|null
     */
    public function getCourseName(): ?string
    {
        return $this->courseName;
    }

    /**
     * @param string|null $courseName
     */
    public function setCourseName(?string $courseName): void
    {
        $this->courseName = $courseName;
    }

    /**
     * @return string|null
     */
    public function getCoursePrefixes(): ?string
    {
        return $this->coursePrefixes;
    }

    /**
     * @param string|null $coursePrefixes
     */
    public function setCoursePrefixes(?string $coursePrefixes): void
    {
        $this->coursePrefixes = $coursePrefixes;
    }

    public function getWorkTodayLabel()
    {
        return $this->workToday ? 'Yes' : 'No';
    }

    /**
     * @return bool|null
     */
    public function getWorkToday(): ?bool
    {
        return $this->workToday;
    }

    /**
     * @param bool|null $workToday
     */
    public function setWorkToday(?bool $workToday): void
    {
        $this->workToday = $workToday;
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
     * @return string|null
     */
    public function getCourseUrl(): ?string
    {
        return $this->courseUrl;
    }

    /**
     * @param string|null $courseUrl
     */
    public function setCourseUrl(?string $courseUrl): void
    {
        $this->courseUrl = $courseUrl;
    }

    /**
     * @return string|null
     */
    public function getUserLogin(): ?string
    {
        return $this->userLogin;
    }

    /**
     * @param string|null $userLogin
     */
    public function setUserLogin(?string $userLogin): void
    {
        $this->userLogin = $userLogin;
    }

    /**
     * @return string|null
     */
    public function getUserPassword(): ?string
    {
        return $this->userPassword;
    }

    /**
     * @param string|null $userPassword
     */
    public function setUserPassword(?string $userPassword): void
    {
        $this->userPassword = $userPassword;
    }

    /**
     * @return string|null
     */
    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    /**
     * @param string|null $instructions
     */
    public function setInstructions(?string $instructions): void
    {
        $this->instructions = $instructions;
    }

    /**
     * @return float|null
     */
    public function getAmountDueToday(): ?float
    {
        return $this->amountDueToday;
    }

    /**
     * @param float|null $amountDueToday
     */
    public function setAmountDueToday(?float $amountDueToday): void
    {
        $this->amountDueToday = $amountDueToday;
    }

    /**
     * @return float|null
     */
    public function getAssignmentValue(): ?float
    {
        return $this->assignmentValue;
    }

    /**
     * @param float|null $assignmentValue
     */
    public function setAssignmentValue(?float $assignmentValue): void
    {
        $this->assignmentValue = $assignmentValue;
    }

    /**
     * @return boolean|null
     */
    public function getInvoiceDueTodayPaid(): ?bool
    {
        return $this->invoiceDueTodayPaid;
    }

    /**
     * @param boolean|null $invoiceDueTodayPaid
     */
    public function setInvoiceDueTodayPaid(?bool $invoiceDueTodayPaid): void
    {
        $this->invoiceDueTodayPaid = $invoiceDueTodayPaid;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Application|null
     */
    public function getApplication(): ?Application
    {
        return $this->application;
    }

    /**
     * @param Application|null $application
     */
    public function setApplication(?Application $application): void
    {
        $this->application = $application;
    }

    /**
     * @return User
     */
    public function getTeacher(): ?User
    {
        return $this->teacher;
    }

    /**
     * @param User $teacher
     */
    public function setTeacher(User $teacher): void
    {
        $this->teacher = $teacher;
    }

    /**
     * @return User
     */
    public function getStudent(): ?User
    {
        return $this->student;
    }

    /**
     * @param User $student
     */
    public function setStudent(User $student): void
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
     * @return Contact|null
     */
    public function getTeacherContact(): ?Contact
    {
        return $this->teacherContact;
    }

    /**
     * @param Contact|null $teacherContact
     */
    public function setTeacherContact(?Contact $teacherContact): void
    {
        $this->teacherContact = $teacherContact;
    }

    /**
     * @return Account|null
     */
    public function getTeacherAccount(): ?Account
    {
        return $this->teacherAccount;
    }

    /**
     * @param Account|null $teacherAccount
     */
    public function setTeacherAccount(?Account $teacherAccount): void
    {
        $this->teacherAccount = $teacherAccount;
    }

    /**
     * @return Collection|TeacherGroup[]
     */
    public function getTeacherGroups()
    {
        return $this->teacherGroups;
    }

    /**
     * @param Collection|TeacherGroup[] $teacherGroups
     */
    public function setTeacherGroups($teacherGroups): void
    {
        $this->teacherGroups = $teacherGroups;
    }

    /**
     * @return Collection|Bid[]
     */
    public function getBids()
    {
        return $this->bids;
    }

    /**
     * @param Collection|Bid[] $bids
     */
    public function setBids($bids): void
    {
        $this->bids = $bids;
    }

    /**
     * @return User
     */
    public function getCourseManager(): ?User
    {
        return $this->courseManager;
    }

    /**
     * @param User $courseManager
     */
    public function setCourseManager(User $courseManager): void
    {
        $this->courseManager = $courseManager;
    }

    /**
     * @return Organization
     */
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     */
    public function setOrganization(Organization $organization): void
    {
        $this->organization = $organization;
    }
}
