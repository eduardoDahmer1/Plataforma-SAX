<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('languages')) {
            return;
        }

        $now = now();
        $translations = [
            ['key' => 'admin_editor', 'pt' => 'Admin/Editor', 'en' => 'Admin/Editor', 'es' => 'Admin/Editor'],
            ['key' => 'usuario_desconhecido', 'pt' => 'Desconhecido', 'en' => 'Unknown', 'es' => 'Desconocido'],
            ['key' => 'admin_access_denied', 'pt' => 'Você não tem permissão para acessar esta área administrativa.', 'en' => 'You do not have permission to access this administrative area.', 'es' => 'No tienes permiso para acceder a esta área administrativa.'],
            ['key' => 'user_created_successfully', 'pt' => 'Usuário criado com sucesso!', 'en' => 'User created successfully!', 'es' => '¡Usuario creado correctamente!'],
            ['key' => 'cannot_change_own_master_role', 'pt' => 'Você não pode remover o seu próprio acesso de Admin Master.', 'en' => 'You cannot remove your own Admin Master access.', 'es' => 'No puedes quitar tu propio acceso de Admin Master.'],
            ['key' => 'cannot_remove_last_master', 'pt' => 'Não é possível remover o último Admin Master do sistema.', 'en' => 'The last Admin Master cannot be removed from the system.', 'es' => 'No se puede eliminar el último Admin Master del sistema.'],
            ['key' => 'user_type_updated_successfully', 'pt' => 'Tipo de usuário atualizado com sucesso.', 'en' => 'User type updated successfully.', 'es' => 'Tipo de usuario actualizado correctamente.'],
            ['key' => 'cannot_delete_own_user', 'pt' => 'Você não pode excluir o seu próprio usuário.', 'en' => 'You cannot delete your own user account.', 'es' => 'No puedes eliminar tu propio usuario.'],
            ['key' => 'change_user_type', 'pt' => 'Alterar tipo de usuário', 'en' => 'Change user type', 'es' => 'Cambiar tipo de usuario'],
            ['key' => 'own_master_role_locked', 'pt' => 'Seu próprio acesso Master fica protegido.', 'en' => 'Your own Master access is protected.', 'es' => 'Tu propio acceso Master está protegido.'],
            ['key' => 'products_sort_newest_added', 'pt' => 'Adicionados recentemente', 'en' => 'Recently added', 'es' => 'Agregados recientemente'],
            ['key' => 'products_registration_date', 'pt' => 'Data de cadastro', 'en' => 'Registration date', 'es' => 'Fecha de registro'],
            ['key' => 'products_all_dates', 'pt' => 'Todas as datas', 'en' => 'All dates', 'es' => 'Todas las fechas'],
            ['key' => 'products_with_registered_date', 'pt' => 'Com data registrada', 'en' => 'With registered date', 'es' => 'Con fecha registrada'],
            ['key' => 'products_without_registered_date', 'pt' => 'Sem data registrada', 'en' => 'Without registered date', 'es' => 'Sin fecha registrada'],
            ['key' => 'product_audit_created', 'pt' => 'Adicionado ao sistema', 'en' => 'Added to the system', 'es' => 'Agregado al sistema'],
            ['key' => 'product_audit_created_title', 'pt' => 'Data em que o produto foi adicionado', 'en' => 'Date the product was added', 'es' => 'Fecha en que se agregó el producto'],
            ['key' => 'product_audit_created_at', 'pt' => 'Adicionado em :date', 'en' => 'Added on :date', 'es' => 'Agregado el :date'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $translation) => $translation + ['created_at' => $now, 'updated_at' => $now],
            $translations
        ));
    }

    public function down(): void
    {
        // As traduções podem ter sido personalizadas no painel; não as removemos no rollback.
    }
};
