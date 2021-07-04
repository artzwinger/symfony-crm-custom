<?php

namespace Teachers\Bundle\InvoiceBundle\Provider;

use DateTime;
use Oro\Bundle\ActivityBundle\Tools\ActivityAssociationHelper;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\ActivityListBundle\Model\ActivityListDateProviderInterface;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use Oro\Bundle\CommentBundle\Model\CommentProviderInterface;
use Oro\Bundle\CommentBundle\Tools\CommentAssociationHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\DependencyInjection\ServiceLink;
use Teachers\Bundle\InvoiceBundle\Entity\Refund;
use function is_object;

/**
 * Provides a way to use Refund entity in an activity list.
 */
class RefundActivityListProvider implements
    ActivityListProviderInterface,
    CommentProviderInterface,
    ActivityListDateProviderInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ServiceLink */
    protected $entityOwnerAccessorLink;

    /** @var ActivityAssociationHelper */
    protected $activityAssociationHelper;

    /** @var CommentAssociationHelper */
    protected $commentAssociationHelper;
    /**
     * @var DateTimeFormatterInterface
     */
    private $dateTimeFormatter;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ServiceLink $entityOwnerAccessorLink
     * @param ActivityAssociationHelper $activityAssociationHelper
     * @param CommentAssociationHelper $commentAssociationHelper
     * @param DateTimeFormatterInterface $dateTimeFormatter
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ServiceLink $entityOwnerAccessorLink,
        ActivityAssociationHelper $activityAssociationHelper,
        CommentAssociationHelper $commentAssociationHelper,
        DateTimeFormatterInterface $dateTimeFormatter
    )
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->entityOwnerAccessorLink = $entityOwnerAccessorLink;
        $this->activityAssociationHelper = $activityAssociationHelper;
        $this->commentAssociationHelper = $commentAssociationHelper;
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableTarget($entityClass, $accessible = true): bool
    {
        return $this->activityAssociationHelper->isActivityAssociationEnabled(
            $entityClass,
            Refund::class,
            $accessible
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($entity): ?string
    {
        /** @var $entity Refund */
        return 'Refund made at ' . $this->dateTimeFormatter->format($entity->getCreatedAt());
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($entity): ?string
    {
        /** @var $entity Refund */
        return 'Refund with amount refunded $' . number_format($entity->getAmountRefunded(), 2);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(ActivityList $activityListEntity): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner($entity): ?User
    {
        /** @var $entity Refund */
        return $entity->getOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt($entity): ?DateTime
    {
        /** @var $entity Refund */
        return $entity->getCreatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt($entity): ?DateTime
    {
        /** @var $entity Refund */
        return $entity->getUpdatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization($activityEntity): ?Organization
    {
        /** @var $activityEntity Refund */
        return $activityEntity->getOrganization();
    }

    /**
     * {@inheritdoc
     */
    public function getTemplate(): string
    {
        return 'TeachersInvoiceBundle:Refund:js/activityItemTemplate.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes($activityEntity): array
    {
        return [
            'itemView' => 'teachers_refund_info',
            'itemEdit' => 'teachers_refund_update',
            'itemDelete' => 'teachers_refund_delete'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityId($entity)
    {
        return $this->doctrineHelper->getSingleEntityIdentifier($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($entity): bool
    {
        if (is_object($entity)) {
            return $entity instanceof Refund;
        }

        return $entity === Refund::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntities($entity)
    {
        /** @var Refund $entity */
        return $entity->getActivityTargets();
    }

    /**
     * {@inheritdoc}
     */
    public function isCommentsEnabled($entityClass): bool
    {
        return $this->commentAssociationHelper->isCommentAssociationEnabled($entityClass);
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityOwners($entity, ActivityList $activityList): array
    {
        $org = $this->getOrganization($entity);
        $owner = $this->entityOwnerAccessorLink->getService()->getOwner($entity);

        if (!$org || !$owner) {
            return [];
        }

        $activityOwner = new ActivityOwner();
        $activityOwner->setActivity($activityList);
        $activityOwner->setOrganization($org);
        $activityOwner->setUser($owner);
        return [$activityOwner];
    }
}
