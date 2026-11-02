<?php

namespace App\Support;

/**
 * Katalog izin & peran Asset Care (spesifikasi bagian 3). Deny by default:
 * setiap layar & aksi memeriksa izin; harga/nilai buku hanya untuk asset.view_financial.
 */
class PermissionCatalog
{
    public const PERMISSIONS = [
        // Umum
        'dashboard.view' => ['Beranda manajerial (KPI, risiko, biaya)', 'Umum'],
        'report.view' => ['Melihat laporan', 'Umum'],
        'report.export' => ['Ekspor laporan (CSV)', 'Umum'],
        // Aset
        'asset.view' => ['Melihat register & kartu aset', 'Aset'],
        'asset.edit' => ['Membuat & mengubah data aset', 'Aset'],
        'asset.verify' => ['Verifikasi fisik aset', 'Aset'],
        'asset.activate' => ['Mengaktifkan / menghentikan aset (siklus hidup)', 'Aset'],
        'asset.view_financial' => ['Melihat harga & nilai aset', 'Aset'],
        'asset.edit_financial' => ['Mengubah data finansial aset', 'Aset'],
        'template.manage' => ['Menyusun template aset tipe kamar', 'Aset'],
        'template.publish' => ['Memeriksa & menerbitkan template', 'Aset'],
        'generation.run' => ['Pembuatan aset massal untuk kamar', 'Aset'],
        'label.print' => ['Membuat batch & cetak label QR', 'Aset'],
        'label.rotate' => ['Rotasi token label (nonaktifkan label lama)', 'Aset'],
        'label.install' => ['Konfirmasi pemasangan label', 'Aset'],
        'stocktake.count' => ['Stock opname: mencatat hasil hitung fisik', 'Aset'],
        'stocktake.manage' => ['Stock opname: membuat, menutup & menerapkan hasil', 'Aset'],
        'disposal.request' => ['Mengusulkan penghapusan aset', 'Aset'],
        'disposal.approve' => ['Menyetujui / menolak penghapusan aset', 'Aset'],
        'survey.run' => ['Pendataan aset lapangan (checklist kamar, tempel & hubungkan label)', 'Aset'],
        'transfer.request' => ['Mengajukan mutasi aset', 'Aset'],
        'transfer.approve' => ['Menyetujui mutasi aset', 'Aset'],
        'transfer.receive' => ['Mengirim / menerima mutasi aset', 'Aset'],
        // Pemeliharaan
        'request.create' => ['Membuat laporan kerusakan', 'Pemeliharaan'],
        'request.view_all' => ['Melihat semua laporan (lintas departemen)', 'Pemeliharaan'],
        'request.triage' => ['Triase laporan & menetapkan prioritas', 'Pemeliharaan'],
        'wo.view' => ['Melihat work order', 'Pemeliharaan'],
        'wo.create' => ['Membuat work order', 'Pemeliharaan'],
        'wo.assign' => ['Menugaskan & menjadwalkan work order', 'Pemeliharaan'],
        'wo.execute' => ['Mengerjakan work order (teknisi)', 'Pemeliharaan'],
        'wo.verify' => ['Memverifikasi hasil pekerjaan', 'Pemeliharaan'],
        'wo.approve' => ['Menyetujui biaya / lingkup work order', 'Pemeliharaan'],
        'wo.close' => ['Menutup & membuka ulang work order', 'Pemeliharaan'],
        'wo.cost_finalize' => ['Finalisasi biaya (Finance)', 'Pemeliharaan'],
        'room.block_confirm' => ['Konfirmasi blokir kamar (Front Office)', 'Pemeliharaan'],
        // Pengaturan
        'reference.manage' => ['Lokasi, kategori, departemen, tipe kamar, vendor', 'Pengaturan'],
        'settings.manage' => ['SLA & batas persetujuan', 'Pengaturan'],
        'user.manage' => ['Pengguna & penugasan peran', 'Pengaturan'],
        'role.manage' => ['Peran & izin', 'Pengaturan'],
        'audit.view' => ['Log audit', 'Pengaturan'],
    ];

