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

    /**
     * Menyimpan product variant baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'      => 'required|string|max:255',
            'price'     => 'required|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        // Mengatur is_active menjadi true sebagai default jika tidak dikirim (sesuai input hidden di form)
        $validatedData['is_active'] = $request->input('is_active', 1);

        // Memanggil service untuk menyimpan data
        $this->variantService->createVariant($validatedData);

        return redirect()->route('owner.product_variants.index')
                         ->with('success', 'Variant produk berhasil ditambahkan.');
    }

    /**
     * Memperbarui product variant spesifik.
     */
    public function update(Request $request, ProductVariant $productVariant)
    {
        $validatedData = $request->validate([
            'name'      => 'required|string|max:255',
            'price'     => 'required|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $validatedData['is_active'] = $request->input('is_active', 1);

        // Memanggil service untuk memperbarui data
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