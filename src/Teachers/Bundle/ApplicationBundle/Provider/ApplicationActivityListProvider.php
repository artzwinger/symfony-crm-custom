<?php

namespace Teachers\Bundle\ApplicationBundle\Provider;

use Oro\Bundle\ActivityBundle\Tools\ActivityAssociationHelper;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\ActivityListBundle\Model\ActivityListDateProviderInterface;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use Oro\Bundle\CommentBundle\Model\CommentProviderInterface;
use Oro\Bundle\CommentBundle\Tools\CommentAssociationHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\DependencyInjection\ServiceLink;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

/**
 * Provides a way to use Application entity in an activity list.
 */
class ApplicationActivityListProvider implements
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
    public function isApplicableTarget($entityClass, $accessible = true)
    {
        return $this->activityAssociationHelper->isActivityAssociationEnabled(
            $entityClass,
            Application::class,
            $accessible
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($entity)
    {
        /** @var $entity Application */
        return $entity->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($entity)
    {
        /** @var $entity Application */
        return trim(strip_tags($entity->getDescription()));
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner($entity)
    {
        /** @var $entity Application */
        return $entity->getOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt($entity)
    {
        /** @var $entity Application */
        return $entity->getCreatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt($entity)
    {
        /** @var $entity Application */
        return $entity->getUpdatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getData(ActivityList $activityListEntity)
    {
        /** @var Application $application */
        $application = $this->doctrineHelper
            ->getEntityManager($activityListEntity->getRelatedActivityClass())
            ->getRepository($activityListEntity->getRelatedActivityClass())
            ->find($activityListEntity->getRelatedActivityId());

        if (!$application->getStatus()) {
            return [
                'statusId' => null,
                'statusName' => null,
            ];
        }

        return [
            'statusId' => $application->getStatus()->getId(),
            'statusName' => $application->getStatus()->getName(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization($activityEntity)
    {
        /** @var $activityEntity Application */
        return $activityEntity->getOwner();
    }

    /**
     * {@inheritdoc
     */
    public function getTemplate()
    {
        return 'TeachersApplicationBundle:Application:js/activityItemTemplate.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes($activityEntity)
    {
        return [
            'itemView' => 'teachers_application_widget_info',
            'itemEdit' => 'teachers_application_update'
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
    public function isApplicable($entity)
    {
        if (\is_object($entity)) {
            return $entity instanceof Application;
        }

        return $entity === Application::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntities($entity)
    {
        return $entity->getActivityTargets();
    }

    /**
     * {@inheritdoc}
     */
    public function isCommentsEnabled($entityClass)
    {
        return $this->commentAssociationHelper->isCommentAssociationEnabled($entityClass);
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityOwners($entity, ActivityList $activityList)
    {
        $organization = $this->getOrganization($entity);
        $owner = $this->entityOwnerAccessorLink->getService()->getOwner($entity);

        if (!$organization || !$owner) {
            return [];
        }

        $activityOwner = new ActivityOwner();
        $activityOwner->setActivity($activityList);
        $activityOwner->setOrganization($organization);
        $activityOwner->setUser($owner);
        return [$activityOwner];
    }
}
