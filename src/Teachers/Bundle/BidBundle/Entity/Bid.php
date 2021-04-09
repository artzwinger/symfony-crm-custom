<?php

namespace Teachers\Bundle\BidBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Teachers\Bundle\BidBundle\Model\ExtendBid;
use Teachers\Bundle\UsersBundle\Entity\Teacher;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\BidBundle\Entity\Repository\BidRepository")
 * @ORM\Table(
 *      name="teachers_bid",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="teachers_bid_index",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-briefcase"
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
 *              "category"="bid"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="teachers_bid_activity_view",
 *              "acl"="teachers_bid_view",
 *              "action_button_widget"="oro_log_call_button",
 *              "action_link_widget"="oro_log_call_link"
 *          },
 *          "grid"={
 *              "default"="bids-grid",
 *              "context"="bid-for-context-grid"
 *          }
 *      }
 * )
 */
class Bid extends ExtendBid implements DatesAwareInterface
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
     * @var Teacher $teacher
     *
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\UsersBundle\Entity\Teacher")
     * @ORM\JoinColumn(name="teacher_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $teacher;
    /**
     * @var Bid $bid
     *
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\BidBundle\Entity\Bid")
     * @ORM\JoinColumn(name="bid_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $bid;
    /**
     * @var double $price
     *
     * @ORM\Column(name="amount", type="money", nullable=false)
     */
    protected $price;
}
