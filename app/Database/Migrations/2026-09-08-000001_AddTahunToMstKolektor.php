<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTahunToMstKolektor extends Migration
{
    public function up()
    {
        // 1. Add tahun column if it doesn't exist
        if (!$this->db->fieldExists('tahun', 'mst_kolektor')) {
            $fields = [
                'tahun' => [
                    'type'       => 'INT',
                    'constraint' => 4,
                    'null'       => false,
                    'default'    => 2026,
                    'after'      => 'desa_id',
                ],
            ];
            $this->forge->addColumn('mst_kolektor', $fields);
        }

        // 2. Add new unique constraint FIRST so desa_id is still indexed for foreign key constraint fk_mst_kolektor_desa
        $newIndexes = $this->db->query("SHOW INDEX FROM mst_kolektor WHERE Key_name = 'ux_mst_kolektor_desa_kd_tahun'")->getResultArray();
        if (empty($newIndexes)) {
            $this->db->query("ALTER TABLE mst_kolektor ADD UNIQUE KEY ux_mst_kolektor_desa_kd_tahun (desa_id, kd_kolektor, tahun)");
        }

        // 3. Drop old unique constraint on (desa_id, kd_kolektor)
        $oldIndexes = $this->db->query("SHOW INDEX FROM mst_kolektor WHERE Key_name = 'ux_mst_kolektor_desa_kd_kolektor'")->getResultArray();
        if (!empty($oldIndexes)) {
            $this->db->query("ALTER TABLE mst_kolektor DROP INDEX ux_mst_kolektor_desa_kd_kolektor");
        }

        // 4. Add index on tahun for fast filtering
        $tahunIndexes = $this->db->query("SHOW INDEX FROM mst_kolektor WHERE Key_name = 'idx_mst_kolektor_tahun'")->getResultArray();
        if (empty($tahunIndexes)) {
            $this->db->query("ALTER TABLE mst_kolektor ADD INDEX idx_mst_kolektor_tahun (tahun)");
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('tahun', 'mst_kolektor')) {
            $this->db->query("ALTER TABLE mst_kolektor ADD UNIQUE KEY ux_mst_kolektor_desa_kd_kolektor (desa_id, kd_kolektor)");
            $this->db->query("ALTER TABLE mst_kolektor DROP INDEX IF EXISTS ux_mst_kolektor_desa_kd_tahun");
            $this->db->query("ALTER TABLE mst_kolektor DROP INDEX IF EXISTS idx_mst_kolektor_tahun");
            $this->forge->dropColumn('mst_kolektor', 'tahun');
        }
    }
}
