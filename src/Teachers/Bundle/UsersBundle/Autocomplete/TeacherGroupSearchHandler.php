<?php

namespace Teachers\Bundle\UsersBundle\Autocomplete;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Teachers\Bundle\UsersBundle\Entity\TeacherGroup;

/**
 * ORM search for contact reasons by default title
 */
class TeacherGroupSearchHandler implements SearchHandlerInterface
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var PropertyAccessor */
    private $propertyAccessor;

    /** @var array */
    private $displayFields = ['title'];

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param PropertyAccessor $propertyAccessor
     */
    public function __construct(DoctrineHelper $doctrineHelper, PropertyAccessor $propertyAccessor)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage, $searchById = false): array
    {
        $repository = $this->doctrineHelper->getEntityRepository(TeacherGroup::class);
        $queryBuilder = $repository->createQueryBuilder('teacher_group');

        if ($query) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('teacher_group.title', ':tt'));
            $queryBuilder->setParameter('tt', '%' . $query . '%');
        }

        /** @var TeacherGroup[] $result */
        $result = $queryBuilder->getQuery()->getResult();

        $data = [];
        foreach ($result as $item) {
            $data[] = $this->convertItem($item);
        }

        return [
            'results' => $data,
            'more' => false
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties(): array
    {
        return $this->displayFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return TeacherGroup::class;
    }

    /**
     * {@inheritdoc}
     */
    public function convertItem($item): array
    {
        $result = [];

        $idFieldName = 'id';
        $result[$idFieldName] = $this->propertyAccessor->getValue($item, $idFieldName);

        foreach ($this->getProperties() as $field) {
            $result[$field] = (string)$this->propertyAccessor->getValue($item, $field);
        }

        return $result;
    }
}
