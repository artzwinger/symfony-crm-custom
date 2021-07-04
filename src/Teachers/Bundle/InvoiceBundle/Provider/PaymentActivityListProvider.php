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
use Teachers\Bundle\InvoiceBundle\Entity\Payment;

/**
 * Provides a way to use Payment entity in an activity list.
 */
class PaymentActivityListProvider implements
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
            Payment::class,
            $accessible
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($entity): ?string
    {
        /** @var $entity Payment */
        return 'Payment from ' . $this->dateTimeFormatter->format($entity->getCreatedAt());
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($entity): ?string
    {
        /** @var $entity Payment */
        $desc = 'Payment with amount paid $' . number_format($entity->getAmountPaid(), 2);
        if ($entity->getAmountRefunded()) {
            $desc .= ' and amount refunded $' . number_format($entity->getAmountRefunded(), 2);
        }
        return $desc;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(ActivityList $activityListEntity): array
    {
        /** @var Payment $invoice */
        $invoice = $this->doctrineHelper
            ->getEntityManager($activityListEntity->getRelatedActivityClass())
            ->getRepository($activityListEntity->getRelatedActivityClass())
            ->find($activityListEntity->getRelatedActivityId());

        if (!$invoice->getStatus()) {
            return [
                'statusId' => null,
                'statusName' => null,
            ];
        }

        return [
            'statusId' => $invoice->getStatus()->getId(),
            'statusName' => $invoice->getStatus()->getName(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner($entity): ?User
    {
        /** @var $entity Payment */
        return $entity->getOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt($entity): ?DateTime
    {
        /** @var $entity Payment */
        return $entity->getCreatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt($entity): ?DateTime
    {
        /** @var $entity Payment */
        return $entity->getUpdatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization($activityEntity): ?Organization
    {
        /** @var $activityEntity Payment */
        return $activityEntity->getOrganization();
    }

    /**
     * {@inheritdoc
     */
    public function getTemplate(): string
    {
        return 'TeachersInvoiceBundle:Payment:js/activityItemTemplate.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes($activityEntity): array
    {
        return [
            'itemView' => 'teachers_payment_info',
            'itemEdit' => 'teachers_payment_update',
            'itemDelete' => 'teachers_payment_delete'
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
            return $entity instanceof Payment;
        }

        return $entity === Payment::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntities($entity)
    {
        /** @var Payment $entity */
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
