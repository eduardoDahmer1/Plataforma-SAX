<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminImageEditorTest extends TestCase
{
    public function test_shared_editor_exposes_the_expected_editing_tools(): void
    {
        $root = dirname(__DIR__, 2);
        $modal = file_get_contents($root.'/resources/views/components/admin/image-editor.blade.php');
        $script = file_get_contents($root.'/public/js/admin-image-editor.js');

        $this->assertStringContainsString('data-image-action="auto"', $modal);
        $this->assertStringContainsString('data-image-action="zoom-in"', $modal);
        $this->assertStringContainsString('data-image-action="rotate-left"', $modal);
        $this->assertStringContainsString('data-image-quality', $modal);
        $this->assertStringContainsString('canvas.toBlob', $script);
        $this->assertStringContainsString("'image/webp'", $script);
        $this->assertStringContainsString('detectSubject', $script);
        $this->assertStringContainsString('removeBackground', $script);
        $this->assertStringContainsString('data-image-tool="lasso"', $modal);
        $this->assertStringContainsString('data-image-tool="polygon"', $modal);
        $this->assertStringContainsString('data-image-tool="magic"', $modal);
        $this->assertStringContainsString('data-image-tool="erase"', $modal);
        $this->assertStringContainsString('data-image-tool="restore"', $modal);
        $this->assertStringContainsString('data-image-action="polygon-remove-outside"', $modal);
        $this->assertStringContainsString('data-image-action="polygon-remove-inside"', $modal);
        $this->assertStringContainsString('data-image-action="recover-details"', $modal);
        $this->assertStringContainsString('data-image-action="refine-mask"', $modal);
        $this->assertStringContainsString('data-image-action="add-text"', $modal);
        $this->assertStringContainsString('localContrast', $script);
        $this->assertStringContainsString('refineMask', $script);
        $this->assertStringContainsString('magicRemove', $script);
        $this->assertStringContainsString('backgroundRemovalPass', $script);
        $this->assertStringContainsString('recoverObjectDetails', $script);
        $this->assertStringContainsString('GalleryManager.prototype.autoAll', $script);
    }

    public function test_product_images_use_square_editor_and_support_saved_gallery_edits(): void
    {
        $root = dirname(__DIR__, 2);
        $view = file_get_contents($root.'/resources/views/admin/products/edit.blade.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/ProductControllerAdmin.php');

        $this->assertStringContainsString('data-sax-image-editor="single"', $view);
        $this->assertStringContainsString('data-editor-width="1080"', $view);
        $this->assertStringContainsString('data-editor-height="1080"', $view);
        $this->assertStringContainsString('data-editor-smart-subject="1"', $view);
        $this->assertStringContainsString('data-image-edit-existing', $view);
        $this->assertStringContainsString('gallery_remove.*', $controller);
        $this->assertStringContainsString("input('gallery_remove', [])", $controller);
    }

    public function test_blog_cover_and_gallery_have_their_own_output_dimensions(): void
    {
        $root = dirname(__DIR__, 2);
        $view = file_get_contents($root.'/resources/views/admin/blogs/_form.blade.php');

        $this->assertStringContainsString('inputId="blogCoverInput"', $view);
        $this->assertStringContainsString('editorWidth="1600"', $view);
        $this->assertStringContainsString('editorHeight="1000"', $view);
        $this->assertStringContainsString('data-editor-width="1400"', $view);
        $this->assertStringContainsString('data-editor-height="1050"', $view);
        $this->assertStringContainsString('data-gallery-max="10"', $view);
        $this->assertStringContainsString('data-gallery-auto-all', $view);
    }

    public function test_system_image_studio_has_route_menu_presets_and_download(): void
    {
        $root = dirname(__DIR__, 2);
        $routes = file_get_contents($root.'/routes/web.php');
        $menu = file_get_contents($root.'/resources/views/admin/menu-lateral.blade.php');
        $view = file_get_contents($root.'/resources/views/admin/image-editor/index.blade.php');
        $script = file_get_contents($root.'/public/js/admin-image-studio.js');

        $this->assertStringContainsString("name('image-editor.index')", $routes);
        $this->assertStringContainsString("route('admin.image-editor.index')", $menu);
        $this->assertStringContainsString('data-studio-preset="1920,640,cover"', $view);
        $this->assertStringContainsString('data-studio-download', $view);
        $this->assertStringContainsString('window.SaxImageEditor.open', $script);
        $this->assertStringContainsString('link.download', $script);
    }
}
