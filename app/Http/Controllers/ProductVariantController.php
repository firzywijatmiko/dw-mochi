<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    protected $variantService;

    public function __construct(ProductVariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    /**
     * Menampilkan daftar semua product variant dengan pagination.
     */
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page'), [5, 10, 25, 50])
            ? (int) $request->input('per_page')
            : 10;

        $variants = ProductVariant::orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('owner.product_variants.index', compact('variants', 'perPage'));
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
        if (\App\Models\OrderDetail::where('product_variant_id', $productVariant->id)->exists()) {
            return redirect()->route('owner.product_variants.index')
                ->withErrors(['variant' => 'Varian "' . $productVariant->name . '" tidak bisa dihapus karena sudah dipakai pada pesanan.']);
        }

        $this->variantService->deleteVariant($productVariant);

        return redirect()->route('owner.product_variants.index')
            ->with('success', 'Varian produk berhasil dihapus.');
    }
}