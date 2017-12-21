<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 06/08/14
 * Time: 15:36
 */

namespace Networking\InitCmsBundle\Security;

use Sonata\UserBundle\Security\EditableRolesBuilder as BaseRolesBuilder;

class EditableRolesBuilder extends BaseRolesBuilder
{


    /**
     * @param bool $domain
     * @param bool $expanded
     * @return array
     */
    public function getRoles($domain = false, $expanded = true)
    {

        return parent::getRoles($domain, $expanded);
        $roles = [];
        $rolesReadOnly = [];

        if (!$this->tokenStorage->getToken()) {
            return $roles;
        }

        // get roles from the Admin classes
        foreach ($this->pool->getAdminServiceIds() as $id) {
            try {
                $admin = $this->pool->getInstance($id);
            } catch (\Exception $e) {
                continue;
            }

            $isMaster = $admin->isGranted('MASTER');
            $securityHandler = $admin->getSecurityHandler();
            // TODO get the base role from the admin or security handler
            $baseRole = $securityHandler->getBaseRole($admin);

            foreach ($admin->getSecurityInformation() as $role => $permissions) {
                $role = sprintf($baseRole, $role);

                if ($isMaster) {
                    // if the user has the MASTER permission, allow to grant access the admin roles to other users
                    $roles[$role] = $role;
                } elseif ($this->authorizationChecker->isGranted($role)) {
                    // although the user has no MASTER permission, allow the currently logged in user to view the role
                    $rolesReadOnly[$role] = $role;
                }
            }
        }

        $isMaster = $this->authorizationChecker->isGranted(
            $this->pool->getOption('role_super_admin', 'ROLE_SUPER_ADMIN')
        );

        // get roles from the service container
        foreach ($this->rolesHierarchy as $name => $rolesHierarchy) {
            if ($this->authorizationChecker->isGranted($name) || $isMaster) {
                $roles[$name] = $name;

                foreach ($rolesHierarchy as $role) {
                    if (!isset($roles[$role])) {
                        $roles[$role] = $role;
                    }
                }
            }
        }

        return [$roles, $rolesReadOnly];
    }

} 