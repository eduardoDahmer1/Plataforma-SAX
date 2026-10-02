<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'rating_average') || ! Schema::hasColumn('products', 'rating_count')) {
            Schema::table('products', function (Blueprint $table): void {
                if (! Schema::hasColumn('products', 'rating_average')) {
                    $table->decimal('rating_average', 3, 2)->default(0)->after('views');
                }
                if (! Schema::hasColumn('products', 'rating_count')) {
                    $table->unsignedInteger('rating_count')->default(0)->after('rating_average');
                }
            });
        }

        if (! Schema::hasTable('product_reviews')) {
            Schema::create('product_reviews', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('author_name', 120)->nullable();
                $table->unsignedTinyInteger('rating');
                $table->string('title', 120)->nullable();
                $table->text('comment')->nullable();
                $table->string('status', 20)->default('approved');
                $table->boolean('verified_purchase')->default(false);
                $table->text('admin_note')->nullable();
                $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('moderated_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['product_id', 'user_id'], 'product_reviews_product_user_unique');
                $table->index(['product_id', 'status', 'created_at'], 'product_reviews_public_index');
                $table->index(['status', 'rating'], 'product_reviews_admin_index');
            });
        }

        if (Schema::hasTable('languages')) {
            $now = now();
            $translations = [
                ['key' => 'product_reviews', 'pt' => 'Avaliações do produto', 'en' => 'Product reviews', 'es' => 'Valoraciones del producto'],
                ['key' => 'product_review_singular', 'pt' => 'avaliação', 'en' => 'review', 'es' => 'valoración'],
                ['key' => 'product_reviews_plural', 'pt' => 'avaliações', 'en' => 'reviews', 'es' => 'valoraciones'],
                ['key' => 'review_write', 'pt' => 'Avaliar este produto', 'en' => 'Review this product', 'es' => 'Valorar este producto'],
                ['key' => 'review_edit', 'pt' => 'Editar minha avaliação', 'en' => 'Edit my review', 'es' => 'Editar mi valoración'],
                ['key' => 'review_rating', 'pt' => 'Sua nota', 'en' => 'Your rating', 'es' => 'Tu puntuación'],
                ['key' => 'review_title_label', 'pt' => 'Título (opcional)', 'en' => 'Title (optional)', 'es' => 'Título (opcional)'],
                ['key' => 'review_comment_label', 'pt' => 'Conte como foi sua experiência (opcional)', 'en' => 'Tell us about your experience (optional)', 'es' => 'Cuéntanos tu experiencia (opcional)'],
                ['key' => 'review_submit', 'pt' => 'Publicar avaliação', 'en' => 'Publish review', 'es' => 'Publicar valoración'],
                ['key' => 'review_update', 'pt' => 'Salvar alterações', 'en' => 'Save changes', 'es' => 'Guardar cambios'],
                ['key' => 'review_delete', 'pt' => 'Excluir avaliação', 'en' => 'Delete review', 'es' => 'Eliminar valoración'],
                ['key' => 'review_login_message', 'pt' => 'Entre na sua conta para avaliar este produto.', 'en' => 'Sign in to review this product.', 'es' => 'Inicia sesión para valorar este producto.'],
                ['key' => 'review_login_action', 'pt' => 'Entrar para avaliar', 'en' => 'Sign in to review', 'es' => 'Iniciar sesión para valorar'],
                ['key' => 'review_verified_purchase', 'pt' => 'Compra verificada', 'en' => 'Verified purchase', 'es' => 'Compra verificada'],
                ['key' => 'review_no_reviews', 'pt' => 'Este produto ainda não recebeu avaliações. Seja o primeiro a compartilhar sua experiência.', 'en' => 'This product has no reviews yet. Be the first to share your experience.', 'es' => 'Este producto aún no tiene valoraciones. Sé el primero en compartir tu experiencia.'],
                ['key' => 'review_average_label', 'pt' => 'Nota média', 'en' => 'Average rating', 'es' => 'Puntuación media'],
                ['key' => 'review_success_created', 'pt' => 'Sua avaliação foi publicada.', 'en' => 'Your review has been published.', 'es' => 'Tu valoración fue publicada.'],
                ['key' => 'review_success_updated', 'pt' => 'Sua avaliação foi atualizada.', 'en' => 'Your review has been updated.', 'es' => 'Tu valoración fue actualizada.'],
                ['key' => 'review_success_deleted', 'pt' => 'Sua avaliação foi excluída.', 'en' => 'Your review has been deleted.', 'es' => 'Tu valoración fue eliminada.'],
                ['key' => 'review_delete_confirm', 'pt' => 'Tem certeza que deseja excluir sua avaliação?', 'en' => 'Are you sure you want to delete your review?', 'es' => '¿Seguro que deseas eliminar tu valoración?'],
                ['key' => 'review_count_summary', 'pt' => ':count avaliações de clientes', 'en' => ':count customer reviews', 'es' => ':count valoraciones de clientes'],
                ['key' => 'review_customer_label', 'pt' => 'Cliente SAX', 'en' => 'SAX customer', 'es' => 'Cliente SAX'],
                ['key' => 'review_distribution_label', 'pt' => 'Distribuição das avaliações', 'en' => 'Rating distribution', 'es' => 'Distribución de valoraciones'],
                ['key' => 'review_hidden_notice', 'pt' => 'Sua avaliação está oculta. Ao salvar uma nova versão, ela será publicada novamente.', 'en' => 'Your review is hidden. Saving a new version will publish it again.', 'es' => 'Tu valoración está oculta. Al guardar una nueva versión, se publicará nuevamente.'],
                ['key' => 'review_title_placeholder', 'pt' => 'Resumo da sua experiência', 'en' => 'Summarize your experience', 'es' => 'Resume tu experiencia'],
                ['key' => 'review_comment_placeholder', 'pt' => 'Qualidade, tamanho, acabamento, uso...', 'en' => 'Quality, size, finish, use...', 'es' => 'Calidad, tamaño, acabado, uso...'],
            ];

            DB::table('languages')->insertOrIgnore(array_map(
                fn (array $translation) => $translation + ['created_at' => $now, 'updated_at' => $now],
                $translations
            ));
            Cache::forget('all_translations_db');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');

        Schema::table('products', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['rating_average', 'rating_count'],
                fn (string $column) => Schema::hasColumn('products', $column)
            ));
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
