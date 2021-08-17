<?php

namespace Teachers\Bundle\ApplicationBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\ApplicationBundle\Model\ExtendApplication;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\ApplicationBundle\Entity\Repository\ApplicationRepository")
 * @ORM\Table(
 *      name="teachers_application",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="teachers_application_index",
 *      routeView="teachers_application_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-clipboard"
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="application"
 *          },
 *          "grid"={
 *              "default"="teachers-applications-grid"
 *          }
 *      }
 * )
 */
class Application extends ExtendApplication implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var int|null $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="teachers_application_id_seq", initialValue=1000)
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
     * @var string|null $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    protected $email;

    /**
     * @var string|null $phone
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=false)
     */
    protected $phone;

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
     * @ORM\Column(name="work_today", type="boolean", nullable=true)
     */
    protected $workToday = true;

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
     * @var double|null $price
     *
     * @ORM\Column(name="price", type="money", nullable=true)
     */
    protected $price;

    /**
     * @var Organization|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;
    /**
     * @var User|null $student
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
     * @var string|null $reCaptchaToken
     */
    protected $reCaptchaToken;

    const STATUS_NEW = 'new';
    const STATUS_WORKING = 'working';
    const STATUS_COMPLETE = 'complete';

    const REP_SALES = 'sales';
    const REP_JOEL = 'joel';

    const TERM_SHORT = 'short';
    const TERM_LONG = 'long';

    const WORKFLOW_STEP_NEW = 'new';
    const WORKFLOW_STEP_WORKING = 'working';
    const WORKFLOW_STEP_COMPLETE = 'complete';

    public static function getAvailableWorkflowSteps(): array
    {
        return [
            self::WORKFLOW_STEP_NEW,
            self::WORKFLOW_STEP_WORKING,
            self::WORKFLOW_STEP_COMPLETE
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_NEW => [
                'name' => 'New',
                'is_default' => true
            ],
            self::STATUS_WORKING => [
                'name' => 'Working',
                'is_default' => false
            ],
            self::STATUS_COMPLETE => [
                'name' => 'Complete',
                'is_default' => false
            ],
        ];
    }

    public static function getAvailableReps(): array
    {
        return [
            self::REP_SALES => [
                'name' => 'Sales',
                'is_default' => true
            ],
            self::REP_JOEL => [
                'name' => 'Joel',
                'is_default' => false
            ],
        ];
    }

    public static function getAvailableTerms(): array
    {
        return [
            self::TERM_SHORT => [
                'name' => 'Short-term',
                'is_default' => true
            ],
            self::TERM_LONG => [
                'name' => 'Long-term',
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
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
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

    public function getWorkTodayLabel()
    {
        return $this->workToday ? 'Yes' : 'No';
    }

    /**
     * @return bool|null
     */
    public function getWorkToday() // do not add types
    {
        return $this->workToday;
    }

    /**
     * @param bool|null $workToday
     */
    public function setWorkToday($workToday): void // do not add types
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
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return Organization|null
     */
    public function getOwner(): ?Organization
    {
        return $this->owner;
    }

    /**
     * @param Organization|null $owner
     */
    public function setOwner(?Organization $owner): void
    {
        $this->owner = $owner;
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
     * @return string|null
     */
    public function getReCaptchaToken(): ?string
    {
        return $this->reCaptchaToken;
    }

    /**
     * @param string|null $reCaptchaToken
     */
    public function setReCaptchaToken(?string $reCaptchaToken): void
    {
        $this->reCaptchaToken = $reCaptchaToken;
    }

    public function __toString(): string
    {
        return (string)$this->getCourseName();
    }
}
