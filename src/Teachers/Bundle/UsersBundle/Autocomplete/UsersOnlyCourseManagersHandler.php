<?php

namespace Teachers\Bundle\UsersBundle\Autocomplete;

use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\UserBundle\Autocomplete\UserSearchHandler;
use Teachers\Bundle\UsersBundle\Helper\Role;

class UsersOnlyCourseManagersHandler extends UserSearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function searchIds($search, $firstResult, $maxResults): array
    {
        $queryObj = $this->indexer->select()
            ->from($this->entitySearchAlias);
        $queryObj->getCriteria()
            ->setMaxResults((int)$maxResults)
            ->setFirstResult((int)$firstResult);

        if ($search) {
            $field = Criteria::implodeFieldTypeName(Query::TYPE_TEXT, Indexer::TEXT_ALL_DATA_FIELD);
            $queryObj->getCriteria()->andWhere(Criteria::expr()->contains($field, $search));
        }

        $field = Criteria::implodeFieldTypeName(Query::TYPE_TEXT, Role::SEARCH_FIELD_NAME);
        $queryObj->getCriteria()->andWhere(Criteria::expr()->contains($field, Role::ROLE_COURSE_MANAGER));

        $ids = [];
        $result = $this->indexer->query($queryObj);
        $elements = $result->getElements();

        foreach ($elements as $element) {
            $ids[] = $element->getRecordId();
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function convertItem($user): ?array
    {
        if (!$user->hasRole(Role::ROLE_COURSE_MANAGER)) {
            return null;
        }
        return parent::convertItem($user);
    }

    /**
     * {@inheritdoc}
     */
    protected function convertItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $converted = $this->convertItem($item);
            if ($converted !== null) {
                $result[] = $converted;
            }
        }
        return $result;
    }
}
