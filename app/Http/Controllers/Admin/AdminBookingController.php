<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramBooking;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminBookingController extends Controller
{
    private function baseQuery(Request $request)
    {
        $query = ProgramBooking::with(['program', 'user.classroom', 'respondedBy'])->latest('id');

        // Optional date filtering (request created_at)
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to')->toString());
        }

        return $query;
    }

    /** GET /admin/data-booking */
    public function index(Request $request)
    {
        $bookings = $this->baseQuery($request)->paginate(50)->appends($request->query());

        return view('admin.sections.data_booking.index', compact('bookings'));
    }

    /** GET /admin/data-booking/export/excel */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $rows = $this->baseQuery($request)->limit(5000)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Booking');
        // Make borders visually obvious (like Excel "All Borders")
        $sheet->setShowGridlines(false);

        $headers = [
            'ID',
            'Siswa',
            'Kelas',
            'Program dan Kegiatan',
            'Tipe',
            'Jadwal',
            'Status',
            'BK',
            'Peserta',
            'Pesan',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = 'A1:' . $lastCol . '1';

        // Intentionally no AutoFilter (avoid header dropdown UI)

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F1F5F9'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ]);

        $data = [];
        foreach ($rows as $b) {
            $typeLabel = strtoupper((string) ($b->booking_type ?? 'individu'));
            $statusRaw = (string) ($b->status ?? '');
            $statusLabel = match ($statusRaw) {
                'approved' => 'APPROVED',
                'rejected' => 'REJECTED',
                'pending' => 'PENDING',
                default => strtoupper($statusRaw),
            };

            $participants = '-';
            if (($b->booking_type ?? 'individu') === 'group' && !empty($b->participants)) {
                $participants = collect($b->participants)->implode(', ');
            }

            $data[] = [
                (string) ($b->id ?? ''),
                (string) ($b->user->name ?? ''),
                (string) ($b->user?->classroom?->name ?? ''),
                (string) ($b->program->title ?? ''),
                (string) $typeLabel,
                optional($b->scheduled_at)->format('Y-m-d H:i:s') ?? '',
                (string) $statusLabel,
                (string) ($b->respondedBy->name ?? ''),
                (string) $participants,
                (string) ($b->message ?? ''),
            ];
        }

        if (!empty($data)) {
            $sheet->fromArray($data, null, 'A2');
        }

        $highestRow = (int) $sheet->getHighestRow();
        $highestCol = (string) $sheet->getHighestColumn();
        $fullRange = 'A1:' . $highestCol . $highestRow;

        // Cell styling
        $sheet->getStyle($fullRange)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle($fullRange)->getAlignment()->setWrapText(true);
        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');

        // Column widths (avoid cramped columns)
        $widths = [
            'A' => 6,   // ID
            'B' => 22,  // Siswa
            'C' => 14,  // Kelas
            'D' => 34,  // Program dan Kegiatan
            'E' => 12,  // Tipe
            'F' => 18,  // Jadwal
            'G' => 12,  // Status
            'H' => 22,  // BK
            'I' => 36,  // Peserta
            'J' => 40,  // Pesan
        ];
        foreach ($widths as $col => $w) {
            if (Coordinate::columnIndexFromString($col) <= count($headers)) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }
        }

        // Center align for compact columns
        foreach (['A','C','E','F','G'] as $col) {
            if (Coordinate::columnIndexFromString($col) <= count($headers)) {
                $sheet->getStyle($col . '2:' . $col . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'data-booking-');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return response()->download($tmpPath, 'data-booking.xlsx')->deleteFileAfterSend(true);
    }

    /** GET /admin/data-booking/export/pdf */
    public function exportPdf(Request $request)
    {
        $rows = $this->baseQuery($request)->limit(2000)->get();

        $pdf = Pdf::loadView('admin.sections.data_booking.pdf', ['rows' => $rows])
            ->setPaper('a4', 'landscape');

        return $pdf->download('data-booking.pdf');
    }
}
