<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;
use Teachers\Bundle\UsersBundle\Helper\Role as RoleHelper;

class UnloadWorkflowPermissions extends AbstractLoadAclData
{
    /**
     * {@inheritdoc}
     */
    public function getDataPath(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAclData(): array
    {
        return [
            RoleHelper::ROLE_TEACHER => [
                'permissions' => [
                    'workflow:assignment_message_flow::__start__' => ['PERFORM_TRANSITIONS_BASIC'],
                ]
            ],
            RoleHelper::ROLE_STUDENT => [
                'permissions' => [
                    'workflow:assignment_message_flow::approve|unapprove' => ['PERFORM_TRANSITIONS_BASIC'],
                ]
            ],
        ];
    }

    /**
     * @param AclManager $aclManager
     * @param ObjectManager $objectManager
     * @param Role $role
     * @param array $roleConfigData
     */
    protected function processRole(AclManager $aclManager, $objectManager, Role $role, array $roleConfigData)
    {
        if (isset($roleConfigData['label'])) {
            $role->setLabel($roleConfigData['label']);
        }

        if (!$role->getId()) {
            $objectManager->persist($role);
        }

        if (isset($roleConfigData['permissions']) && $aclManager->isAclEnabled()) {
            $sid = $aclManager->getSid($role);
            foreach ($roleConfigData['permissions'] as $oid => $permissions) {
                foreach ($permissions as $permission) {
                    $permission = explode('::', $permission);
                    $oid = $aclManager->getOid($permission[0]);

                    $extension = $aclManager->getExtensionSelector()->select($oid);
                    $maskBuilders = $extension->getAllMaskBuilders();

                    foreach ($maskBuilders as $maskBuilder) {
                        $mask = $maskBuilder->reset()->get();

                        if (!empty($acls)) {
                            foreach ($acls as $acl) {
                                if ($maskBuilder->hasMask('MASK_' . $acl)) {
                                    $mask = $maskBuilder->add($acl)->get();
                                }
                            }
                        }

                        $aclManager->setFieldPermission($sid, $oid, $permission[1], $mask);
                    }
                }
            }
        }
    }
}
