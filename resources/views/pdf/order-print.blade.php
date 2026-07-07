<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        @page { margin: 40px; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-bottom: 40px;
        }
        .header table { width: 100%; border: none; }
        .header td { border: none; padding: 0; vertical-align: top; }
        .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand-sub {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
        }
        .invoice-title {
            font-size: 24px;
            color: #3b82f6;
            font-weight: 700;
            text-align: right;
            margin: 0;
        }
        .invoice-meta {
            text-align: right;
            color: #64748b;
            font-size: 11px;
            margin-top: 6px;
        }
        .addresses {
            width: 100%;
            margin-bottom: 30px;
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .addresses table { width: 100%; border: none; }
        .addresses td { border: none; padding: 0; vertical-align: top; width: 50%; }
        .address-title {
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: block;
        }
        .address-text {
            font-size: 13px;
            color: #1e293b;
            line-height: 1.6;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px;
            text-align: left;
        }
        table.items-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        .totals-container {
            width: 100%;
            margin-top: 20px;
        }
        .totals-table {
            float: right;
            width: 250px;
        }
        .totals-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .totals-table tr.grand-total td {
            border-bottom: none;
            color: #0f172a;
            font-size: 16px;
            font-weight: 700;
            background-color: #f1f5f9;
        }
        .footer {
            clear: both;
            margin-top: 80px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.approved { background: #dbeafe; color: #1e40af; }
        .badge.shipped { background: #e0e7ff; color: #3730a3; }
        .badge.completed { background: #dcfce7; color: #166534; }
        .badge.cancelled { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <h1 class="brand-name">Tech Accessories</h1>
                    <div class="brand-sub">Premium Gear &amp; Peripherals</div>
                </td>
                <td>
                    <h1 class="invoice-title">INVOICE</h1>
                    <div class="invoice-meta">
                        <strong>Order #:</strong> {{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}<br>
                        <strong>Date:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('F j, Y') }}<br>
                        <span class="badge {{ $order->status->value }}">{{ $order->status->value }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="addresses">
        <table>
            <tr>
                <td>
                    <span class="address-title">Billed To</span>
                    <div class="address-text">
                        <strong>{{ $order->user?->name ?? 'Guest Customer' }}</strong><br>
                        {{ $order->user?->email ?? '' }}
                    </div>
                </td>
                <td>
                    <span class="address-title">Shipped To</span>
                    <div class="address-text">
                        @if($order->shipping_address)
                            {{ $order->shipping_address['street'] ?? '' }}<br>
                            {{ $order->shipping_address['city'] ?? '' }} {{ isset($order->shipping_address['state']) ? ', '.$order->shipping_address['state'] : '' }}<br>
                            {{ $order->shipping_address['zip'] ?? '' }} {{ $order->shipping_address['country'] ?? '' }}
                        @else
                            N/A
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Unit Price</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->product?->name ?? 'Deleted Product' }}</strong>
                </td>
                <td class="text-right">EGP {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">EGP {{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-container">
        <table class="totals-table" cellspacing="0">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">EGP {{ number_format($order->total, 2) }}</td>
            </tr>
            <tr>
                <td>Shipping</td>
                <td class="text-right">EGP 0.00</td>
            </tr>
            <tr class="grand-total">
                <td>Total Due</td>
                <td class="text-right">EGP {{ number_format($order->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Thank you for your business. For any inquiries about this invoice, please contact support@techaccessories.com.
    </div>

</body>
</html>

