<?php

namespace App\Services;

use App\Models\ProductVariant;

class ProductVariantService
{
    /**
     * Mengambil semua data product variant.
     */
    public function getAllVariants()
    {
        return ProductVariant::all();
    }

    /**
     * Menyimpan product variant baru ke database.
     */
    public function createVariant(array $data)
    {
        return ProductVariant::create($data);
    }

    /**
     * Memperbarui data product variant yang sudah ada.
     */
    public function updateVariant(ProductVariant $productVariant, array $data)
    {
        $productVariant->update($data);
        return $productVariant;
    }

    /**
     * Menghapus product variant dari database.
     */
    public function deleteVariant(ProductVariant $productVariant)
    {
        return $productVariant->delete();
    }
}