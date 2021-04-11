<?php

namespace Teachers\Bundle\ApplicationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
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
     * @var int $id
     *
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
     * @var string|null $subject
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $subject;

    /**
     * @var string|null $description
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string|null $studentLoginInfo
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $studentLoginInfo;

    /**
     * @var double $price
     *
     * @ORM\Column(name="price", type="money", nullable=true)
     */
    protected $price;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    const STATUS_NEW = 'new';
    const STATUS_WORKING = 'working';
    const STATUS_COMPLETE = 'complete';

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
     * @return string|null
     */
    public function getStudentLoginInfo(): ?string
    {
        return $this->studentLoginInfo;
    }

    /**
     * @param string|null $studentLoginInfo
     */
    public function setStudentLoginInfo(?string $studentLoginInfo): void
    {
        $this->studentLoginInfo = $studentLoginInfo;
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
     * @return Organization|null
     */
    public function getOwner(): ?Organization
    {
        return $this->owner;
    }

    /**
     * @param Organization $owningOrganization
     * @return self
     */
    public function setOwner(Organization $owningOrganization): Application
    {
        $this->owner = $owningOrganization;
        return $this;
    }
}
