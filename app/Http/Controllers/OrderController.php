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

    public function store(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string',
            'customer_phone' => ['required', 'string', 'regex:/^08[0-9]{8,11}$/'],
            'pickup_at'      => 'required|date',
            'variants'       => 'required|array|min:1',
            'variants.*.id' => 'required|exists:product_variants,id',
            'variants.*.quantity' => 'required|integer|min:1',
            'variants.*.price' => 'required|numeric|min:0',
        ], [
            'customer_phone.regex' => 'Nomor HP harus diawali dengan 08 dan terdiri dari 10-13 angka.'
        ]);

        if ($this->orderService->checkDuplicate($request->customer_phone, $request->pickup_at)) {
            return back()->with('error', 'Sudah ada pesanan dengan nomor HP tersebut pada tanggal ambil yang sama.')->withInput();
        }

        $this->orderService->storeOrder($request->all(), $request->variants, Auth::id());

        return redirect()->route('owner.orders.index')->with('success', 'Pesanan Berhasil Ditambahkan');
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_name'  => 'required|string',
            'customer_phone' => ['required', 'string', 'regex:/^08[0-9]{8,11}$/'],
            'pickup_at'      => 'required|date',
            'variants'       => 'required|array|min:1',
            'variants.*.id' => 'required|exists:product_variants,id',
            'variants.*.quantity' => 'required|integer|min:1',
            'variants.*.price' => 'required|numeric|min:0',
        ], [
            'customer_phone.regex' => 'Nomor HP harus diawali dengan 08 dan terdiri dari 10-13 angka.'
        ]);

        if ($this->orderService->checkDuplicate($request->customer_phone, $request->pickup_at, $order->id)) {
            return back()->with('error', 'Sudah ada pesanan dengan nomor HP tersebut pada tanggal ambil yang sama.')->withInput();
        }

        // RESET STATUS CETAK: Setiap kali pesanan diubah, kembalikan is_label_printed ke false
        $updateData = $request->only(['customer_name', 'customer_phone', 'pickup_at', 'status', 'notes']);
        $updateData['is_label_printed'] = false;

        $order->update($updateData);
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

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('owner.orders.index')->with('success', 'Data Pesanan Berhasil Dihapus');
    }

    public function parseWhatsApp(Request $request)
    {
        $request->validate(['raw_template' => 'required|string']);

        $templateText = $request->input('raw_template');
        $parsedData = $this->waParser->parse($templateText);

        if ($request->wantsJson() || $request->ajax()) {
            if (!$parsedData['is_valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format Pesan Tidak Sesuai'
                ], 422); 
            }
            
            return response()->json(['success' => true]);
        }

        if (!$parsedData['is_valid']) {
            return back()->with('error', 'Format Pesan Tidak Sesuai');
        }

        $variants = ProductVariant::where('is_active', true)->orderBy('name')->get();
        $raw_template = $templateText; 

        return view('owner.orders.create_preview', compact('parsedData', 'variants', 'raw_template'));
    }

    public function printLabel(Order $order)
    {
        $order->load('details.productVariant');
        
        // TANDAI SUDAH DICETAK
        $order->update(['is_label_printed' => true]);

        $pdf = Pdf::loadView('owner.orders.label_pdf', compact('order'))
                 ->setPaper('a6', 'portrait');

        return $pdf->stream('Label_Pesanan_'.$order->customer_name.'.pdf');
    }

    public function printSelectedLabels(Request $request)
    {
        $orderIds = $request->input('order_ids', []);

        if (empty($orderIds)) {
            return back()->with('error', 'Pilih minimal satu pesanan yang ingin dicetak labelnya.');
        }

        // TANDAI SEMUA PESANAN TERPILIH SEBAGAI SUDAH DICETAK
        Order::whereIn('id', $orderIds)->update(['is_label_printed' => true]);

        $orders = Order::with('details.productVariant')
            ->whereIn('id', $orderIds)
            ->get();

        $pdf = Pdf::loadView('owner.orders.labels_bulk_pdf', compact('orders'))
                 ->setPaper('a4', 'portrait');

        return $pdf->stream('Label_Pesanan_Terpilih.pdf');
    }
}