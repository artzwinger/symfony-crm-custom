<?php

namespace Teachers\Bundle\SatisfactionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Teachers\Bundle\SatisfactionBundle\Model\ExtendSatisfaction;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\SatisfactionBundle\Entity\Repository\SatisfactionRepository")
 * @ORM\Table(
 *      name="teachers_satisfaction",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *      "entity"={
 *          "icon"="fa-smile"
 *      },
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"="",
 *          "category"="satisfaction"
 *      },
 *      "grouping"={"groups"={"activity"}}
 *  }
 * )
 */
class Satisfaction extends ExtendSatisfaction implements DatesAwareInterface
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
     * @var Satisfaction $satisfaction
     *
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\SatisfactionBundle\Entity\Satisfaction")
     * @ORM\JoinColumn(name="satisfaction_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $satisfaction;
    /**
     * @var int $id
     *
     * @ORM\Column(type="smallint")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $rate;
}
