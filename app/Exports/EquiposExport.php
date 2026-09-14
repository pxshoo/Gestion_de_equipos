<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquiposExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping
{
    private const DATE_COLUMNS = ['created_at', 'updated_at'];
    private const NUMERIC_COLUMNS = ['valoracion_equipo_actual_numero'];

    /**
     * @param  array<int, string>  $columns  Keys de las columnas seleccionadas, en el orden a exportar.
     * @param  array<string, string>  $labels  Mapa columna => etiqueta legible (catálogo completo).
     */
    public function __construct(
        private Builder $query,
        private array $columns,
        private array $labels,
        private string $sortBy,
        private string $sortDir,
    ) {
    }

    public function query()
    {
        return $this->query->orderBy($this->sortBy, $this->sortDir);
    }

    public function headings(): array
    {
        return array_map(fn ($column) => $this->labels[$column] ?? $column, $this->columns);
    }

    public function map($equipo): array
    {
        return array_map(function ($column) use ($equipo) {
            $value = $equipo->{$column};

            if (in_array($column, self::DATE_COLUMNS, true) && $value) {
                return Carbon::parse($value)->format('d-m-Y H:i');
            }

            return $value ?? '';
        }, $this->columns);
    }

    public function columnFormats(): array
    {
        $formats = [];

        foreach ($this->columns as $index => $column) {
            if (in_array($column, self::NUMERIC_COLUMNS, true)) {
                $letter = Coordinate::stringFromColumnIndex($index + 1);
                $formats[$letter] = '#,##0';
            }
        }

        return $formats;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0E6F6A'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->columns));
                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');
                $sheet->getStyle("A1:{$lastColumn}1")->getAlignment()->setVertical('center');
                $sheet->getRowDimension(1)->setRowHeight(22);
            },
        ];
    }
}
