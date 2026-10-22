<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Additive, identity-based replication. Never reads or writes products.attributes. */
class CatalogAttributeSyncService
{
    public function ready(ConnectionInterface $peer): bool
    {
        foreach (['catalog_attributes', 'catalog_attribute_options', 'product_attribute_values', 'product_attribute_value_options'] as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table) || ! $peer->getSchemaBuilder()->hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    public function dictionary(ConnectionInterface $peer): void
    {
        if (! $this->ready($peer)) {
            throw new RuntimeException('Aplicá la migración de atributos en ambas tiendas antes de sincronizar.');
        }
        $peer->transaction(function () use ($peer) {
            foreach (DB::table('catalog_attributes')->orderBy('id')->get() as $source) {
                $target = $peer->table('catalog_attributes')->where('code', $source->code)->first();
                if ($target && ($target->data_type !== $source->data_type || $target->unit !== $source->unit)) {
                    throw new RuntimeException('Tipos o unidades incompatibles para el atributo '.$source->code.'.');
                }
                if (! $target || $this->newer($source, $target)) {
                    $data = (array) $source;
                    unset($data['id']);
                    $data['language_id'] = $this->language($peer, $source->language_id);
                    $peer->table('catalog_attributes')->updateOrInsert(['code' => $source->code], $data);
                    $target = $peer->table('catalog_attributes')->where('code', $source->code)->first();
                }
                foreach (DB::table('catalog_attribute_options')->where('attribute_id', $source->id)->get() as $option) {
                    $existing = $peer->table('catalog_attribute_options')->where('attribute_id', $target->id)->where('code', $option->code)->first();
                    if ($existing && ! $this->newer($option, $existing)) {
                        continue;
                    }
                    $data = (array) $option;
                    unset($data['id']);
                    $data['attribute_id'] = $target->id;
                    $data['language_id'] = $this->language($peer, $option->language_id);
                    $peer->table('catalog_attribute_options')->updateOrInsert(['attribute_id' => $target->id, 'code' => $option->code], $data);
                }
            }
        });
    }

    public function product(ConnectionInterface $peer, int $productId): void
    {
        $product = DB::table('products')->where('id', $productId)->first();
        if (! $product) {
            return;
        }
        $targetProductId = $peer->table('products')->where('sku', $product->sku)->value('id');
        if (! $targetProductId) {
            return;
        } // The ERP mirror may not have created this SKU yet.
        $peer->transaction(function () use ($peer, $productId, $targetProductId) {
            foreach (DB::table('product_attribute_values')->where('product_id', $productId)->get() as $source) {
                $code = DB::table('catalog_attributes')->where('id', $source->attribute_id)->value('code');
                $attributeId = $peer->table('catalog_attributes')->where('code', $code)->value('id');
                if (! $attributeId) {
                    throw new RuntimeException('Diccionario de destino incompleto.');
                }
                $existing = $peer->table('product_attribute_values')->where('product_id', $targetProductId)->where('attribute_id', $attributeId)->lockForUpdate()->first();
                if ($existing && (($existing->source === 'manual' && $source->source === 'ai')
                    || ($existing->source === $source->source && ! $this->newer($source, $existing)))) {
                    continue;
                }
                $optionIds = [];
                foreach (DB::table('product_attribute_value_options as v')->join('catalog_attribute_options as o', 'o.id', '=', 'v.option_id')->where('v.value_id', $source->id)->pluck('o.code') as $optionCode) {
                    $id = $peer->table('catalog_attribute_options')->where('attribute_id', $attributeId)->where('code', $optionCode)->value('id');
                    if (! $id) {
                        throw new RuntimeException('Opciones de destino incompletas.');
                    }
                    $optionIds[] = $id;
                }
                $data = (array) $source;
                unset($data['id']);
                $data['product_id'] = $targetProductId;
                $data['attribute_id'] = $attributeId;
                $data['updated_by'] = null;
                $peer->table('product_attribute_values')->updateOrInsert(['product_id' => $targetProductId, 'attribute_id' => $attributeId], $data);
                $valueId = $peer->table('product_attribute_values')->where('product_id', $targetProductId)->where('attribute_id', $attributeId)->value('id');
                $peer->table('product_attribute_value_options')->where('value_id', $valueId)->delete();
                foreach ($optionIds as $id) {
                    $peer->table('product_attribute_value_options')->insert(['value_id' => $valueId, 'option_id' => $id]);
                }
            }
        });
    }

    private function language(ConnectionInterface $peer, int $id): int
    {
        $language = (array) DB::table('languages')->where('id', $id)->first();
        if (! $language) {
            throw new RuntimeException('Traducción de atributo ausente.');
        }
        unset($language['id']);
        $peer->table('languages')->updateOrInsert(['key' => $language['key']], $language);

        return (int) $peer->table('languages')->where('key', $language['key'])->value('id');
    }

    private function newer(object $source, object $target): bool
    {
        return strcmp($source->updated_at.'|'.$source->revision, $target->updated_at.'|'.$target->revision) > 0;
    }
}
