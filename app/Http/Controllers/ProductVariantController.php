<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    protected $variantService;

    // Menyuntikkan ProductVariantService melalui constructor
    public function __construct(ProductVariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    /**
     * Menampilkan daftar semua product variant.
     */
    public function index()
    {
        $variants = $this->variantService->getAllVariants();
        return view('owner.product_variants.index', compact('variants')); // Baris ini mencari file di folder product_variants
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'      => 'required|string|max:255|unique:product_variants,name',
            'price'     => 'required|numeric|min:1000',
            'is_active' => 'required|boolean',
        ]);

        $this->variantService->createVariant($validatedData);

        return redirect()->route('owner.product_variants.index')
                         ->with('success', 'Variant produk berhasil ditambahkan.');
    }

    public function update(Request $request, ProductVariant $productVariant)
    {
        $validatedData = $request->validate([
            // Abaikan nama yang sama jika ID-nya milik produk ini sendiri
            'name'      => 'required|string|max:255|unique:product_variants,name,' . $productVariant->id,
            'price'     => 'required|numeric|min:1000',
            'is_active' => 'required|boolean',
        ]);

        $this->variantService->updateVariant($productVariant, $validatedData);

        return redirect()->route('owner.product_variants.index')
                         ->with('success', 'Variant produk berhasil diperbarui.');
    }

    /**
     * Menghapus product variant.
     */
    public function destroy(ProductVariant $productVariant)
    {
        // Memanggil service untuk menghapus data
        $this->variantService->deleteVariant($productVariant);

        return redirect()->route('owner.product_variants.index')
                         ->with('success', 'Variant produk berhasil dihapus.');
    }
}