<?php

namespace App\Http\Controllers;

use App\Exports\TableExport;
use App\Models\Apartment;
use App\Support\Reports;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = Reports::type($request->query('report'));
        $filters = Reports::filters($request->query());
        $reports = new Reports;

        return view('reports.index', [
            'type' => $type,
            'types' => Reports::TYPES,
            'statuses' => Reports::STATUSES[$type],
            'filters' => $filters,
            'data' => $reports->build($type, $filters),
            'summary' => $reports->summary($filters),
            'buildings' => Apartment::query()
                ->whereNotNull('building_name')
                ->where('building_name', '!=', '')
                ->distinct()
                ->orderBy('building_name')
                ->pluck('building_name'),
        ]);
    }

    public function export(Request $request, string $report)
    {
        abort_unless(array_key_exists($report, Reports::TYPES), 404);

        $filters = Reports::filters($request->query());
        $reports = new Reports;
        $data = $reports->build($report, $filters);

        if ($request->query('format') === 'pdf') {
            return Pdf::loadView('pdf.report', [
                'type' => $report,
                'data' => $data,
                'filters' => $filters,
                'summary' => $reports->summary($filters),
            ])->setPaper('a4', 'landscape')->download(
                'staytrack-'.$report.'-'.now()->format('Y-m-d').'.pdf'
            );
        }

        return Excel::download(
            new TableExport($data['sheet'], $data['headings'], $data['rows'], $data['totals']),
            'staytrack-'.$report.'-'.now()->format('Y-m-d').'.xlsx'
        );
    }
}
