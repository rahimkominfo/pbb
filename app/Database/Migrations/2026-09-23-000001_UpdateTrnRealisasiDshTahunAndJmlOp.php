<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTrnRealisasiDshTahunAndJmlOp extends Migration
{
    public function up()
    {
        // 1. Rename column 'nop' to 'jml_op'
        if ($this->db->fieldExists('nop', 'trn_realisasi_dsh')) {
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` CHANGE COLUMN `nop` `jml_op` INT NOT NULL DEFAULT 0");
        }

        // 2. Add 'tahun' column if not exists
        if (!$this->db->fieldExists('tahun', 'trn_realisasi_dsh')) {
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` ADD COLUMN `tahun` INT(4) NOT NULL DEFAULT 2026 AFTER `kolektor_id`");
            $this->db->query("UPDATE `trn_realisasi_dsh` SET `tahun` = YEAR(`tgl_bayar`) WHERE `tgl_bayar` IS NOT NULL");
        }

        // 3. Add index on tahun for fast filtering and joins
        $indexes = $this->db->query("SHOW INDEX FROM `trn_realisasi_dsh` WHERE Key_name = 'idx_trn_realisasi_dsh_tahun'")->getResultArray();
        if (empty($indexes)) {
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` ADD INDEX `idx_trn_realisasi_dsh_tahun` (`tahun`)");
        }
    }

    public function down()
    {
        // 1. Revert 'jml_op' back to 'nop'
        if ($this->db->fieldExists('jml_op', 'trn_realisasi_dsh')) {
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` CHANGE COLUMN `jml_op` `nop` INT NOT NULL DEFAULT 0");
        }

        // 2. Drop index and 'tahun' column
        if ($this->db->fieldExists('tahun', 'trn_realisasi_dsh')) {
            $indexes = $this->db->query("SHOW INDEX FROM `trn_realisasi_dsh` WHERE Key_name = 'idx_trn_realisasi_dsh_tahun'")->getResultArray();
            if (!empty($indexes)) {
                $this->db->query("ALTER TABLE `trn_realisasi_dsh` DROP INDEX `idx_trn_realisasi_dsh_tahun`");
            }
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` DROP COLUMN `tahun`");
        }
    }
}
