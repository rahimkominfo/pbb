<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDesaIdAndNullableKolektorToTrnRealisasiDsh extends Migration
{
    public function up()
    {
        // 1. Add desa_id column if not exists
        if (!$this->db->fieldExists('desa_id', 'trn_realisasi_dsh')) {
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` ADD COLUMN `desa_id` INT NULL AFTER `realisasi_dsh_id`");
            
            // Populate desa_id from mst_kolektor if rows exist
            $this->db->query("UPDATE `trn_realisasi_dsh` r JOIN `mst_kolektor` k ON r.kolektor_id = k.kolektor_id SET r.desa_id = k.desa_id WHERE r.desa_id IS NULL");
            
            // Set NOT NULL
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` MODIFY COLUMN `desa_id` INT NOT NULL");
            
            // Add Index & Foreign Key
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` ADD INDEX `idx_trn_realisasi_dsh_desa` (`desa_id`)");
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` ADD CONSTRAINT `fk_trn_realisasi_dsh_desa` FOREIGN KEY (`desa_id`) REFERENCES `mst_desa` (`desa_id`) ON DELETE RESTRICT ON UPDATE CASCADE");
        }

        // 2. Make kolektor_id NULLABLE and update FK constraint to ON DELETE SET NULL
        // First drop existing constraint
        $fks = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'trn_realisasi_dsh' AND COLUMN_NAME = 'kolektor_id' AND REFERENCED_TABLE_NAME IS NOT NULL")->getResultArray();
        foreach ($fks as $fk) {
            $fkName = $fk['CONSTRAINT_NAME'];
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` DROP FOREIGN KEY `{$fkName}`");
        }

        // Modify kolektor_id to NULL
        $this->db->query("ALTER TABLE `trn_realisasi_dsh` MODIFY COLUMN `kolektor_id` INT NULL");

        // Re-add FK with ON DELETE SET NULL
        $this->db->query("ALTER TABLE `trn_realisasi_dsh` ADD CONSTRAINT `fk_trn_realisasi_dsh_kolektor` FOREIGN KEY (`kolektor_id`) REFERENCES `mst_kolektor` (`kolektor_id`) ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down()
    {
        // 1. Revert FK on kolektor_id to ON DELETE RESTRICT and NOT NULL
        $fks = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'trn_realisasi_dsh' AND COLUMN_NAME = 'kolektor_id' AND REFERENCED_TABLE_NAME IS NOT NULL")->getResultArray();
        foreach ($fks as $fk) {
            $fkName = $fk['CONSTRAINT_NAME'];
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` DROP FOREIGN KEY `{$fkName}`");
        }
        $this->db->query("ALTER TABLE `trn_realisasi_dsh` MODIFY COLUMN `kolektor_id` INT NOT NULL");
        $this->db->query("ALTER TABLE `trn_realisasi_dsh` ADD CONSTRAINT `fk_trn_realisasi_dsh_kolektor` FOREIGN KEY (`kolektor_id`) REFERENCES `mst_kolektor` (`kolektor_id`) ON DELETE RESTRICT ON UPDATE CASCADE");

        // 2. Drop desa_id FK and column
        if ($this->db->fieldExists('desa_id', 'trn_realisasi_dsh')) {
            $desaFks = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'trn_realisasi_dsh' AND COLUMN_NAME = 'desa_id' AND REFERENCED_TABLE_NAME IS NOT NULL")->getResultArray();
            foreach ($desaFks as $fk) {
                $fkName = $fk['CONSTRAINT_NAME'];
                $this->db->query("ALTER TABLE `trn_realisasi_dsh` DROP FOREIGN KEY `{$fkName}`");
            }
            $idx = $this->db->query("SHOW INDEX FROM `trn_realisasi_dsh` WHERE Key_name = 'idx_trn_realisasi_dsh_desa'")->getResultArray();
            if (!empty($idx)) {
                $this->db->query("ALTER TABLE `trn_realisasi_dsh` DROP INDEX `idx_trn_realisasi_dsh_desa`");
            }
            $this->db->query("ALTER TABLE `trn_realisasi_dsh` DROP COLUMN `desa_id`");
        }
    }
}
