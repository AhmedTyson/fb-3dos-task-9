<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
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
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #111827;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 5px 0;
            letter-spacing: -0.5px;
        }
        .header p {
            color: #6b7280;
            font-size: 13px;
            margin: 0;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
            width: 100%;
        }
        .summary-box table {
            width: 100%;
            border: none;
        }
        .summary-box td {
            width: 33.33%;
            vertical-align: top;
            padding: 0;
            border: none;
        }
        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 5px;
            display: block;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }
        .table-container {
            width: 100%;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }
        table.data-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.approved { background: #dbeafe; color: #1e40af; }
        .badge.shipped { background: #e0e7ff; color: #3730a3; }
        .badge.completed { background: #dcfce7; color: #166534; }
        .badge.cancelled { background: #fee2e2; color: #b91c1c; }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            color: #9ca3af;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Sales &amp; Revenue Report</h1>
        <p>Tech Accessories INC &nbsp;&bull;&nbsp; Generated on {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="summary-box">
        <table>
            <tr>
                <td>
                    <span class="stat-label">Total Revenue</span>
                    <span class="stat-value">EGP {{ number_format($report['total_revenue'], 2) }}</span>
                </td>
                <td>
                    <span class="stat-label">Total Orders</span>
                    <span class="stat-value">{{ number_format($report['total_orders']) }}</span>
                </td>
                <td>
                    <span class="stat-label">Period</span>
                    <span class="stat-value" style="font-size: 16px; margin-top: 6px; display: block;">
                        {{ $report['period']['from'] ? \Carbon\Carbon::parse($report['period']['from'])->format('M d') : 'All Time' }}
                        &mdash;
                        {{ $report['period']['to'] ? \Carbon\Carbon::parse($report['period']['to'])->format('M d, Y') : 'Present' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    @if($report['top_product'])
    <div style="margin-bottom: 25px; padding: 15px; background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 4px;">
        <span style="font-size: 11px; text-transform: uppercase; color: #1d4ed8; font-weight: 600;">Top Performing Product</span>
        <div style="font-size: 16px; color: #1e3a8a; font-weight: 700; margin-top: 4px;">
            {{ $report['top_product']['name'] }} ({{ number_format($report['top_product']['units_sold']) }} units)
        </div>
    </div>
    @endif

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['orders'] as $order)
                <tr>
                    <td>#{{ str_pad($order['id'], 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') }}</td>
                    <td>{{ $order['customer_name'] }}</td>
                    <td class="text-center">
                        <span class="badge {{ $order['status'] }}">{{ $order['status'] }}</span>
                    </td>
                    <td class="text-right font-bold">EGP {{ number_format($order['total'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 30px; color: #94a3b8;">No orders found for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        &copy; {{ now()->year }} Tech Accessories. Internal Confidential Report.
    </div>

</body>
</html>

