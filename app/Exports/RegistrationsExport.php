<?php

namespace App\Exports;

use App\Models\McuRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistrationsExport implements FromCollection, WithColumnWidths, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $status;

    protected $search;

    protected $tanggal;

    public function __construct(?string $status = null, ?string $search = null, ?string $tanggal = null)
    {
        $this->status = $status;
        $this->search = $search;
        $this->tanggal = $tanggal;
    }

    public function collection()
    {
        $query = McuRegistration::with(['user', 'package']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->tanggal) {
            $query->whereDate('created_at', $this->tanggal);
        }

        if ($this->search) {
            $search = $this->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NIP',
            'Paket MCU',
            'Tanggal Daftar',
            'Jadwal Periksa',
            'Status',
            'Catatan Admin',
        ];
    }

    public function map($reg): array
    {
        static $i = 0;
        $i++;

        return [
            $i,
            $reg->user?->name ?? '-',
            $reg->user?->nip ?? '-',
            $reg->package?->nama_paket ?? '-',
            $reg->created_at?->format('d/m/Y H:i') ?? '-',
            $reg->tanggal_jadwal?->format('d/m/Y') ?? '-',
            $this->mapStatus($reg->status),
            $reg->catatan_admin ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Rekap MCU';
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 28,
            'C' => 18,
            'D' => 22,
            'E' => 18,
            'F' => 16,
            'G' => 18,
            'H' => 30,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A4:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A4:A{$lastRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        return $sheet;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Title row
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'REKAP DATA MEDICAL CHECK UP');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => '1E3A5F'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Subtitle row
                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2', 'Per '.now()->format('d/m/Y H:i').' WIB');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '6B7280'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);

                // Alternating row colors
                $lastRow = $sheet->getHighestRow();
                for ($row = 4; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F1F5F9'],
                            ],
                        ]);
                    }
                }

                // Auto-filter
                $sheet->setAutoFilter("A3:H{$lastRow}");

                // Freeze header
                $sheet->freezePane('A4');
            },
        ];
    }

    protected function mapStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'doctor_done' => 'Fisik Selesai',
            'lab_done' => 'Lab Selesai',
            'radiology_done' => 'Radiologi Selesai',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            default => $status,
        };
    }
}
