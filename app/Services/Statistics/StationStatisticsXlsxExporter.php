<?php

namespace App\Services\Statistics;

use Carbon\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;
use Illuminate\Support\Str;

class StationStatisticsXlsxExporter
{
    private const COLUMN_COUNT = 12;

    /**
     * @param  array<string, mixed>  $report
     * @return array{path: string, filename: string}
     */
    public function export(array $report, string $stationCode, Carbon $start, Carbon $end): array
    {
        $directory = storage_path('app/tmp');

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Не удалось создать временную директорию для экспорта.');
        }

        $sanitizedCode = Str::slug($stationCode, '-');
        if ($sanitizedCode === '') {
            $sanitizedCode = 'station';
        }

        $filename = sprintf(
            'station-%s-statistics-%s_%s.xlsx',
            $sanitizedCode,
            $start->toDateString(),
            $end->toDateString(),
        );

        $path = $directory . '/' . (string) Str::uuid() . '-' . $filename;

        $options = new Options();
        $this->configureColumns($options);
        $this->configureMerges($options);

        $writer = new Writer($options);
        $writer->openToFile($path);

        $styles = $this->buildStyles();
        $this->writeRows($writer, $report, $styles);

        $writer->close();

        return [
            'path' => $path,
            'filename' => $filename,
        ];
    }

    private function configureColumns(Options $options): void
    {
        $options->setColumnWidth(5, 1);
        $options->setColumnWidth(28, 2);
        $options->setColumnWidth(16, 3, 4);
        $options->setColumnWidth(12, 5);
        $options->setColumnWidth(12, 6, 7, 8, 9, 10, 11);
        $options->setColumnWidth(4, 12);
    }

    private function configureMerges(Options $options): void
    {
        // Период
        $options->mergeCells(1, 1, 3, 1);

        // Секционные заголовки
        $options->mergeCells(0, 4, 11, 4);
        $options->mergeCells(0, 9, 11, 9);

        // Блок "Моющие средства"
        $options->mergeCells(0, 5, 3, 5);
        $options->mergeCells(0, 7, 3, 7);

        // Блок "Программы"
        $options->mergeCells(4, 10, 6, 10);
        $options->mergeCells(0, 15, 4, 15);
        $options->mergeCells(5, 16, 10, 16);
    }

    /**
     * @return array<string, Style>
     */
    private function buildStyles(): array
    {
        $border = new Border(
            new BorderPart(Border::LEFT, Color::BLACK, Border::WIDTH_THIN),
            new BorderPart(Border::RIGHT, Color::BLACK, Border::WIDTH_THIN),
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN),
        );

        return [
            'plain' => $this->makePlainStyle(),
            'base' => $this->makeStyle($border),
            'base_left' => $this->makeStyle($border, CellAlignment::LEFT),
            'bold' => $this->makeStyle($border, CellAlignment::CENTER, true),
            'bold_left' => $this->makeStyle($border, CellAlignment::LEFT, true),
            'header' => $this->makeStyle($border, CellAlignment::CENTER, true, 'EAF1FB'),
            'header_left' => $this->makeStyle($border, CellAlignment::LEFT, true, 'EAF1FB'),
            'section' => $this->makeStyle($border, CellAlignment::CENTER, true, 'D9E2F3'),
        ];
    }

    private function makeStyle(
        Border $border,
        string $alignment = CellAlignment::CENTER,
        bool $bold = false,
        ?string $backgroundColor = null,
    ): Style {
        $style = new Style();
        $style->setFontName('Arial');
        $style->setFontSize(10);
        $style->setCellAlignment($alignment);
        $style->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
        $style->setBorder($border);

        if ($bold) {
            $style->setFontBold();
        }

        if ($backgroundColor !== null) {
            $style->setBackgroundColor($backgroundColor);
        }

        return $style;
    }

    private function makePlainStyle(): Style
    {
        $style = new Style();
        $style->setFontName('Arial');
        $style->setFontSize(10);

        return $style;
    }

    /**
     * @param  array<string, mixed>  $report
     * @param  array<string, Style>  $styles
     */
    private function writeRows(Writer $writer, array $report, array $styles): void
    {
        $startDay = (int) data_get($report, 'period.start_parts.day', 0);
        $startMonth = (int) data_get($report, 'period.start_parts.month', 0);
        $startYear = (int) data_get($report, 'period.start_parts.year', 0);
        $endDay = (int) data_get($report, 'period.end_parts.day', 0);
        $endMonth = (int) data_get($report, 'period.end_parts.month', 0);
        $endYear = (int) data_get($report, 'period.end_parts.year', 0);

        $row = $this->emptyRow();
        $row[1] = 'выбор периода';
        $row[4] = 'число';
        $row[5] = 'месяц';
        $row[6] = 'год';
        $row[8] = 'число';
        $row[9] = 'месяц';
        $row[10] = 'год';
        $this->addRow($writer, $row, $this->allStyles($styles['header']));

        $row = $this->emptyRow();
        $row[4] = $startDay;
        $row[5] = $startMonth;
        $row[6] = $startYear;
        $row[8] = $endDay;
        $row[9] = $endMonth;
        $row[10] = $endYear;
        $this->addRow($writer, $row, $this->allStyles($styles['base']));

        $this->addRow($writer, $this->emptyRow(), $this->allStyles($styles['plain']));

        $row = $this->emptyRow();
        $row[0] = 'Моющие средства';
        $this->addRow($writer, $row, $this->allStyles($styles['section']));

        $row = $this->emptyRow();
        $row[0] = 'Всего литров';
        for ($i = 1; $i <= 8; $i++) {
            $row[3 + $i] = $this->formatDecimal((float) data_get($report, "detergents.{$i}.liters", 0));
        }
        $rowStyles = $this->allStyles($styles['base']);
        $rowStyles[0] = $styles['bold_left'];
        $this->addRow($writer, $row, $rowStyles);

        $row = $this->emptyRow();
        $row[3] = '№';
        for ($i = 1; $i <= 8; $i++) {
            $row[3 + $i] = $i;
        }
        $this->addRow($writer, $row, $this->allStyles($styles['base']));

        $row = $this->emptyRow();
        $row[2] = 'наименование';
        for ($i = 1; $i <= 8; $i++) {
            $row[3 + $i] = (string) data_get($report, "detergents.{$i}.name", "Средство {$i}");
        }
        $rowStyles = $this->allStyles($styles['base']);
        $rowStyles[2] = $styles['base_left'];
        $this->addRow($writer, $row, $rowStyles);

        $this->addRow($writer, $this->emptyRow(), $this->allStyles($styles['plain']));

        $row = $this->emptyRow();
        $row[0] = 'Программы';
        $this->addRow($writer, $row, $this->allStyles($styles['section']));

        $row = $this->emptyRow();
        $row[4] = 'Итого кг';
        $row[7] = $this->formatDecimal((float) data_get($report, 'totals.kg_total', 0));
        $rowStyles = $this->allStyles($styles['base']);
        $rowStyles[4] = $styles['bold'];
        $rowStyles[7] = $styles['bold'];
        $this->addRow($writer, $row, $rowStyles);

        $row = $this->emptyRow();
        $row[4] = '№ машины';
        for ($machine = 1; $machine <= 6; $machine++) {
            $row[4 + $machine] = $machine;
        }
        $rowStyles = $this->allStyles($styles['base']);
        $rowStyles[4] = $styles['bold'];
        $this->addRow($writer, $row, $rowStyles);

        $row = $this->emptyRow();
        for ($machine = 1; $machine <= 6; $machine++) {
            $row[4 + $machine] = (string) data_get($report, "machines.{$machine}.name", "Машина {$machine}");
        }
        $this->addRow($writer, $row, $this->allStyles($styles['base']));

        $row = $this->emptyRow();
        $row[4] = 'загрузка';
        for ($machine = 1; $machine <= 6; $machine++) {
            $row[4 + $machine] = $this->formatDecimal((float) data_get($report, "machines.{$machine}.loading", 0));
        }
        $rowStyles = $this->allStyles($styles['base']);
        $rowStyles[4] = $styles['bold'];
        $this->addRow($writer, $row, $rowStyles);

        $row = $this->emptyRow();
        $row[4] = 'всего кг';
        for ($machine = 1; $machine <= 6; $machine++) {
            $row[4 + $machine] = $this->formatDecimal((float) data_get($report, "machines.{$machine}.total_kg", 0));
        }
        $rowStyles = $this->allStyles($styles['base']);
        $rowStyles[4] = $styles['bold'];
        $this->addRow($writer, $row, $rowStyles);

        $row = $this->emptyRow();
        $row[0] = 'Количество оконченных программ';
        for ($machine = 1; $machine <= 6; $machine++) {
            $row[4 + $machine] = (int) data_get($report, "machines.{$machine}.completed_total", 0);
        }
        $rowStyles = $this->allStyles($styles['base']);
        $rowStyles[0] = $styles['bold_left'];
        $this->addRow($writer, $row, $rowStyles);

        $row = $this->emptyRow();
        $row[0] = '№';
        $row[1] = 'Программа';
        $row[2] = 'Прервано кол-во';
        $row[3] = 'Всего кол-во';
        $row[4] = 'Всего кг';
        $row[5] = 'Количество оконченных программ';
        $rowStyles = $this->allStyles($styles['header']);
        $rowStyles[1] = $styles['header_left'];
        $this->addRow($writer, $row, $rowStyles);

        for ($program = 1; $program <= 19; $program++) {
            $row = $this->emptyRow();
            $row[0] = $program;
            $row[1] = (string) data_get($report, "programs.{$program}.name", "Программа {$program}");
            $row[2] = (int) data_get($report, "programs.{$program}.interrupted_total", 0);
            $row[3] = (int) data_get($report, "programs.{$program}.completed_total", 0);
            $row[4] = $this->formatDecimal((float) data_get($report, "programs.{$program}.total_kg", 0));

            for ($machine = 1; $machine <= 6; $machine++) {
                $row[4 + $machine] = (int) data_get(
                    $report,
                    "programs.{$program}.completed_by_machine.{$machine}",
                    0,
                );
            }

            $rowStyles = $this->allStyles($styles['base']);
            $rowStyles[1] = $styles['base_left'];
            $this->addRow($writer, $row, $rowStyles);
        }
    }

    /**
     * @return array<int, string>
     */
    private function emptyRow(): array
    {
        return array_fill(0, self::COLUMN_COUNT, '');
    }

    /**
     * @return array<int, Style>
     */
    private function allStyles(Style $style): array
    {
        return array_fill(0, self::COLUMN_COUNT, $style);
    }

    /**
     * @param  array<int, string|int|float>  $values
     * @param  array<int, Style>  $columnStyles
     */
    private function addRow(Writer $writer, array $values, array $columnStyles): void
    {
        $writer->addRow(Row::fromValuesWithStyles($values, null, $columnStyles));
    }

    private function formatDecimal(float $value): string
    {
        return number_format($value, 1, ',', '');
    }
}
