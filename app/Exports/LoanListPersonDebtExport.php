<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoanListPersonDebtExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $datas;
    protected $startDate;
    protected $endDate;

    public function __construct($datas, $startDate, $endDate)
    {
        $this->datas = $datas;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return $this->datas;
    }

    public function headings(): array
    {
        return [
            'N°',
            'Fecha',
            'Propietario',
            'Dirección',
            'Servicio',
            'Detalle',
            'Realizado Por',
        ];
    }

    public function map($row): array
    {
        static $count = 0;
        $count++;

        // Formatear nombre completo del propietario
        $propietario = 'S/N';
        if ($row->person) {
            $propietario = trim(
                ($row->person->first_name ?? '') . ' ' .
                ($row->person->middle_name ?? '') . ' ' .
                ($row->person->paternal_surname ?? '') . ' ' .
                ($row->person->maternal_surname ?? '')
            );
        }

        // Formatear nombre completo del trabajador
        $trabajador = 'S/N';
        if ($row->worker) {
            $trabajador = trim(
                ($row->worker->first_name ?? '') . ' ' .
                ($row->worker->middle_name ?? '') . ' ' .
                ($row->worker->paternal_surname ?? '') . ' ' .
                ($row->worker->maternal_surname ?? '')
            );
        }

        return [
            $count,
            \Carbon\Carbon::parse($row->date)->format('d/m/Y'),
            $propietario,
            $row->address ?? '',
            $row->type ?? '',
            trim($row->detail ?? $row->observation ?? ''),
            $trabajador,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Insertar 4 filas al inicio para el encabezado
                $sheet->insertNewRowBefore(1, 4);

                // Título del reporte
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'REPORTE DE SERVICIOS REALIZADOS A DOMICILIO');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '1F4E78']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Fecha del reporte
                $months = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                
                if ($this->startDate == $this->endDate) {
                    $dateText = \Carbon\Carbon::parse($this->startDate)->format('d') . ' de ' . 
                                $months[intval(\Carbon\Carbon::parse($this->startDate)->format('m'))] . ' de ' . 
                                \Carbon\Carbon::parse($this->startDate)->format('Y');
                } else {
                    $dateText = \Carbon\Carbon::parse($this->startDate)->format('d') . ' de ' . 
                                $months[intval(\Carbon\Carbon::parse($this->startDate)->format('m'))] . ' de ' . 
                                \Carbon\Carbon::parse($this->startDate)->format('Y') . ' al ' . 
                                \Carbon\Carbon::parse($this->endDate)->format('d') . ' de ' . 
                                $months[intval(\Carbon\Carbon::parse($this->endDate)->format('m'))] . ' de ' . 
                                \Carbon\Carbon::parse($this->endDate)->format('Y');
                }

                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', $dateText);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size' => 11,
                        'italic' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Información de generación
                $sheet->mergeCells('A3:G3');
                $sheet->setCellValue('A3', 'Generado por: ' . \Auth::user()->name . ' | ' . date('d/M/Y h:i a'));
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 9],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Ajustar altura de las primeras filas
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(15);
                $sheet->getRowDimension(4)->setRowHeight(5);

                // Estilo para encabezados (ahora en la fila 5)
                $sheet->getStyle('A5:G5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Bordes para toda la tabla
                $lastRowData = $lastRow + 4;
                $sheet->getStyle("A5:G{$lastRowData}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Centrar columnas específicas
                $sheet->getStyle("A6:A{$lastRowData}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B6:B{$lastRowData}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E6:E{$lastRowData}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Alineación vertical para todas las celdas de datos
                $sheet->getStyle("A6:G{$lastRowData}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                // Zebra striping (filas alternadas)
                for ($i = 6; $i <= $lastRowData; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle("A{$i}:G{$i}")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F2F2F2']
                            ],
                        ]);
                    }
                }

                // Ajustar wrap text para la columna de Detalle
                $sheet->getStyle("F6:F{$lastRowData}")->getAlignment()->setWrapText(true);

                // Footer - Solución Digital
                $footerRow = $lastRowData + 2;
                $sheet->mergeCells("A{$footerRow}:G{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", 'Desarrollado por Solución Digital - 67285914');
                $sheet->getStyle("A{$footerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                        'color' => ['rgb' => '4472C4']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function title(): string
    {
        if ($this->startDate == $this->endDate) {
            return 'Servicios ' . \Carbon\Carbon::parse($this->startDate)->format('d-m-Y');
        }
        return 'Servicios del ' . \Carbon\Carbon::parse($this->startDate)->format('d-m-Y') . ' al ' . \Carbon\Carbon::parse($this->endDate)->format('d-m-Y');
    }
}