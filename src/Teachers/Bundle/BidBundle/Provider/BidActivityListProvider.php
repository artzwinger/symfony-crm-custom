<?php

namespace Teachers\Bundle\BidBundle\Provider;

use DateTime;
use Oro\Bundle\ActivityBundle\Tools\ActivityAssociationHelper;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\ActivityListBundle\Model\ActivityListDateProviderInterface;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use Oro\Bundle\CommentBundle\Model\CommentProviderInterface;
use Oro\Bundle\CommentBundle\Tools\CommentAssociationHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\DependencyInjection\ServiceLink;
use Teachers\Bundle\BidBundle\Entity\Bid;

/**
 * Provides a way to use Bid entity in an activity list.
 */
class BidActivityListProvider implements
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
     * @param DoctrineHelper $doctrineHelper
     * @param ServiceLink $entityOwnerAccessorLink
     * @param ActivityAssociationHelper $activityAssociationHelper
     * @param CommentAssociationHelper $commentAssociationHelper
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ServiceLink $entityOwnerAccessorLink,
        ActivityAssociationHelper $activityAssociationHelper,
        CommentAssociationHelper $commentAssociationHelper
    )
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->entityOwnerAccessorLink = $entityOwnerAccessorLink;
        $this->activityAssociationHelper = $activityAssociationHelper;
        $this->commentAssociationHelper = $commentAssociationHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableTarget($entityClass, $accessible = true): bool
    {
        return $this->activityAssociationHelper->isActivityAssociationEnabled(
            $entityClass,
            Bid::class,
            $accessible
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($entity): ?string
    {
        /** @var $entity Bid */
        return $entity->getSubject();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($entity): ?string
    {
        /** @var $entity Bid */
        return $entity->getSubject() . ' ' . $entity->getPrice();
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
        /** @var $entity Bid */
        return $entity->getTeacher();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt($entity): ?DateTime
    {
        /** @var $entity Bid */
        return $entity->getCreatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt($entity): ?DateTime
    {
        /** @var $entity Bid */
        return $entity->getUpdatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization($activityEntity): ?Organization
    {
        /** @var $activityEntity Bid */
        return $activityEntity->getOrganization();
    }

    /**
     * {@inheritdoc
     */
    public function getTemplate(): string
    {
        return 'TeachersBidBundle:Bid:js/activityItemTemplate.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes($activityEntity): array
    {
        return [
            'itemView' => 'teachers_bid_info',
            'itemEdit' => 'teachers_bid_update',
            'itemDelete' => 'teachers_bid_delete'
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
        if (\is_object($entity)) {
            return $entity instanceof Bid;
        }

        return $entity === Bid::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntities($entity)
    {
        /** @var Bid $entity */
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
    public function getActivityOwners($bid, ActivityList $activityList): array
    {
        $org = $this->getOrganization($bid);
        $owner = $this->entityOwnerAccessorLink->getService()->getOwner($bid);

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
