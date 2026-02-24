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
    protected $people;

    public function __construct($people)
    {
        $this->people = $people;
    }

    public function collection()
    {
        $data = [];
        $count = 1;
        $totalCapital = 0;
        $totalInteres = 0;
        $total = 0;
        $totalDeuda = 0;
        
        foreach ($this->people as $person) {
            $personName = trim($person->first_name . ' ' . $person->last_name1 . ' ' . $person->last_name2);
            
            foreach ($person->loans as $loan) {
                $loanDays = $loan->loanDay->sortBy('date');
                $lastDate = $loanDays->last()->date ?? null;
                $today = date('Y-m-d');
                
                if ($lastDate && $today > $lastDate) {
                    $status = 'MORA';
                } else {
                    $status = 'VIGENTE';
                }

                $data[] = [
                    'nro' => $count,
                    'cliente' => $personName,
                    'codigo_prestamo' => $loan->code,
                    'fecha_entrega' => \Carbon\Carbon::parse($loan->dateDelivered)->format('d/m/Y'),
                    'estado' => $status,
                    'ruta' => $loan->current_loan_route->route->name ?? 'N/A',
                    'capital' => $loan->amountLoan,
                    'interes' => $loan->amountPorcentage,
                    'total' => $loan->amountTotal,
                    'deuda' => $loan->debt,
                ];
                
                $totalCapital += $loan->amountLoan;
                $totalInteres += $loan->amountPorcentage;
                $total += $loan->amountTotal;
                $totalDeuda += $loan->debt;
                $count++;
            }
        }
        
        $data[] = [
            'nro' => '',
            'cliente' => '',
            'codigo_prestamo' => '',
            'fecha_entrega' => '',
            'estado' => '',
            'ruta' => 'TOTAL GENERAL',
            'capital' => $totalCapital,
            'interes' => $totalInteres,
            'total' => $total,
            'deuda' => $totalDeuda,
        ];
        
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'N°',
            'CLIENTE',
            'CÓDIGO PRÉSTAMO',
            'FECHA ENTREGA',
            'ESTADO',
            'RUTA',
            'CAPITAL',
            'INTERÉS',
            'TOTAL',
            'DEUDA',
        ];
    }

    public function map($row): array
    {
        return [
            $row['nro'],
            $row['cliente'],
            $row['codigo_prestamo'],
            $row['fecha_entrega'],
            $row['estado'],
            $row['ruta'],
            $row['capital'] !== '' ? number_format($row['capital'], 2, ',', '.') : '',
            $row['interes'] !== '' ? number_format($row['interes'], 2, ',', '.') : '',
            $row['total'] !== '' ? number_format($row['total'], 2, ',', '.') : '',
            $row['deuda'] !== '' ? number_format($row['deuda'], 2, ',', '.') : '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->insertNewRowBefore(1, 4);

                $sheet->mergeCells('A1:J1');
                $sheet->setCellValue('A1', 'REPORTE DE LISTA DE PERSONAS DEUDORAS');
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

                $sheet->mergeCells('A2:J2');
                $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i:s'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 11, 'italic' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A3:J3');
                $sheet->setCellValue('A3', 'Usuario: ' . auth()->user()->name);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 9],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(15);

                $sheet->getStyle('A5:J5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $lastRowData = $highestRow;
                $sheet->getStyle("A5:J{$lastRowData}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A6:A{$lastRowData}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C6:F{$lastRowData}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G6:J{$lastRowData}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                for ($i = 6; $i <= $lastRowData; $i++) {
                    if ($sheet->getCell("F{$i}")->getValue() && strpos($sheet->getCell("F{$i}")->getValue(), 'TOTAL GENERAL') !== false) {
                        $sheet->getStyle("A{$i}:J{$i}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'E8E8E8']
                            ],
                        ]);
                    } elseif ($i % 2 == 0) {
                        $sheet->getStyle("A{$i}:J{$i}")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F2F2F2']
                            ],
                        ]);
                    }
                }

                $footerRow = $lastRowData + 2;
                $sheet->mergeCells("A{$footerRow}:J{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", 'Desarrollado por Solución Digital - 67285914');
                $sheet->getStyle("A{$footerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '4472C4']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
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
        return 'Lista Personas Deudoras';
    }
}
