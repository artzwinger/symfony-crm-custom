<?php

namespace Teachers\Bundle\AssignmentBundle\Provider;

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
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentPrivateNote;

/**
 * Provides a way to use Assignment entity in an activity list.
 */
class AssignmentPrivateNoteActivityListProvider implements
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
            AssignmentPrivateNote::class,
            $accessible
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($entity): ?string
    {
        /** @var $entity AssignmentPrivateNote */
        return $entity->getOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($entity): ?string
    {
        /** @var $entity AssignmentPrivateNote */
        return $entity->getMessage();
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
        /** @var $entity AssignmentPrivateNote */
        return $entity->getOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt($entity): ?DateTime
    {
        /** @var $entity AssignmentPrivateNote */
        return $entity->getCreatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt($entity): ?DateTime
    {
        /** @var $entity AssignmentPrivateNote */
        return $entity->getUpdatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization($activityEntity): ?Organization
    {
        /** @var $activityEntity AssignmentPrivateNote */
        return $activityEntity->getOrganization();
    }

    /**
     * {@inheritdoc
     */
    public function getTemplate(): string
    {
        return 'TeachersAssignmentBundle:AssignmentPrivateNote:js/activityItemTemplate.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes($activityEntity): array
    {
        return [
            'itemView' => 'teachers_assignment_private_note_info',
            'itemEdit' => 'teachers_assignment_private_note_update',
            'itemDelete' => 'teachers_assignment_private_note_delete'
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
            return $entity instanceof AssignmentPrivateNote;
        }

        return $entity === AssignmentPrivateNote::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntities($entity)
    {
        /** @var $entity AssignmentPrivateNote */
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
