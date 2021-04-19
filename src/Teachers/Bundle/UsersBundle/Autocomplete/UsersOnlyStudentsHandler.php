<?php

namespace Teachers\Bundle\UsersBundle\Autocomplete;

use Oro\Bundle\UserBundle\Autocomplete\UserSearchHandler;
use Teachers\Bundle\UsersBundle\Helper\Role;

class UsersOnlyStudentsHandler extends UserSearchHandler
{
    /**
     * {@inheritdoc}
     */
    public function convertItem($user): ?array
    {
        if (!$user->hasRole(Role::ROLE_STUDENT)) {
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
