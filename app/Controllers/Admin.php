<?php

namespace App\Controllers;

class Admin extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Helper to get list of years for filter dropdowns.
     */
    private function getAvailableYears(): array
    {
        $years = $this->db->table('trn_target')
            ->select('DISTINCT(tahun) as tahun')
            ->orderBy('tahun', 'DESC')
            ->get()
            ->getResultArray();

        $yearList = array_map(function ($row) {
            return (int) $row['tahun'];
        }, $years);

        if (empty($yearList)) {
            $yearList = [2026];
        } elseif (!in_array(2026, $yearList)) {
            $yearList[] = 2026;
            rsort($yearList);
        }

        return $yearList;
    }

    /**
     * Helper to get list of years for collector filter dropdowns.
     */
    private function getAvailableCollectorYears(): array
    {
        $years = $this->db->table('mst_kolektor')
            ->select('DISTINCT(tahun) as tahun')
            ->orderBy('tahun', 'DESC')
            ->get()
            ->getResultArray();

        $yearList = array_map(function ($row) {
            return (int) $row['tahun'];
        }, $years);

        if (empty($yearList)) {
            $yearList = [2026];
        } elseif (!in_array(2026, $yearList)) {
            $yearList[] = 2026;
            rsort($yearList);
        }

        return $yearList;
    }

    /**
     * Admin Dashboard: Capaian & Realisasi drill-down dashboard.
     */
    public function dashboard()
    {
        $years = $this->getAvailableYears();
        
        $selectedYear = $this->request->getGet('tahun');
        if (!$selectedYear || !in_array((int)$selectedYear, $years)) {
            $dbYears = $this->db->table('trn_target')->select('DISTINCT(tahun) as tahun')->orderBy('tahun', 'DESC')->get()->getResultArray();
            $selectedYear = !empty($dbYears) ? (int)$dbYears[0]['tahun'] : 2026;
        }

        $kecId = $this->request->getGet('kecamatan_id');
        $desaId = $this->request->getGet('desa_id');

        // Fetch all kecamatan for filter
        $kecamatans = $this->db->table('mst_kecamatan')
            ->orderBy('nm_kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        // Fetch desas for selected kecamatan if any
        $desasFiltered = [];
        if ($kecId) {
            $desasFiltered = $this->db->table('mst_desa')
                ->where('kecamatan_id', $kecId)
                ->orderBy('nm_desa', 'ASC')
                ->get()
                ->getResultArray();
        }

        // Summary calculation
        // Calculate Target and Realisasi for the selected area (Desa / Kecamatan / Kabupaten)
        // Target calculation
        $targetBuilder = $this->db->table('trn_target')
            ->select('SUM(target) as target')
            ->where('tahun', $selectedYear);
        if ($desaId) {
            $targetBuilder->where('desa_id', $desaId);
        } elseif ($kecId) {
            $targetBuilder->join('mst_desa', 'trn_target.desa_id = mst_desa.desa_id')
                ->where('mst_desa.kecamatan_id', $kecId);
        }
        $targetRow = $targetBuilder->get()->getRowArray();
        $totalTarget = (float)($targetRow['target'] ?? 0);

        // Realisasi calculation from trn_realisasi_dsh
        $realBuilder = $this->db->table('trn_realisasi_dsh r')
            ->select('SUM(r.realisasi) as realisasi')
            ->join('mst_kolektor c', 'r.kolektor_id = c.kolektor_id')
            ->join('mst_desa d', 'c.desa_id = d.desa_id')
            ->where('YEAR(r.tgl_bayar)', $selectedYear);
        if ($desaId) {
            $realBuilder->where('d.desa_id', $desaId);
        } elseif ($kecId) {
            $realBuilder->where('d.kecamatan_id', $kecId);
        }
        $realRow = $realBuilder->get()->getRowArray();
        $totalRealisasi = (float)($realRow['realisasi'] ?? 0);

        $totalSisa = max(0.0, $totalTarget - $totalRealisasi);
        $totalPersen = $totalTarget > 0 ? ($totalRealisasi / $totalTarget) * 100 : 0.0;

        // Build Drill-down Hierarchical Data
        // 1. Get targets
        $targetBuilder = $this->db->table('trn_target t')
            ->select('t.desa_id, t.target, t.nop, t.realisasi, d.nm_desa, d.kecamatan_id, k.nm_kecamatan')
            ->join('mst_desa d', 't.desa_id = d.desa_id')
            ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id')
            ->where('t.tahun', $selectedYear)
            ->orderBy('k.kecamatan_id', 'ASC')
            ->orderBy('d.desa_id', 'ASC');

        if ($kecId) {
            $targetBuilder->where('d.kecamatan_id', $kecId);
            if ($desaId) {
                $targetBuilder->where('d.desa_id', $desaId);
            }
        }

        $desaTargets = $targetBuilder->get()->getResultArray();

        // 2. Get collector realisations for the selected year
        $colRealBuilder = $this->db->table('mst_kolektor c')
            ->select('c.kolektor_id, c.nm_kolektor, c.desa_id, SUM(r.realisasi) as total_setor')
            ->join('trn_realisasi_dsh r', 'c.kolektor_id = r.kolektor_id AND YEAR(r.tgl_bayar) = ' . $selectedYear, 'left')
            ->where('c.tahun', $selectedYear)
            ->groupBy('c.kolektor_id');
        
        $colRealArray = $colRealBuilder->get()->getResultArray();

        // Build Tree structure
        $drilldown = [];
        foreach ($desaTargets as $dt) {
            $kId = $dt['kecamatan_id'];
            $kName = $dt['nm_kecamatan'];
            $dId = $dt['desa_id'];
            $dName = $dt['nm_desa'];
            
            if (!isset($drilldown[$kId])) {
                $drilldown[$kId] = [
                    'id' => $kId,
                    'name' => $kName,
                    'target' => 0.0,
                    'realisasi' => 0.0,
                    'desas' => []
                ];
            }
            
            // Get collectors for this desa
            $collectorsInDesa = [];
            foreach ($colRealArray as $col) {
                if ($col['desa_id'] == $dId) {
                    $collectorsInDesa[] = $col;
                }
            }

            $numCollectors = count($collectorsInDesa);
            $desaTarget = (float)$dt['target'];
            $desaRealisasi = 0.0;
            foreach ($collectorsInDesa as $col) {
                $desaRealisasi += (float)($col['total_setor'] ?? 0.0);
            }

            $kolektorsMapped = [];
            foreach ($collectorsInDesa as $col) {
                $colRealisasi = (float)($col['total_setor'] ?? 0.0);
                
                $kolektorsMapped[] = [
                    'id' => $col['kolektor_id'],
                    'name' => $col['nm_kolektor'],
                    'realisasi' => $colRealisasi,
                ];
            }

            $drilldown[$kId]['desas'][$dId] = [
                'id' => $dId,
                'name' => $dName,
                'target' => $desaTarget,
                'realisasi' => $desaRealisasi,
                'sisa' => max(0.0, $desaTarget - $desaRealisasi),
                'persen' => $desaTarget > 0 ? ($desaRealisasi / $desaTarget) * 100 : 0.0,
                'kolektors' => $kolektorsMapped
            ];

            $drilldown[$kId]['target'] += $desaTarget;
            $drilldown[$kId]['realisasi'] += $desaRealisasi;
        }

        // Finalise percentages for kecamatan
        foreach ($drilldown as $kId => &$kData) {
            $kData['sisa'] = max(0.0, $kData['target'] - $kData['realisasi']);
            $kData['persen'] = $kData['target'] > 0 ? ($kData['realisasi'] / $kData['target']) * 100 : 0.0;
        }

        return view('admin/dashboard', [
            'years' => $years,
            'selectedYear' => (int)$selectedYear,
            'kecId' => $kecId,
            'desaId' => $desaId,
            'kecamatans' => $kecamatans,
            'desasFiltered' => $desasFiltered,
            'totalTarget' => $totalTarget,
            'totalRealisasi' => $totalRealisasi,
            'totalSisa' => $totalSisa,
            'totalPersen' => $totalPersen,
            'drilldown' => $drilldown
        ]);
    }

    /**
     * Master Kolektor Management page.
     */
    public function kolektor()
    {
        $years = $this->getAvailableCollectorYears();
        $selectedYear = $this->request->getGet('tahun');
        if (!$selectedYear || !in_array((int)$selectedYear, $years)) {
            $selectedYear = !empty($years) ? (int)$years[0] : 2026;
        }

        $search = $this->request->getGet('search');
        $kecId = $this->request->getGet('kecamatan_id');
        $desaId = $this->request->getGet('desa_id');

        // Filter Kecamatan
        $kecamatans = $this->db->table('mst_kecamatan')
            ->orderBy('nm_kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        // Fetch desas list if kecamatan is selected
        $desas = [];
        if (!empty($kecId)) {
            $desas = $this->db->table('mst_desa')
                ->where('kecamatan_id', $kecId)
                ->orderBy('nm_desa', 'ASC')
                ->get()
                ->getResultArray();
        }

        // Building pagination query
        $builder = $this->db->table('mst_kolektor c')
            ->select('c.*, d.nm_desa, k.nm_kecamatan, d.kecamatan_id')
            ->join('mst_desa d', 'c.desa_id = d.desa_id')
            ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id')
            ->where('c.tahun', $selectedYear);

        if (!empty($search)) {
            $builder->like('c.nm_kolektor', $search);
        }

        if (!empty($kecId)) {
            $builder->where('d.kecamatan_id', $kecId);
        }

        if (!empty($desaId)) {
            $builder->where('c.desa_id', $desaId);
        }

        // Pagination parameters
        $page = (int)($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;
        
        $totalItems = $builder->countAllResults(false);
        $totalPages = ceil($totalItems / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;
        $kolektors = $builder->orderBy('c.kolektor_id', 'DESC')->limit($perPage, $offset)->get()->getResultArray();

        return view('admin/kolektor', [
            'kolektors' => $kolektors,
            'kecamatans' => $kecamatans,
            'desas' => $desas,
            'years' => $years,
            'selectedYear' => (int)$selectedYear,
            'search' => $search,
            'selectedKec' => $kecId,
            'selectedDesa' => $desaId,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems
        ]);
    }

    /**
     * AJAX endpoint: Get desas in kecamatan.
     */
    public function getDesasByKecamatan()
    {
        $kecId = $this->request->getGet('kecamatan_id');
        $desas = [];
        if ($kecId) {
            $desas = $this->db->table('mst_desa')
                ->where('kecamatan_id', $kecId)
                ->orderBy('nm_desa', 'ASC')
                ->get()
                ->getResultArray();
        }
        return $this->response->setJSON($desas);
    }

    /**
     * AJAX endpoint to check if a collector code already exists in a village for a specific year.
     */
    public function checkDuplicateKolektor()
    {
        $desaId = $this->request->getGet('desa_id');
        $tahun = $this->request->getGet('tahun');
        $kdKolektor = $this->request->getGet('kd_kolektor');
        $kolektorId = $this->request->getGet('kolektor_id');

        if (empty($desaId) || empty($kdKolektor) || empty($tahun)) {
            return $this->response->setJSON(['duplicate' => false]);
        }

        $builder = $this->db->table('mst_kolektor')
            ->where('desa_id', $desaId)
            ->where('tahun', (int)$tahun)
            ->where('kd_kolektor', $kdKolektor);

        if (!empty($kolektorId)) {
            $builder->where('kolektor_id !=', $kolektorId);
        }

        $count = $builder->countAllResults();

        return $this->response->setJSON(['duplicate' => $count > 0]);
    }

    /**
     * Saves/Updates Collector data.
     */
    public function saveKolektor()
    {
        $id = $this->request->getPost('kolektor_id');
        $desaId = $this->request->getPost('desa_id');
        $tahun = $this->request->getPost('tahun');
        $kdKolektor = $this->request->getPost('kd_kolektor');
        $nmKolektor = $this->request->getPost('nm_kolektor');
        $dusun = $this->request->getPost('dusun');
        $norekKolektor = $this->request->getPost('norek_kolektor');

        if (empty($desaId) || empty($tahun) || empty($kdKolektor) || empty($nmKolektor)) {
            return redirect()->back()->with('error', 'Semua data wajib diisi kecuali dusun dan nomor rekening.')->withInput();
        }

        $tahun = (int)$tahun;
        if ($tahun < 2000 || $tahun > 2100) {
            return redirect()->back()->with('error', 'Tahun tidak valid.')->withInput();
        }

        if (strlen($kdKolektor) != 2 || !is_numeric($kdKolektor)) {
            return redirect()->back()->with('error', 'Kode Kolektor harus berisi 2 digit angka.')->withInput();
        }

        // Check if collector code already exists for that desa and year (excluding current if editing)
        $chkBuilder = $this->db->table('mst_kolektor')
            ->where(['desa_id' => $desaId, 'tahun' => $tahun, 'kd_kolektor' => $kdKolektor]);
        if ($id) {
            $chkBuilder->where('kolektor_id !=', $id);
        }
        $existing = $chkBuilder->get()->getRowArray();

        if ($existing) {
            return redirect()->back()->with('error', 'Kode Kolektor "' . esc($kdKolektor) . '" sudah terdaftar untuk Desa ini pada tahun ' . $tahun . '.')->withInput();
        }

        $data = [
            'desa_id' => $desaId,
            'tahun' => $tahun,
            'kd_kolektor' => $kdKolektor,
            'nm_kolektor' => $nmKolektor,
            'dusun' => !empty($dusun) ? $dusun : null,
            'norek_kolektor' => !empty($norekKolektor) ? $norekKolektor : null
        ];

        if ($id) {
            $this->db->table('mst_kolektor')->where('kolektor_id', $id)->update($data);
            $msg = 'Data Kolektor berhasil diperbarui.';
        } else {
            $this->db->table('mst_kolektor')->insert($data);
            $msg = 'Kolektor baru berhasil ditambahkan.';
        }

        return redirect()->to(base_url('admin/kolektor?tahun=' . $tahun))->with('success', $msg);
    }

    /**
     * Copy collectors from one year to another.
     */
    public function copyKolektor()
    {
        $fromYear = (int)$this->request->getPost('from_year');
        $toYear = (int)$this->request->getPost('to_year');
        $kecId = $this->request->getPost('kecamatan_id');
        $desaId = $this->request->getPost('desa_id');

        if (empty($fromYear) || empty($toYear)) {
            return redirect()->back()->with('error', 'Tahun asal dan tahun tujuan wajib dipilih.');
        }

        if ($fromYear === $toYear) {
            return redirect()->back()->with('error', 'Tahun asal dan tahun tujuan tidak boleh sama.');
        }

        $builder = $this->db->table('mst_kolektor c');
        if (!empty($desaId)) {
            $builder->where('c.desa_id', $desaId);
        } elseif (!empty($kecId)) {
            $builder->join('mst_desa d', 'c.desa_id = d.desa_id')
                    ->where('d.kecamatan_id', $kecId);
        }
        $builder->where('c.tahun', $fromYear);
        $sources = $builder->get()->getResultArray();

        if (empty($sources)) {
            return redirect()->back()->with('error', 'Tidak ada data kolektor pada tahun ' . $fromYear . ' untuk disalin.');
        }

        $copied = 0;
        $skipped = 0;
        foreach ($sources as $src) {
            // Check duplicate in target year
            $exists = $this->db->table('mst_kolektor')
                ->where([
                    'desa_id' => $src['desa_id'],
                    'tahun' => $toYear,
                    'kd_kolektor' => $src['kd_kolektor']
                ])
                ->countAllResults();

            if ($exists == 0) {
                $this->db->table('mst_kolektor')->insert([
                    'desa_id' => $src['desa_id'],
                    'tahun' => $toYear,
                    'kd_kolektor' => $src['kd_kolektor'],
                    'nm_kolektor' => $src['nm_kolektor'],
                    'dusun' => $src['dusun'],
                    'norek_kolektor' => $src['norek_kolektor']
                ]);
                $copied++;
            } else {
                $skipped++;
            }
        }

        $msg = "Berhasil menyalin {$copied} kolektor ke tahun {$toYear}.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} kolektor dilewati karena kode sudah terdaftar).";
        }

        return redirect()->to(base_url('admin/kolektor?tahun=' . $toYear))->with('success', $msg);
    }

    /**
     * Deletes Collector.
     */
    public function deleteKolektor()
    {
        $id = $this->request->getPost('kolektor_id');
        $redirectYear = $this->request->getPost('redirect_tahun') ?? 2026;
        if ($id) {
            // Check if there are realisasi references
            $hasRef = $this->db->table('trn_realisasi_dsh')->where('kolektor_id', $id)->countAllResults();
            if ($hasRef > 0) {
                return redirect()->back()->with('error', 'Kolektor tidak dapat dihapus karena sudah memiliki transaksi setoran.');
            }
            $this->db->table('mst_kolektor')->where('kolektor_id', $id)->delete();
            return redirect()->to(base_url('admin/kolektor?tahun=' . $redirectYear))->with('success', 'Kolektor berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'ID Kolektor tidak valid.');
    }

    /**
     * penetapan PBB target for desas.
     */
    public function target()
    {
        $years = $this->getAvailableYears();
        
        $selectedYear = $this->request->getGet('tahun');
        if (!$selectedYear || !in_array((int)$selectedYear, $years)) {
            $dbYears = $this->db->table('trn_target')->select('DISTINCT(tahun) as tahun')->orderBy('tahun', 'DESC')->get()->getResultArray();
            $selectedYear = !empty($dbYears) ? (int)$dbYears[0]['tahun'] : 2026;
        }

        $kecId = $this->request->getGet('kecamatan_id');
        $desaId = $this->request->getGet('desa_id');

        $kecamatans = $this->db->table('mst_kecamatan')
            ->orderBy('nm_kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        // Fetch desas list if kecamatan is selected
        $desas = [];
        if (!empty($kecId)) {
            $desas = $this->db->table('mst_desa')
                ->where('kecamatan_id', $kecId)
                ->orderBy('nm_desa', 'ASC')
                ->get()
                ->getResultArray();
        }

        // Building pagination query
        $builder = $this->db->table('trn_target t')
            ->select('t.target_id, t.desa_id, t.target, t.nop, t.tahun, d.nm_desa, k.nm_kecamatan,
                      (SELECT COALESCE(SUM(r.realisasi), 0) 
                       FROM trn_realisasi_dsh r 
                       JOIN mst_kolektor c ON r.kolektor_id = c.kolektor_id 
                       WHERE c.desa_id = t.desa_id AND YEAR(r.tgl_bayar) = t.tahun) as realisasi')
            ->join('mst_desa d', 't.desa_id = d.desa_id')
            ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id')
            ->where('t.tahun', $selectedYear);

        if (!empty($kecId)) {
            $builder->where('d.kecamatan_id', $kecId);
        }

        if (!empty($desaId)) {
            $builder->where('t.desa_id', $desaId);
        }

        // Pagination params
        $page = (int)($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;
        
        $totalItems = $builder->countAllResults(false);
        $totalPages = ceil($totalItems / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;
        $targets = $builder->orderBy('t.target_id', 'DESC')->limit($perPage, $offset)->get()->getResultArray();

        return view('admin/target', [
            'years' => $years,
            'selectedYear' => (int)$selectedYear,
            'kecamatans' => $kecamatans,
            'desas' => $desas,
            'selectedKec' => $kecId,
            'selectedDesa' => $desaId,
            'targets' => $targets,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems
        ]);
    }

    /**
     * AJAX endpoint to fetch a single target for Edit.
     */
    public function fetchTarget()
    {
        $id = $this->request->getPost('target_id');
        $target = $this->db->table('trn_target t')
            ->select('t.*, d.kecamatan_id')
            ->join('mst_desa d', 't.desa_id = d.desa_id')
            ->where('t.target_id', $id)
            ->get()
            ->getRowArray();
        return $this->response->setJSON($target);
    }

    /**
     * Saves/Updates Desa PBB Target.
     */
    public function saveTarget()
    {
        $id = $this->request->getPost('target_id');
        $tahun = $this->request->getPost('tahun');
        $desaId = $this->request->getPost('desa_id');
        $nop = $this->request->getPost('nop');
        $target = $this->request->getPost('target');

        if ($id) {
            // Update mode: validate only nop and target (since year and village are read-only/disabled)
            if (empty($nop) || empty($target)) {
                return redirect()->back()->with('error', 'Jumlah NOP dan Jumlah Target wajib diisi.')->withInput();
            }

            $this->db->table('trn_target')
                ->where('target_id', $id)
                ->update(['nop' => $nop, 'target' => $target]);
            $msg = 'Target PBB berhasil diperbarui.';

            // Get target year for redirection
            $targetRecord = $this->db->table('trn_target')->where('target_id', $id)->get()->getRowArray();
            $tahun = $targetRecord ? $targetRecord['tahun'] : date('Y');
        } else {
            // Insert mode: validate all fields
            if (empty($tahun) || empty($desaId) || empty($nop) || empty($target)) {
                return redirect()->back()->with('error', 'Tahun, Desa, Jumlah NOP, dan Jumlah Target wajib diisi.')->withInput();
            }

            // Check if target already exists for that desa and year
            $existing = $this->db->table('trn_target')
                ->where(['desa_id' => $desaId, 'tahun' => $tahun])
                ->get()
                ->getRowArray();

            if ($existing) {
                return redirect()->back()->with('error', 'Target untuk Desa tersebut pada tahun ' . $tahun . ' sudah terdaftar.')->withInput();
            }

            // Insert new target record
            $this->db->table('trn_target')->insert([
                'desa_id' => $desaId,
                'target' => $target,
                'nop' => $nop,
                'realisasi' => 0,
                'tahun' => $tahun
            ]);
            $msg = 'Target PBB baru berhasil ditetapkan.';
        }

        return redirect()->to(base_url('admin/target?tahun=' . $tahun))->with('success', $msg);
    }

    /**
     * Riwayat setoran (Realisasi).
     */
    public function setor()
    {
        // Months array for filtering
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $search = $this->request->getGet('search');
        
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');

        $filterMonth = $this->request->getGet('bulan');
        if ($filterMonth === null) {
            $filterMonth = $currentMonth;
        }

        $filterYear = $this->request->getGet('tahun');
        if ($filterYear === null) {
            $filterYear = $currentYear;
        }

        // Fetch collectors with their villages for dropdown
        $kolektorsSelect = $this->db->table('mst_kolektor c')
            ->select('c.kolektor_id, c.nm_kolektor, c.tahun, d.nm_desa')
            ->join('mst_desa d', 'c.desa_id = d.desa_id')
            ->orderBy('c.tahun', 'DESC')
            ->orderBy('c.nm_kolektor', 'ASC')
            ->get()
            ->getResultArray();

        $builder = $this->db->table('trn_realisasi_dsh r')
            ->select('r.*, c.nm_kolektor, c.tahun as kolektor_tahun, d.nm_desa, k.nm_kecamatan')
            ->join('mst_kolektor c', 'r.kolektor_id = c.kolektor_id')
            ->join('mst_desa d', 'c.desa_id = d.desa_id')
            ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id');

        if (!empty($search)) {
            $builder->like('c.nm_kolektor', $search);
        }

        if (!empty($filterMonth)) {
            $builder->where('MONTH(r.tgl_bayar)', (int)$filterMonth);
        }

        if (!empty($filterYear)) {
            $builder->where('YEAR(r.tgl_bayar)', (int)$filterYear);
        }

        // Pagination
        $page = (int)($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;
        
        $totalItems = $builder->countAllResults(false);
        $totalPages = ceil($totalItems / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;
        $setorans = $builder->orderBy('r.tgl_bayar', 'DESC')
            ->orderBy('r.realisasi_dsh_id', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        // Get years list for filtering based on payments
        $paymentYears = $this->db->table('trn_realisasi_dsh')
            ->select('DISTINCT(YEAR(tgl_bayar)) as tahun')
            ->orderBy('tahun', 'DESC')
            ->get()
            ->getResultArray();
        $paymentYears = array_map(function($x) { return (int)$x['tahun']; }, $paymentYears);
        if (empty($paymentYears)) {
            $paymentYears = [$currentYear];
        } elseif (!in_array($currentYear, $paymentYears)) {
            $paymentYears[] = $currentYear;
            rsort($paymentYears);
        }

        return view('admin/setor', [
            'setorans' => $setorans,
            'kolektors' => $kolektorsSelect,
            'months' => $months,
            'paymentYears' => $paymentYears,
            'search' => $search,
            'selectedMonth' => $filterMonth,
            'selectedYear' => $filterYear,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems
        ]);
    }

    public function saveSetor()
    {
        $id = $this->request->getPost('realisasi_dsh_id');
        $tglBayar = $this->request->getPost('tgl_bayar');
        $kolektorId = $this->request->getPost('kolektor_id');
        $nop = $this->request->getPost('nop');
        $nominal = $this->request->getPost('realisasi');

        if (empty($tglBayar) || empty($kolektorId) || empty($nop) || empty($nominal)) {
            return redirect()->back()->with('error', 'Semua data wajib diisi.')->withInput();
        }

        $nominal = (float)$nominal;
        $tahun = (int)date('Y', strtotime($tglBayar));

        // Start transaction
        $this->db->transStart();

        if ($id) {
            // Update Mode
            // 1. Fetch old record
            $oldSetor = $this->db->table('trn_realisasi_dsh')->where('realisasi_dsh_id', $id)->get()->getRowArray();
            if ($oldSetor) {
                $oldKolektorId = $oldSetor['kolektor_id'];
                $oldNominal = (float)$oldSetor['realisasi'];
                $oldTahun = (int)date('Y', strtotime($oldSetor['tgl_bayar']));

                // Deduct old nominal from old trn_target
                $oldCollector = $this->db->table('mst_kolektor')->where('kolektor_id', $oldKolektorId)->get()->getRowArray();
                if ($oldCollector) {
                    $oldDesaId = $oldCollector['desa_id'];
                    $oldTargetEntry = $this->db->table('trn_target')
                        ->where(['desa_id' => $oldDesaId, 'tahun' => $oldTahun])
                        ->get()
                        ->getRowArray();
                    if ($oldTargetEntry) {
                        $this->db->table('trn_target')
                            ->where('target_id', $oldTargetEntry['target_id'])
                            ->decrement('realisasi', $oldNominal);
                    }
                }
            }

            // 2. Update setoran record
            $this->db->table('trn_realisasi_dsh')
                ->where('realisasi_dsh_id', $id)
                ->update([
                    'kolektor_id' => $kolektorId,
                    'nop' => $nop,
                    'realisasi' => $nominal,
                    'tgl_bayar' => $tglBayar
                ]);
            $msg = 'Setoran berhasil diperbarui.';

        } else {
            // Insert Mode
            // 1. Insert setoran record
            $this->db->table('trn_realisasi_dsh')->insert([
                'kolektor_id' => $kolektorId,
                'nop' => $nop,
                'realisasi' => $nominal,
                'tgl_bayar' => $tglBayar
            ]);
            $msg = 'Setoran berhasil dicatat dan ditambahkan ke realisasi desa.';
        }

        // 3. Update/Insert new trn_target (increment realisasi)
        $collector = $this->db->table('mst_kolektor')->where('kolektor_id', $kolektorId)->get()->getRowArray();
        if ($collector) {
            $desaId = $collector['desa_id'];
            $targetEntry = $this->db->table('trn_target')
                ->where(['desa_id' => $desaId, 'tahun' => $tahun])
                ->get()
                ->getRowArray();

            if ($targetEntry) {
                $this->db->table('trn_target')
                    ->where('target_id', $targetEntry['target_id'])
                    ->increment('realisasi', $nominal);
            } else {
                // If it doesn't exist, insert a default target entry with 0 target and the nominal as realisasi
                $this->db->table('trn_target')->insert([
                    'desa_id' => $desaId,
                    'target' => 0,
                    'nop' => 0,
                    'realisasi' => $nominal,
                    'tahun' => $tahun
                ]);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menyimpan data setoran. Terjadi kesalahan database.')->withInput();
        }

        return redirect()->to(base_url('admin/setor'))->with('success', $msg);
    }

    /**
     * Deletes Setoran payment.
     */
    public function deleteSetor()
    {
        $id = $this->request->getPost('realisasi_dsh_id');
        if (empty($id)) {
            return redirect()->back()->with('error', 'ID Setoran tidak valid.');
        }

        // Start transaction
        $this->db->transStart();

        $setor = $this->db->table('trn_realisasi_dsh')->where('realisasi_dsh_id', $id)->get()->getRowArray();
        if ($setor) {
            $kolektorId = $setor['kolektor_id'];
            $nominal = (float)$setor['realisasi'];
            $tahun = (int)date('Y', strtotime($setor['tgl_bayar']));

            // Find desa_id of collector
            $collector = $this->db->table('mst_kolektor')->where('kolektor_id', $kolektorId)->get()->getRowArray();
            if ($collector) {
                $desaId = $collector['desa_id'];
                
                // Deduct from trn_target realisasi
                $targetEntry = $this->db->table('trn_target')
                    ->where(['desa_id' => $desaId, 'tahun' => $tahun])
                    ->get()
                    ->getRowArray();

                if ($targetEntry) {
                    $this->db->table('trn_target')
                        ->where('target_id', $targetEntry['target_id'])
                        ->decrement('realisasi', $nominal);
                }
            }

            // Delete setoran record
            $this->db->table('trn_realisasi_dsh')->where('realisasi_dsh_id', $id)->delete();
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menghapus setoran. Terjadi kesalahan database.');
        }

        return redirect()->to(base_url('admin/setor'))->with('success', 'Catatan setoran berhasil dihapus.');
    }

    /**
     * User accounts management page.
     */
    public function pengguna()
    {
        $search = $this->request->getGet('search');

        $builder = $this->db->table('sys_user');
        if (!empty($search)) {
            $builder->like('username', $search);
        }

        $users = $builder->orderBy('user_id', 'DESC')->get()->getResultArray();

        return view('admin/pengguna', [
            'users' => $users,
            'search' => $search
        ]);
    }

    /**
     * Saves User Account.
     */
    public function savePengguna()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $role = $this->request->getPost('role');

        if (empty($username) || empty($password) || empty($role)) {
            return redirect()->back()->with('error', 'Semua data wajib diisi.')->withInput();
        }

        // Validate username uniqueness
        $existing = $this->db->table('sys_user')->where('username', $username)->get()->getRowArray();
        if ($existing) {
            return redirect()->back()->with('error', 'Username "' . esc($username) . '" sudah digunakan.')->withInput();
        }

        // Hash password
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $this->db->table('sys_user')->insert([
            'username' => $username,
            'password' => $hashed,
            'role' => $role
        ]);

        return redirect()->to(base_url('admin/pengguna'))->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Resets User Password.
     */
    public function resetPassword()
    {
        $userId = $this->request->getPost('user_id');
        $newPassword = $this->request->getPost('password');

        if (empty($userId) || empty($newPassword)) {
            return redirect()->back()->with('error', 'User ID dan password baru wajib diisi.');
        }

        // Hash new password
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        $this->db->table('sys_user')
            ->where('user_id', $userId)
            ->update(['password' => $hashed]);

        return redirect()->to(base_url('admin/pengguna'))->with('success', 'Password pengguna berhasil di-reset.');
    }

    /**
     * Deletes User Account.
     */
    public function deletePengguna()
    {
        $userId = $this->request->getPost('user_id');
        
        // Cannot delete self
        if ($userId == session()->get('user_id')) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($userId) {
            $this->db->table('sys_user')->where('user_id', $userId)->delete();
            return redirect()->to(base_url('admin/pengguna'))->with('success', 'Pengguna berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'ID Pengguna tidak valid.');
    }

    /**
     * Master Wilayah (Kecamatan & Desa) page.
     */
    public function wilayah()
    {
        $tab = $this->request->getGet('tab') ?? 'kecamatan';
        if (!in_array($tab, ['kecamatan', 'desa'])) {
            $tab = 'kecamatan';
        }

        $kecamatans = $this->db->table('mst_kecamatan')
            ->orderBy('nm_kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        // For Desa tab
        $selectedKec = $this->request->getGet('kecamatan_id');
        $searchDesa = $this->request->getGet('search_desa');

        $desaBuilder = $this->db->table('mst_desa d')
            ->select('d.*, k.nm_kecamatan')
            ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id');

        if (!empty($selectedKec)) {
            $desaBuilder->where('d.kecamatan_id', $selectedKec);
        }

        if (!empty($searchDesa)) {
            $desaBuilder->like('d.nm_desa', $searchDesa);
        }

        // Pagination for Desa
        $desaPage = (int)($this->request->getGet('page') ?? 1);
        if ($desaPage < 1) $desaPage = 1;
        $desaPerPage = 10;

        $totalDesas = $desaBuilder->countAllResults(false);
        $totalDesaPages = ceil($totalDesas / $desaPerPage);
        if ($totalDesaPages < 1) $totalDesaPages = 1;
        if ($desaPage > $totalDesaPages) $desaPage = $totalDesaPages;

        $offset = ($desaPage - 1) * $desaPerPage;
        $desas = $desaBuilder->orderBy('k.nm_kecamatan', 'ASC')
            ->orderBy('d.nm_desa', 'ASC')
            ->limit($desaPerPage, $offset)
            ->get()
            ->getResultArray();

        return view('admin/wilayah', [
            'tab' => $tab,
            'kecamatans' => $kecamatans,
            'desas' => $desas,
            'selectedKec' => $selectedKec,
            'searchDesa' => $searchDesa,
            'currentPage' => $desaPage,
            'totalPages' => $totalDesaPages,
            'totalItems' => $totalDesas
        ]);
    }

    /**
     * Saves/Updates Kecamatan details (Camat details).
     */
    public function saveKecamatan()
    {
        $id = $this->request->getPost('kecamatan_id');
        $nmCamat = $this->request->getPost('nm_camat');
        $norekCamat = $this->request->getPost('norek_camat');

        if (empty($id)) {
            return redirect()->back()->with('error', 'Kecamatan ID tidak valid.')->withInput();
        }

        $data = [
            'nm_camat' => !empty($nmCamat) ? $nmCamat : null,
            'norek_camat' => !empty($norekCamat) ? $norekCamat : null
        ];

        $this->db->table('mst_kecamatan')
            ->where('kecamatan_id', $id)
            ->update($data);

        return redirect()->to(base_url('admin/wilayah?tab=kecamatan'))->with('success', 'Data Camat berhasil diperbarui.');
    }

    /**
     * Saves/Updates Desa details (Kepala Desa details).
     */
    public function saveDesa()
    {
        $id = $this->request->getPost('desa_id');
        $nmKades = $this->request->getPost('nm_kepala_desa');
        $norekKades = $this->request->getPost('norek_kepala_desa');
        $nmKoordinator = $this->request->getPost('nm_koordinator');
        $norekKoordinator = $this->request->getPost('norek_koordinator');

        if (empty($id)) {
            return redirect()->back()->with('error', 'Desa ID tidak valid.')->withInput();
        }

        $data = [
            'nm_kepala_desa' => !empty($nmKades) ? $nmKades : null,
            'norek_kepala_desa' => !empty($norekKades) ? $norekKades : null,
            'nm_koordinator' => !empty($nmKoordinator) ? $nmKoordinator : null,
            'norek_koordinator' => !empty($norekKoordinator) ? $norekKoordinator : null
        ];

        $this->db->table('mst_desa')
            ->where('desa_id', $id)
            ->update($data);

        // Preserve filters when redirecting back
        $kecId = $this->request->getPost('redirect_kecamatan_id');
        $search = $this->request->getPost('redirect_search_desa');
        $page = $this->request->getPost('redirect_page');
        
        $url = 'admin/wilayah?tab=desa';
        if (!empty($kecId)) $url .= '&kecamatan_id=' . $kecId;
        if (!empty($search)) $url .= '&search_desa=' . urlencode($search);
        if (!empty($page)) $url .= '&page=' . $page;

        return redirect()->to(base_url($url))->with('success', 'Data Desa berhasil diperbarui.');
    }

    /**
     * Laporan Insentif page.
     */
    public function insentif()
    {
        $years = $this->getAvailableYears();
        
        $selectedYear = $this->request->getGet('tahun');
        if (!$selectedYear || !in_array((int)$selectedYear, $years)) {
            $dbYears = $this->db->table('trn_target')->select('DISTINCT(tahun) as tahun')->orderBy('tahun', 'DESC')->get()->getResultArray();
            $selectedYear = !empty($dbYears) ? (int)$dbYears[0]['tahun'] : 2026;
        }

        $tab = $this->request->getGet('tab') ?? 'camat';
        if (!in_array($tab, ['camat', 'kades', 'kolektor'])) {
            $tab = 'camat';
        }

        // Percentage slider/input for calculations (default 5%)
        $rate = $this->request->getGet('rate');
        if ($rate === null || $rate === '') {
            $rate = 5.0;
        } else {
            $rate = (float)$rate;
        }

        $dataReport = [];

        if ($tab === 'camat') {
            // Fetch Kecamatan details, target, and computed realization
            $dataReport = $this->db->table('mst_kecamatan k')
                ->select('k.kecamatan_id, k.kd_kecamatan, k.nm_kecamatan, k.nm_camat, k.norek_camat,
                          COALESCE((SELECT SUM(t.target) FROM trn_target t JOIN mst_desa d ON t.desa_id = d.desa_id WHERE d.kecamatan_id = k.kecamatan_id AND t.tahun = ' . $selectedYear . '), 0) as target,
                          COALESCE((SELECT SUM(r.realisasi) FROM trn_realisasi_dsh r JOIN mst_kolektor c ON r.kolektor_id = c.kolektor_id JOIN mst_desa d ON c.desa_id = d.desa_id WHERE d.kecamatan_id = k.kecamatan_id AND YEAR(r.tgl_bayar) = ' . $selectedYear . '), 0) as realisasi')
                ->orderBy('k.nm_kecamatan', 'ASC')
                ->get()
                ->getResultArray();
        } elseif ($tab === 'kades') {
            // Fetch Desa details for Kepala Desa
            $dataReport = $this->db->table('mst_desa d')
                ->select('d.desa_id, d.kd_desa, d.nm_desa, d.nm_kepala_desa, d.norek_kepala_desa, d.nm_koordinator, d.norek_koordinator, k.nm_kecamatan, k.kd_kecamatan,
                          COALESCE((SELECT t.target FROM trn_target t WHERE t.desa_id = d.desa_id AND t.tahun = ' . $selectedYear . '), 0) as target,
                          COALESCE((SELECT SUM(r.realisasi) FROM trn_realisasi_dsh r JOIN mst_kolektor c ON r.kolektor_id = c.kolektor_id WHERE c.desa_id = d.desa_id AND YEAR(r.tgl_bayar) = ' . $selectedYear . '), 0) as realisasi')
                ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id')
                ->orderBy('k.nm_kecamatan', 'ASC')
                ->orderBy('d.nm_desa', 'ASC')
                ->get()
                ->getResultArray();
        } else {
            // Fetch Collector details (kolektor)
            $collectors = $this->db->table('mst_kolektor c')
                ->select('c.kolektor_id, c.kd_kolektor, c.nm_kolektor, c.norek_kolektor, c.desa_id, c.tahun, d.nm_desa, d.kd_desa, d.kecamatan_id, k.nm_kecamatan, k.kd_kecamatan')
                ->join('mst_desa d', 'c.desa_id = d.desa_id')
                ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id')
                ->where('c.tahun', $selectedYear)
                ->orderBy('k.nm_kecamatan', 'ASC')
                ->orderBy('d.nm_desa', 'ASC')
                ->orderBy('c.nm_kolektor', 'ASC')
                ->get()
                ->getResultArray();

            $desaTargets = $this->db->table('trn_target')
                ->where('tahun', $selectedYear)
                ->get()
                ->getResultArray();
            $targetLookup = [];
            foreach ($desaTargets as $dt) {
                $targetLookup[$dt['desa_id']] = (float)$dt['target'];
            }

            $collectorCounts = $this->db->table('mst_kolektor')
                ->select('desa_id, COUNT(*) as cnt')
                ->where('tahun', $selectedYear)
                ->groupBy('desa_id')
                ->get()
                ->getResultArray();
            $countsLookup = [];
            foreach ($collectorCounts as $cc) {
                $countsLookup[$cc['desa_id']] = (int)$cc['cnt'];
            }

            $realisations = $this->db->table('trn_realisasi_dsh')
                ->select('kolektor_id, SUM(realisasi) as total_realisasi')
                ->where('YEAR(tgl_bayar)', $selectedYear)
                ->groupBy('kolektor_id')
                ->get()
                ->getResultArray();
            $realLookup = [];
            foreach ($realisations as $r) {
                $realLookup[$r['kolektor_id']] = (float)$r['total_realisasi'];
            }

            $dataReport = [];
            foreach ($collectors as $col) {
                $desaId = $col['desa_id'];
                $colId = $col['kolektor_id'];
                
                $cnt = $countsLookup[$desaId] ?? 0;
                $villageTarget = $targetLookup[$desaId] ?? 0.0;
                $target = $cnt > 0 ? $villageTarget / $cnt : 0.0;
                $realisasi = $realLookup[$colId] ?? 0.0;
                
                $col['target'] = $target;
                $col['realisasi'] = $realisasi;
                $dataReport[] = $col;
            }
        }

        // Calculate totals for summary cards
        $totalTarget = 0.0;
        $totalRealisasi = 0.0;
        foreach ($dataReport as $row) {
            $totalTarget += (float)$row['target'];
            $totalRealisasi += (float)$row['realisasi'];
        }

        return view('admin/insentif', [
            'years' => $years,
            'selectedYear' => (int)$selectedYear,
            'tab' => $tab,
            'rate' => $rate,
            'dataReport' => $dataReport,
            'totalTarget' => $totalTarget,
            'totalRealisasi' => $totalRealisasi
        ]);
    }

    /**
     * Export Laporan Insentif to Excel format.
     */
    public function exportInsentif()
    {
        $selectedYear = $this->request->getGet('tahun');
        $tab = $this->request->getGet('tab') ?? 'camat';
        $rate = $this->request->getGet('rate');
        
        if ($rate === null || $rate === '') {
            $rate = 5.0;
        } else {
            $rate = (float)$rate;
        }

        $years = $this->getAvailableYears();
        if (!$selectedYear || !in_array((int)$selectedYear, $years)) {
            $dbYears = $this->db->table('trn_target')->select('DISTINCT(tahun) as tahun')->orderBy('tahun', 'DESC')->get()->getResultArray();
            $selectedYear = !empty($dbYears) ? (int)$dbYears[0]['tahun'] : 2026;
        }

        $filename = "laporan-insentif-" . $tab . "-" . $selectedYear . ".xls";

        if ($tab === 'camat') {
            $dataReport = $this->db->table('mst_kecamatan k')
                ->select('k.kecamatan_id, k.kd_kecamatan, k.nm_kecamatan, k.nm_camat, k.norek_camat,
                          COALESCE((SELECT SUM(t.target) FROM trn_target t JOIN mst_desa d ON t.desa_id = d.desa_id WHERE d.kecamatan_id = k.kecamatan_id AND t.tahun = ' . $selectedYear . '), 0) as target,
                          COALESCE((SELECT SUM(r.realisasi) FROM trn_realisasi_dsh r JOIN mst_kolektor c ON r.kolektor_id = c.kolektor_id JOIN mst_desa d ON c.desa_id = d.desa_id WHERE d.kecamatan_id = k.kecamatan_id AND YEAR(r.tgl_bayar) = ' . $selectedYear . '), 0) as realisasi')
                ->orderBy('k.nm_kecamatan', 'ASC')
                ->get()
                ->getResultArray();
        } elseif ($tab === 'kades') {
            $dataReport = $this->db->table('mst_desa d')
                ->select('d.desa_id, d.kd_desa, d.nm_desa, d.nm_kepala_desa, d.norek_kepala_desa, d.nm_koordinator, d.norek_koordinator, k.nm_kecamatan, k.kd_kecamatan,
                          COALESCE((SELECT t.target FROM trn_target t WHERE t.desa_id = d.desa_id AND t.tahun = ' . $selectedYear . '), 0) as target,
                          COALESCE((SELECT SUM(r.realisasi) FROM trn_realisasi_dsh r JOIN mst_kolektor c ON r.kolektor_id = c.kolektor_id WHERE c.desa_id = d.desa_id AND YEAR(r.tgl_bayar) = ' . $selectedYear . '), 0) as realisasi')
                ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id')
                ->orderBy('k.nm_kecamatan', 'ASC')
                ->orderBy('d.nm_desa', 'ASC')
                ->get()
                ->getResultArray();
        } else {
            // Fetch Collector details (kolektor)
            $collectors = $this->db->table('mst_kolektor c')
                ->select('c.kolektor_id, c.kd_kolektor, c.nm_kolektor, c.norek_kolektor, c.desa_id, c.tahun, d.nm_desa, d.kd_desa, d.kecamatan_id, k.nm_kecamatan, k.kd_kecamatan')
                ->join('mst_desa d', 'c.desa_id = d.desa_id')
                ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id')
                ->where('c.tahun', $selectedYear)
                ->orderBy('k.nm_kecamatan', 'ASC')
                ->orderBy('d.nm_desa', 'ASC')
                ->orderBy('c.nm_kolektor', 'ASC')
                ->get()
                ->getResultArray();

            $desaTargets = $this->db->table('trn_target')
                ->where('tahun', $selectedYear)
                ->get()
                ->getResultArray();
            $targetLookup = [];
            foreach ($desaTargets as $dt) {
                $targetLookup[$dt['desa_id']] = (float)$dt['target'];
            }

            $collectorCounts = $this->db->table('mst_kolektor')
                ->select('desa_id, COUNT(*) as cnt')
                ->where('tahun', $selectedYear)
                ->groupBy('desa_id')
                ->get()
                ->getResultArray();
            $countsLookup = [];
            foreach ($collectorCounts as $cc) {
                $countsLookup[$cc['desa_id']] = (int)$cc['cnt'];
            }

            $realisations = $this->db->table('trn_realisasi_dsh')
                ->select('kolektor_id, SUM(realisasi) as total_realisasi')
                ->where('YEAR(tgl_bayar)', $selectedYear)
                ->groupBy('kolektor_id')
                ->get()
                ->getResultArray();
            $realLookup = [];
            foreach ($realisations as $r) {
                $realLookup[$r['kolektor_id']] = (float)$r['total_realisasi'];
            }

            $dataReport = [];
            foreach ($collectors as $col) {
                $desaId = $col['desa_id'];
                $colId = $col['kolektor_id'];
                
                $cnt = $countsLookup[$desaId] ?? 0;
                $villageTarget = $targetLookup[$desaId] ?? 0.0;
                $target = $cnt > 0 ? $villageTarget / $cnt : 0.0;
                $realisasi = $realLookup[$colId] ?? 0.0;
                
                $col['target'] = $target;
                $col['realisasi'] = $realisasi;
                $dataReport[] = $col;
            }
        }

        // Set headers for Excel download
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");

        ?>
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8">
            <style>
                table {
                    border-collapse: collapse;
                    width: 100%;
                }
                th {
                    background-color: #4F46E5;
                    color: #FFFFFF;
                    font-weight: bold;
                    border: 1px solid #D1D5DB;
                    padding: 8px;
                    text-align: left;
                }
                td {
                    border: 1px solid #D1D5DB;
                    padding: 8px;
                    text-align: left;
                }
                .number {
                    mso-number-format: "\#\,\#\#0";
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
                .header-title {
                    font-size: 16px;
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .header-meta {
                    font-size: 12px;
                    margin-bottom: 20px;
                    color: #4B5563;
                }
            </style>
        </head>
        <body>
            <div class="header-title">LAPORAN ESTIMASI INSENTIF REALISASI PBB</div>
            <div class="header-meta">
                Tahun Target: <?= $selectedYear ?><br>
                Persentase Insentif: <?= number_format($rate, 2, ',', '.') ?>%<br>
                Tanggal Cetak: <?= date('d-m-Y H:i:s') ?><br>
                Kategori Laporan: <?= $tab === 'camat' ? 'Camat (Kecamatan)' : ($tab === 'kades' ? 'Kepala Desa' : 'Kolektor') ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <?php if ($tab === 'camat'): ?>
                            <th>Kode Kecamatan</th>
                            <th>Nama Kecamatan</th>
                            <th>Nama Camat</th>
                            <th>No. Rekening</th>
                        <?php elseif ($tab === 'kades'): ?>
                            <th>Kode Kecamatan</th>
                            <th>Kode Desa</th>
                            <th>Kecamatan</th>
                            <th>Nama Desa</th>
                            <th>Nama Kepala Desa</th>
                            <th>No. Rekening Kades</th>
                            <th>Nama Koordinator</th>
                            <th>No. Rekening Koordinator</th>
                        <?php else: /* tab === 'kolektor' */ ?>
                            <th>Kode Kecamatan</th>
                            <th>Kode Desa</th>
                            <th>Kecamatan</th>
                            <th>Nama Desa</th>
                            <th>Kode Kolektor</th>
                            <th>Nama Kolektor</th>
                            <th>No. Rekening Kolektor</th>
                        <?php endif; ?>
                        
                        <?php if ($tab === 'kades'): ?>
                            <th>Target (Rp)</th>
                            <th>Realisasi (Rp)</th>
                        <?php elseif ($tab === 'kolektor'): ?>
                            <th>Realisasi (Rp)</th>
                        <?php elseif ($tab !== 'camat'): ?>
                            <th>Target (Rp)</th>
                            <th>Realisasi (Rp)</th>
                            <th>% Capaian</th>
                            <th>Estimasi Insentif (Rp)</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $totalTarget = 0;
                    $totalRealisasi = 0;
                    $totalInsentif = 0;
                    foreach ($dataReport as $row): 
                        $target = (float)$row['target'];
                        $realisasi = (float)$row['realisasi'];
                        $persen = $target > 0 ? ($realisasi / $target) * 100 : 0;
                        $insentif = ($realisasi * $rate) / 100;
                        
                        $totalTarget += $target;
                        $totalRealisasi += $realisasi;
                        $totalInsentif += $insentif;
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <?php if ($tab === 'camat'): ?>
                                <td style="mso-number-format:'\@';"><?= esc($row['kd_kecamatan']) ?></td>
                                <td><?= esc($row['nm_kecamatan']) ?></td>
                                <td><?= $row['nm_camat'] ? esc($row['nm_camat']) : '-' ?></td>
                                <td style="mso-number-format:'\@';"><?= $row['norek_camat'] ? esc($row['norek_camat']) : '-' ?></td>
                            <?php elseif ($tab === 'kades'): ?>
                                <td style="mso-number-format:'\@';"><?= esc($row['kd_kecamatan']) ?></td>
                                <td style="mso-number-format:'\@';"><?= esc($row['kd_desa']) ?></td>
                                <td><?= esc($row['nm_kecamatan']) ?></td>
                                <td><?= esc($row['nm_desa']) ?></td>
                                <td><?= $row['nm_kepala_desa'] ? esc($row['nm_kepala_desa']) : '-' ?></td>
                                <td style="mso-number-format:'\@';"><?= $row['norek_kepala_desa'] ? esc($row['norek_kepala_desa']) : '-' ?></td>
                                <td><?= $row['nm_koordinator'] ? esc($row['nm_koordinator']) : '-' ?></td>
                                <td style="mso-number-format:'\@';"><?= $row['norek_koordinator'] ? esc($row['norek_koordinator']) : '-' ?></td>
                            <?php else: /* tab === 'kolektor' */ ?>
                                <td style="mso-number-format:'\@';"><?= esc($row['kd_kecamatan']) ?></td>
                                <td style="mso-number-format:'\@';"><?= esc($row['kd_desa']) ?></td>
                                <td><?= esc($row['nm_kecamatan']) ?></td>
                                <td><?= esc($row['nm_desa']) ?></td>
                                <td style="mso-number-format:'\@';"><?= esc($row['kd_kolektor']) ?></td>
                                <td><?= esc($row['nm_kolektor']) ?></td>
                                <td style="mso-number-format:'\@';"><?= $row['norek_kolektor'] ? esc($row['norek_kolektor']) : '-' ?></td>
                            <?php endif; ?>
                            
                            <?php if ($tab === 'kades'): ?>
                                <td class="number"><?= $target ?></td>
                                <td class="number"><?= $realisasi ?></td>
                            <?php elseif ($tab === 'kolektor'): ?>
                                <td class="number"><?= $realisasi ?></td>
                            <?php elseif ($tab !== 'camat'): ?>
                                <td class="number"><?= $target ?></td>
                                <td class="number"><?= $realisasi ?></td>
                                <td class="text-center"><?= number_format($persen, 2, ',', '.') ?>%</td>
                                <td class="number"><?= $insentif ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Total Row -->
                    <?php if ($tab === 'kades'): ?>
                        <tr style="font-weight: bold; background-color: #F3F4F6;">
                            <td colspan="9" style="text-align: right;">Total:</td>
                            <td class="number"><?= $totalTarget ?></td>
                            <td class="number"><?= $totalRealisasi ?></td>
                        </tr>
                    <?php elseif ($tab === 'kolektor'): ?>
                        <tr style="font-weight: bold; background-color: #F3F4F6;">
                            <td colspan="8" style="text-align: right;">Total:</td>
                            <td class="number"><?= $totalRealisasi ?></td>
                        </tr>
                    <?php elseif ($tab !== 'camat'): ?>
                        <tr style="font-weight: bold; background-color: #F3F4F6;">
                            <td colspan="6" style="text-align: right;">Total:</td>
                            <td class="number"><?= $totalTarget ?></td>
                            <td class="number"><?= $totalRealisasi ?></td>
                            <td class="text-center"><?= number_format($totalTarget > 0 ? ($totalRealisasi / $totalTarget) * 100 : 0, 2, ',', '.') ?>%</td>
                            <td class="number"><?= $totalInsentif ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        exit;
    }
}
