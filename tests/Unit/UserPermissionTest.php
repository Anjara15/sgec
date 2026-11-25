<?php

namespace App\Tests\Unit;

use App\Entity\Role;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserPermissionTest extends TestCase
{
    public function testAdminHasAllPermissions(): void
    {
        $user = (new User())->setRoles(['ROLE_ADMIN']);
        $this->assertTrue($user->hasPermission('courrier.validate'));
    }

    public function testCustomRolePermission(): void
    {
        $role = (new Role())->setNom('Validateur')->setPermissions(['courrier.validate']);
        $user = (new User())->setRoles(['ROLE_USER'])->addCustomRole($role);

        $this->assertTrue($user->hasPermission('courrier.validate'));
        $this->assertFalse($user->hasPermission('user.manage'));
    }
}
