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
        
        return view('product_variants.index', compact('variants'));
    }

    /**
     * Menampilkan form untuk membuat variant baru.
     */
    public function create()
    {
        return view('product_variants.create');
    }

    /**
     * Menyimpan product variant baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'      => 'required|string|max:255',
            'price'     => 'required|numeric',
            'is_active' => 'boolean',
        ]);

        $validatedData['is_active'] = $request->has('is_active');

        // Memanggil service untuk menyimpan data
        $this->variantService->createVariant($validatedData);

        return redirect()->route('product_variants.index')
                         ->with('success', 'Variant produk berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail product variant spesifik.
     */
    public function show(ProductVariant $productVariant)
    {
        return view('product_variants.show', compact('productVariant'));
    }

    /**
     * Menampilkan form untuk mengedit variant spesifik.
     */
    public function edit(ProductVariant $productVariant)
    {
        return view('product_variants.edit', compact('productVariant'));
    }

    /**
     * Memperbarui product variant spesifik.
     */
    public function update(Request $request, ProductVariant $productVariant)
    {
        $validatedData = $request->validate([
            'name'      => 'required|string|max:255',
            'price'     => 'required|numeric',
            'is_active' => 'boolean',
        ]);

        $validatedData['is_active'] = $request->has('is_active');

        // Memanggil service untuk memperbarui data
        $this->variantService->updateVariant($productVariant, $validatedData);

        return redirect()->route('product_variants.index')
                         ->with('success', 'Variant produk berhasil diperbarui.');
    }

    /**
     * Menghapus product variant.
     */
    public function destroy(ProductVariant $productVariant)
    {
        // Memanggil service untuk menghapus data
        $this->variantService->deleteVariant($productVariant);

        return redirect()->route('product_variants.index')
                         ->with('success', 'Variant produk berhasil dihapus.');
    }
}