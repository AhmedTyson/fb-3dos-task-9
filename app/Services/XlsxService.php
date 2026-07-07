<?php

namespace App\Services;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;

class XlsxService
{
    /**
     * Build a sales-report XLSX and return the file path (temp).
     */
    public function salesReport(array $report): string
    {
        $path = tempnam(sys_get_temp_dir(), 'sales_report_') . '.xlsx';

        $options = new Options();
        // Set column widths for a professional look
        $options->setColumnWidth(10, 1); // ID
        $options->setColumnWidth(30, 2); // Customer
        $options->setColumnWidth(15, 3); // Status
        $options->setColumnWidth(18, 4); // Total
        $options->setColumnWidth(25, 5); // Date

        $writer = new Writer($options);
        $writer->openToFile($path);

        // Styles
        $titleStyle = (new Style())
            ->withFontBold(true)
            ->withFontSize(16)
            ->withFontColor(Color::DARK_BLUE);

        $subtitleStyle = (new Style())
            ->withFontBold(true)
            ->withFontColor(Color::BLACK);

        $headerStyle = (new Style())
            ->withFontBold(true)
            ->withFontColor(Color::WHITE)
            ->withBackgroundColor(Color::DARK_BLUE)
            ->withCellAlignment(CellAlignment::CENTER);

        $rowStyle = (new Style())
            ->withFontSize(11)
            ->withCellAlignment(CellAlignment::LEFT);

        $moneyStyle = clone $rowStyle;
        $moneyStyle = $moneyStyle->withFormat('#,##0.00')->withCellAlignment(CellAlignment::RIGHT);

        $dateStyle = clone $rowStyle;
        $dateStyle = $dateStyle->withCellAlignment(CellAlignment::CENTER);

        $centerStyle = clone $rowStyle;
        $centerStyle = $centerStyle->withCellAlignment(CellAlignment::CENTER);

        // Summary Section
        $titleRow = Row::fromValuesWithStyle(['Tech Accessories - Sales Report'], $titleStyle);
        $writer->addRow($titleRow);

        $writer->addRow(Row::fromValues([]));
        
        $writer->addRow(Row::fromValues(['Period From:', $report['period']['from'] ?? 'All time']));
        $writer->addRow(Row::fromValues(['Period To:',   $report['period']['to']   ?? 'All time']));
        $writer->addRow(Row::fromValues(['Total Orders:',  $report['total_orders']]));

        $revenueRow = Row::fromValuesWithStyle(['Total Revenue:', $report['total_revenue']], $moneyStyle);
        $writer->addRow($revenueRow);

        if ($report['top_product']) {
            $tp = $report['top_product'];
            $writer->addRow(Row::fromValues(['Top Product:', "{$tp['name']} ({$tp['units_sold']} units)"]));
        }

        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues([]));

        // Orders Table Header
        $headerRow = Row::fromValuesWithStyle(['Order ID', 'Customer Name', 'Status', 'Total (EGP)', 'Date'], $headerStyle);
        $writer->addRow($headerRow);

        foreach ($report['orders'] as $order) {
            $idCell       = Cell::fromValue($order['id'])->withStyle($centerStyle);
            $customerCell = Cell::fromValue($order['customer_name']);
            $statusCell   = Cell::fromValue($order['status'])->withStyle($centerStyle);
            $totalCell    = Cell::fromValue($order['total'])->withStyle($moneyStyle);
            $dateCell     = Cell::fromValue($order['created_at'])->withStyle($dateStyle);

            $writer->addRow(new Row([$idCell, $customerCell, $statusCell, $totalCell, $dateCell]));
        }

        $writer->close();

        return $path;
    }
}

