<?php

namespace App\Models;

use CodeIgniter\Model;

class TargetModel extends Model
{
    protected $table      = 'trn_target';
    protected $primaryKey = 'target_id';

    /**
     * Gets total targets and realizations grouped by kecamatan for a given year.
     * Calculated securely on the server side.
     */
    public function getRealisasiPerKecamatan(int $tahun): array
    {
        // 1. Get targets per kecamatan
        $targets = $this->db->table('trn_target t')
            ->select('d.kecamatan_id, k.nm_kecamatan, SUM(t.target) as total_target')
            ->join('mst_desa d', 't.desa_id = d.desa_id')
            ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id')
            ->where('t.tahun', $tahun)
            ->groupBy('d.kecamatan_id')
            ->get()
            ->getResultArray();

        // 2. Get realisations per kecamatan from trn_realisasi_dsh
        $realisations = $this->db->table('trn_realisasi_dsh r')
            ->select('d.kecamatan_id, SUM(r.realisasi) as total_realisasi')
            ->join('mst_kolektor c', 'r.kolektor_id = c.kolektor_id')
            ->join('mst_desa d', 'c.desa_id = d.desa_id')
            ->where('YEAR(r.tgl_bayar)', $tahun)
            ->groupBy('d.kecamatan_id')
            ->get()
            ->getResultArray();

        // Map realisations by kecamatan_id
        $realMap = [];
        foreach ($realisations as $rel) {
            $realMap[$rel['kecamatan_id']] = (float)$rel['total_realisasi'];
        }

        $processed = [];
        foreach ($targets as $row) {
            $kecId = $row['kecamatan_id'];
            $target = (float)$row['total_target'];
            $realisasi = (float)($realMap[$kecId] ?? 0.0);
            
            // Secure calculation of percentage
            $persentase = $target > 0 ? ($realisasi / $target) * 100 : 0.0;
            
            // Return ONLY the kecamatan name and the calculated percentage
            $processed[] = [
                'nama_kecamatan' => trim($row['nm_kecamatan']),
                'persentase'     => round($persentase, 2)
            ];
        }

        return $processed;
    }

    /**
     * Gets all distinct years available in the database for the filter dropdown.
     */
    public function getAvailableYears(): array
    {
        $years = $this->db->table($this->table)
            ->select('DISTINCT(tahun) as tahun')
            ->orderBy('tahun', 'DESC')
            ->get()
            ->getResultArray();

        // Extract years as simple integers
        return array_map(function ($row) {
            return (int) $row['tahun'];
        }, $years);
    }
}
