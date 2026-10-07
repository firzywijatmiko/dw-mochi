<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\WhatsAppParserService;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $waParser;
    protected $orderService;

    public function __construct(WhatsAppParserService $waParser, OrderService $orderService)
    {
        $this->waParser = $waParser;
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $orders = Order::with('details.productVariant')
            ->when(in_array($status, ['pending', 'process', 'completed', 'cancelled']), function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->get();
        $variants = ProductVariant::where('is_active', true)->orderBy('name')->get();

        return view('owner.orders.index', compact('orders', 'variants', 'status'));
    }

    // --- ACTIVITY DIAGRAM: TAMBAH PESANAN ---
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string',
            'customer_phone' => 'required|string',
            'pickup_at'      => 'required|date',
            'variants'       => 'required|array|min:1',
            'variants.*.id' => 'required|exists:product_variants,id',
            'variants.*.quantity' => 'required|integer|min:1',
            'variants.*.price' => 'required|numeric|min:0',
        ]);

        // Cek Duplikasi
        if ($this->orderService->checkDuplicate($request->customer_phone, $request->pickup_at)) {
            return back()->with('error', 'Data Pesanan Sudah Ada pada tanggal tersebut.')->withInput();
        }

        $this->orderService->storeOrder($request->all(), $request->variants, Auth::id());

        return redirect()->route('owner.orders.index')->with('success', 'Pesanan Berhasil Ditambahkan');
    }

    // --- ACTIVITY DIAGRAM: UBAH PESANAN ---
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_name'  => 'required|string',
            'customer_phone' => 'required|string',
            'pickup_at'      => 'required|date',
            'variants'       => 'required|array|min:1',
            'variants.*.id' => 'required|exists:product_variants,id',
            'variants.*.quantity' => 'required|integer|min:1',
            'variants.*.price' => 'required|numeric|min:0',
        ]);

        if ($this->orderService->checkDuplicate($request->customer_phone, $request->pickup_at, $order->id)) {
            return back()->with('error', 'Data Pesanan Sudah Ada.')->withInput();
        }

        $order->update($request->only(['customer_name', 'customer_phone', 'pickup_at', 'status', 'notes']));
        $order->details()->delete();
        foreach ($request->input('variants') as $variant) {
            $order->details()->create([
                'product_variant_id' => $variant['id'],
                'quantity' => $variant['quantity'],
                'unit_price' => $variant['price'],
            ]);
        }

        return redirect()->route('owner.orders.index')->with('success', 'Pesanan Berhasil Diubah');
    }

    // --- ACTIVITY DIAGRAM: HAPUS PESANAN ---
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('owner.orders.index')->with('success', 'Data Pesanan Berhasil Dihapus');
    }

    // --- ACTIVITY DIAGRAM: PARSING DATA WHATSAPP ---
    public function parseWhatsApp(Request $request)
    {
        $request->validate(['raw_template' => 'required|string']);

        $parsedData = $this->waParser->parse($request->raw_template);

        if (!$parsedData['is_valid']) {
            return back()->with('error', 'Format Pesan Tidak Sesuai');
        }

        // Lempar data hasil parsing ke halaman form agar Owner bisa mereview (Preview Data)
        $variants = ProductVariant::where('is_active', true)->orderBy('name')->get();
        return view('owner.orders.create_preview', compact('parsedData', 'variants'));
    }

    // --- ACTIVITY DIAGRAM: GENERATE LABEL PESANAN ---
    public function printLabel(Order $order)
    {
        $order->load('details.productVariant');
        
        // Generate PDF menggunakan DomPDF
        $pdf = Pdf::loadView('owner.orders.label_pdf', compact('order'))
                  ->setPaper('a6', 'portrait');

        return $pdf->stream('Label_Pesanan_'.$order->customer_name.'.pdf');
    }
}