    /** slug => [nama, deskripsi, izin[]] ('*' = semua izin) */
    public const ROLES = [
        'super-admin' => ['Super Admin', 'Konsultan / vendor aplikasi — semua izin', ['*']],
        'owner' => ['Owner', 'Melihat seluruh kinerja & biaya; memutuskan investasi/penghapusan', ['dashboard.view', 'report.view', 'report.export', 'asset.view', 'asset.view_financial', 'wo.view', 'request.view_all', 'audit.view', 'disposal.approve']],
        'owner-rep' => ['Owner Representative', 'Memantau risiko aset & anggaran; mengkaji prioritas & usulan penggantian', ['dashboard.view', 'report.view', 'report.export', 'asset.view', 'asset.view_financial', 'wo.view', 'request.view_all', 'audit.view']],
        'gm' => ['General Manager', 'Mengawasi operasi, backlog, downtime, SLA & persetujuan sesuai batas nilai', ['dashboard.view', 'report.view', 'report.export', 'asset.view', 'asset.view_financial', 'request.create', 'request.view_all', 'wo.view', 'wo.approve', 'wo.close', 'transfer.approve', 'template.publish', 'audit.view', 'disposal.approve', 'disposal.request']],
        'chief-engineering' => ['Chief Engineering', 'Triase, prioritas, penjadwalan teknisi & verifikasi pekerjaan', ['dashboard.view', 'report.view', 'report.export', 'asset.view', 'asset.edit', 'asset.verify', 'request.create', 'request.view_all', 'request.triage', 'wo.view', 'wo.create', 'wo.assign', 'wo.execute', 'wo.verify', 'wo.approve', 'wo.close', 'label.install', 'transfer.request', 'transfer.receive', 'template.manage', 'survey.run', 'stocktake.count', 'disposal.request']],
        'technician' => ['Teknisi', 'Melihat tugas & aset relevan; mencatat diagnosis, waktu, material & bukti', ['asset.view', 'request.create', 'wo.view', 'wo.execute', 'label.install', 'transfer.receive']],
        'hod' => ['HOD', 'Melapor, melihat laporan departemennya, mengonfirmasi layanan & kondisi aset', ['asset.view', 'request.create', 'wo.view', 'report.view', 'transfer.request', 'transfer.approve']],
        'surveyor' => ['Petugas Pendataan', 'Keliling kamar/area: tempel label QR, cek isi kamar, catat kondisi & foto', ['asset.view', 'asset.verify', 'label.install', 'survey.run', 'request.create', 'stocktake.count']],
        'reporter' => ['Pelapor (staf)', 'Membuat laporan kerusakan & melihat laporan sendiri', ['asset.view', 'request.create']],
        'front-office' => ['Front Office', 'Melapor & mengonfirmasi blokir kamar terkait perbaikan', ['asset.view', 'request.create', 'room.block_confirm', 'wo.view']],
        'asset-controller' => ['Asset Controller', 'Memelihara register, label & inventarisasi; mengusulkan mutasi & koreksi', ['dashboard.view', 'report.view', 'report.export', 'asset.view', 'asset.edit', 'asset.verify', 'asset.activate', 'template.manage', 'generation.run', 'label.print', 'label.rotate', 'label.install', 'survey.run', 'transfer.request', 'transfer.receive', 'reference.manage', 'request.create', 'stocktake.count', 'stocktake.manage', 'disposal.request']],
        'finance' => ['Finance / Chief Accountant', 'Data finansial, validasi biaya, anggaran & rekonsiliasi', ['dashboard.view', 'report.view', 'report.export', 'asset.view', 'asset.view_financial', 'asset.edit_financial', 'wo.view', 'wo.approve', 'wo.cost_finalize', 'request.view_all', 'transfer.approve', 'stocktake.manage', 'disposal.request', 'disposal.approve']],
        'storekeeper' => ['Storekeeper', 'Mengelola stok engineering (rilis B)', ['asset.view', 'wo.view', 'request.create']],
        'purchasing' => ['Purchasing', 'Vendor & referensi pembelian', ['asset.view', 'wo.view', 'request.create', 'reference.manage']],
        'it-admin' => ['IT Administrator', 'Konfigurasi teknis & akun; tidak otomatis berwenang menyetujui biaya', ['asset.view', 'request.create', 'user.manage', 'role.manage', 'reference.manage', 'settings.manage', 'audit.view']],
        'guest' => ['Tamu (demo)', 'Akun bersama untuk menjelajah data demo: hanya melihat, tidak bisa mengubah data', ['dashboard.view', 'report.view', 'asset.view', 'asset.view_financial', 'request.view_all', 'wo.view']],
        'auditor' => ['Auditor', 'Membaca data & jejak audit dalam lingkup yang ditetapkan', ['report.view', 'report.export', 'asset.view', 'asset.view_financial', 'wo.view', 'request.view_all', 'audit.view']],
    ];

    /** Tambah/perbarui izin & peran sesuai katalog (aman diulang; dipakai seeder dan perbaikan mode tamu). */
    public static function sync(): void
    {
        foreach (static::PERMISSIONS as $slug => [$name, $group]) {
            \App\Models\Permission::updateOrCreate(['slug' => $slug], ['name' => $name, 'group' => $group]);
        }
        $all = \App\Models\Permission::pluck('id', 'slug');
        foreach (static::ROLES as $slug => [$name, $desc, $perms]) {
            $role = \App\Models\Role::updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $desc]);
            $role->permissions()->sync($perms === ['*'] ? $all->values() : $all->only($perms)->values());
        }
    }
}
