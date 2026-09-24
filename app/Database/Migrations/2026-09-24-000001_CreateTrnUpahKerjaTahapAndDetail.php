<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrnUpahKerjaTahapAndDetail extends Migration
{
    public function up()
    {
        // 1. Table trn_upah_kerja_tahap (Mencatat tahap/batch pembayaran berdasarkan rentang tanggal dinamis)
        $this->forge->addField([
            'tahap_id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'tahun' => [
                'type'       => 'INT',
                'constraint' => 4,
                'default'    => 2026,
            ],
            'nama_tahap' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
            ],
            'tgl_awal' => [
                'type' => 'DATE',
            ],
            'tgl_akhir' => [
                'type' => 'DATE',
            ],
            'is_final' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'total_kolektor' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'total_op_cair' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'total_op_tunda' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'created_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('tahap_id', true);
        $this->forge->addKey(['tahun', 'tgl_awal', 'tgl_akhir']);
        $this->forge->createTable('trn_upah_kerja_tahap', true);

        // 2. Table trn_upah_kerja_detail (Menyimpan posisi carry-over, realisasi baru, dan kelayakan per kolektor)
        $this->forge->addField([
            'detail_id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'tahap_id' => [
                'type' => 'INT',
            ],
            'kolektor_id' => [
                'type' => 'INT',
            ],
            'op_carry_masuk' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'op_periode_ini' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'op_total_akumulasi' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'status_bayar' => [
                'type'       => 'ENUM',
                'constraint' => ['DIBAYARKAN', 'DITUNDA'],
                'default'    => 'DITUNDA',
            ],
            'op_dibayarkan' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'op_carry_keluar' => [
                'type'    => 'INT',
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('detail_id', true);
        $this->forge->addUniqueKey(['tahap_id', 'kolektor_id']);
        $this->forge->addForeignKey('tahap_id', 'trn_upah_kerja_tahap', 'tahap_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('kolektor_id', 'mst_kolektor', 'kolektor_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('trn_upah_kerja_detail', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_upah_kerja_detail', true);
        $this->forge->dropTable('trn_upah_kerja_tahap', true);
    }
}
