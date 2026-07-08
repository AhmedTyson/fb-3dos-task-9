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
    private const COL_WIDTH_ID       = 10;
    private const COL_WIDTH_CUSTOMER = 30;
    private const COL_WIDTH_STATUS   = 15;
    private const COL_WIDTH_TOTAL    = 18;
    private const COL_WIDTH_DATE     = 25;

    public function salesReport(array $report): string
    {
        $path   = tempnam(sys_get_temp_dir(), 'sales_report_') . '.xlsx';
        $writer = new Writer($this->buildOptions());
        $styles = $this->buildStyles();

        $writer->openToFile($path);
        $this->writeSummarySection($writer, $report, $styles);
        $this->writeOrdersTable($writer, $report['orders'], $styles);
        $writer->close();

        return $path;
    }

    private function buildOptions(): Options
    {
        $options = new Options();
        $options->setColumnWidth(self::COL_WIDTH_ID,       1);
        $options->setColumnWidth(self::COL_WIDTH_CUSTOMER, 2);
        $options->setColumnWidth(self::COL_WIDTH_STATUS,   3);
        $options->setColumnWidth(self::COL_WIDTH_TOTAL,    4);
        $options->setColumnWidth(self::COL_WIDTH_DATE,     5);

        return $options;
    }

    private function buildStyles(): array
    {
        $base = (new Style())->withFontSize(11)->withCellAlignment(CellAlignment::LEFT);

        return [
            'title'  => (new Style())->withFontBold(true)->withFontSize(16)->withFontColor(Color::DARK_BLUE),
            'header' => (new Style())->withFontBold(true)->withFontColor(Color::WHITE)
                            ->withBackgroundColor(Color::DARK_BLUE)->withCellAlignment(CellAlignment::CENTER),
            'row'    => $base,
            'money'  => (clone $base)->withFormat('#,##0.00')->withCellAlignment(CellAlignment::RIGHT),
            'center' => (clone $base)->withCellAlignment(CellAlignment::CENTER),
        ];
    }

    private function writeSummarySection(Writer $writer, array $report, array $styles): void
    {
        $writer->addRow(Row::fromValuesWithStyle(['Tech Accessories - Sales Report'], $styles['title']));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['Period From:', $report['period']['from'] ?? 'All time']));
        $writer->addRow(Row::fromValues(['Period To:',   $report['period']['to']   ?? 'All time']));
        $writer->addRow(Row::fromValues(['Total Orders:', $report['total_orders']]));
        $writer->addRow(Row::fromValuesWithStyle(['Total Revenue:', $report['total_revenue']], $styles['money']));

        if ($report['top_product']) {
            $tp = $report['top_product'];
            $writer->addRow(Row::fromValues(['Top Product:', "{$tp['name']} ({$tp['units_sold']} units)"]));
        }

        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues([]));
    }

    private function writeOrdersTable(Writer $writer, iterable $orders, array $styles): void
    {
        $writer->addRow(Row::fromValuesWithStyle(
            ['Order ID', 'Customer Name', 'Status', 'Total (EGP)', 'Date'],
            $styles['header']
        ));

        foreach ($orders as $order) {
            $writer->addRow(new Row([
                Cell::fromValue($order['id'])->withStyle($styles['center']),
                Cell::fromValue($order['customer_name']),
                Cell::fromValue($order['status'])->withStyle($styles['center']),
                Cell::fromValue($order['total'])->withStyle($styles['money']),
                Cell::fromValue($order['created_at'])->withStyle($styles['center']),
            ]));
        }
    }
}
