<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class AdminRoleAccessTest extends TestCase
{
    public function test_master_can_access_every_admin_route(): void
    {
        $master = new User(['user_type' => User::TYPE_ADMIN_MASTER]);

        $this->assertTrue($master->isAdmin());
        $this->assertTrue($master->isMasterAdmin());
        $this->assertTrue($master->canAccessAdminRoute('admin.orders.index'));
        $this->assertTrue($master->canAccessAdminRoute('admin.payments.index'));
    }

    public function test_editor_can_access_only_editorial_admin_areas(): void
    {
        $editor = new User(['user_type' => User::TYPE_ADMIN_EDITOR]);

        $this->assertTrue($editor->isAdmin());
        $this->assertTrue($editor->isAdminEditor());
        $this->assertTrue($editor->canAccessAdminRoute('admin.index'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.products.edit'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.products.ai-batches.index'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.products.outlet.update'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.brands.update'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.subcategories.create'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.categorias-filhas.store'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.blogs.update'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.blog-categories.index'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.palace.edit'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.bridal.update'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.cafe_bistro.edit'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.institucional.update'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.banners.index'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.home-banners.update'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.sections_home.update'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.activate.toggle'));
        $this->assertTrue($editor->canAccessAdminRoute('admin.clear-cache'));

        $this->assertFalse($editor->canAccessAdminRoute('admin.orders.index'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.clients.index'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.categories.index'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.contatos.index'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.emails.create'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.notifications.index'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.payments.index'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.languages.index'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.marketing.edit'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.theme-settings.edit'));
        $this->assertFalse($editor->canAccessAdminRoute('admin.users.updateType'));
    }

    public function test_customer_is_not_an_admin(): void
    {
        $customer = new User(['user_type' => User::TYPE_CUSTOMER]);

        $this->assertFalse($customer->isAdmin());
        $this->assertFalse($customer->canAccessAdminRoute('admin.index'));
    }
}
