<?php

namespace Teachers\Bundle\BidBundle\Entity;

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
use Teachers\Bundle\BidBundle\Model\ExtendBid;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\BidBundle\Entity\Repository\BidRepository")
 * @ORM\Table(
 *      name="teachers_bid",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="teachers_bid_index",
 *      routeView="teachers_bid_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-briefcase"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="teacher",
 *              "owner_column_name"="teacher_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="bid"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="teachers_bid_activity_view",
 *              "acl"="teachers_bid_view",
 *              "action_button_widget"="teachers_bid_button",
 *              "action_link_widget"="teachers_bid_link"
 *          },
 *          "grid"={
 *              "default"="bids-grid"
 *          }
 *      }
 * )
 */
class Bid extends ExtendBid implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var int $id
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

    // removed
    protected $subject;
    /**
     * @var float|null $price
     * @ORM\Column(name="price", type="money", nullable=false)
     */
    protected $price;
    /**
     * @var User|null Teacher
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="teacher_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $teacher;
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
     * @var Organization|null
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    const STATUS_PENDING = 'pending';
    const STATUS_WINNING = 'winning';

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PENDING => [
                'name' => 'Pending',
                'is_default' => true
            ],
            self::STATUS_WINNING => [
                'name' => 'Winning',
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
     * @return Assignment|null
     */
    public function getAssignment(): ?Assignment
    {
        foreach ($this->getActivityTargets() as $target) {
            if ($target instanceof Assignment) {
                return $target;
            }
        }
        return null;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return User|null
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
     * @return Organization|null
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
