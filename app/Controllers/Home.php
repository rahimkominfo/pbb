<?php

namespace App\Controllers;

use App\Models\TargetModel;

class Home extends BaseController
{
    /**
     * Renders the home dashboard.
     */
    public function index(): string
    {
        $targetModel = new TargetModel();
        $years = $targetModel->getAvailableYears();

        // Determine default year: if DB has years, take the latest (first in sorted list), else fallback to 2026
        $defaultYear = !empty($years) ? $years[0] : 2026;

        // Ensure 2026 is at least present in the options list
        if (empty($years)) {
            $years = [2026];
        } elseif (!in_array(2026, $years)) {
            $years[] = 2026;
            rsort($years);
        }

        // Get selected year from query parameters, default to the detected default year
        $selectedYear = $this->request->getGet('tahun');
        if (!$selectedYear || !in_array((int)$selectedYear, $years)) {
            $selectedYear = $defaultYear;
        }

        // Fetch chart data securely (only containing names and percentages)
        $chartData = $targetModel->getRealisasiPerKecamatan((int)$selectedYear);

        return view('home', [
            'years'        => $years,
            'selectedYear' => (int)$selectedYear,
            'chartData'    => $chartData,
        ]);
    }

    /**
     * API endpoint that handles AJAX requests for realisasi data.
     * Securely returns only kecamatan name and percentage.
     */
    public function getChartData()
    {
        $tahun = $this->request->getGet('tahun') ?? 2026;
        $targetModel = new TargetModel();
        $chartData = $targetModel->getRealisasiPerKecamatan((int)$tahun);

        return $this->response->setJSON($chartData);
    }
}
