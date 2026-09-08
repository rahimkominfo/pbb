<?php

namespace App\Models;

use CodeIgniter\Model;

class KolektorModel extends Model
{
    protected $table            = 'mst_kolektor';
    protected $primaryKey       = 'kolektor_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'desa_id',
        'tahun',
        'kd_kolektor',
        'nm_kolektor',
        'dusun',
        'norek_kolektor',
    ];

    /**
     * Get distinct years available in mst_kolektor.
     */
    public function getAvailableYears(): array
    {
        $years = $this->select('DISTINCT(tahun) as tahun')
            ->orderBy('tahun', 'DESC')
            ->findAll();

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
     * Check whether a collector code already exists for the specified village and year.
     */
    public function isDuplicate(int $desaId, string $kdKolektor, int $tahun, ?int $excludeId = null): bool
    {
        $builder = $this->where([
            'desa_id'     => $desaId,
            'kd_kolektor' => $kdKolektor,
            'tahun'       => $tahun,
        ]);

        if (!empty($excludeId)) {
            $builder->where('kolektor_id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Duplicate / Copy collectors from a source year to a target year.
     * Skips collectors that already have a matching code in the target year.
     *
     * @param int $fromYear
     * @param int $toYear
     * @param int|null $desaId Optional filter for a specific village
     * @return int Number of collectors copied
     */
    public function copyFromYear(int $fromYear, int $toYear, ?int $desaId = null): int
    {
        $builder = $this->where('tahun', $fromYear);
        if (!empty($desaId)) {
            $builder->where('desa_id', $desaId);
        }
        $sourceCollectors = $builder->findAll();

        if (empty($sourceCollectors)) {
            return 0;
        }

        $copiedCount = 0;
        foreach ($sourceCollectors as $col) {
            // Check if already exists in target year
            if (!$this->isDuplicate((int)$col['desa_id'], $col['kd_kolektor'], $toYear)) {
                $this->insert([
                    'desa_id'        => $col['desa_id'],
                    'tahun'          => $toYear,
                    'kd_kolektor'    => $col['kd_kolektor'],
                    'nm_kolektor'    => $col['nm_kolektor'],
                    'dusun'          => $col['dusun'],
                    'norek_kolektor' => $col['norek_kolektor'],
                ]);
                $copiedCount++;
            }
        }

        return $copiedCount;
    }
}
