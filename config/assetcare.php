<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GPCH Asset Care
    |--------------------------------------------------------------------------
    | Pengelolaan aset & pemeliharaan Grand Pasundan Convention Hotel.
    | Nilai di bawah adalah konfigurasi awal usulan (lihat spesifikasi bagian 9),
    | harus disahkan GM & Engineering sebelum produksi.
    */
    'brand_name' => env('ASSETCARE_BRAND_NAME', 'GPCH Asset Care'),
    'brand_tagline' => env('ASSETCARE_BRAND_TAGLINE', 'Pengelolaan Aset & Pemeliharaan Grand Pasundan'),
    'property_code' => env('ASSETCARE_PROPERTY_CODE', 'GPCH'),
    'audit_enabled' => (bool) env('ASSETCARE_AUDIT_ENABLED', true),
    'display_timezone' => env('ASSETCARE_DISPLAY_TIMEZONE', 'Asia/Jakarta'),
    'notify_mail' => (bool) env('ASSETCARE_NOTIFY_MAIL', false),
    'seed_demo' => (bool) env('ASSETCARE_SEED_DEMO', false),

    // Domain resolver label QR. Pakai domain milik GPCH agar label tetap berlaku saat hosting pindah.
    'qr_base_url' => rtrim(env('ASSETCARE_QR_BASE_URL') ?: env('APP_URL', 'http://localhost'), '/'),
    // Host yang diterima pembaca QR di aplikasi (selain host qr_base_url). Pisahkan dengan koma.
    'qr_extra_hosts' => array_filter(array_map('trim', explode(',', (string) env('ASSETCARE_QR_EXTRA_HOSTS', '')))),

    // Wajib 2FA untuk peran berikut bila ASSETCARE_MFA_REQUIRED=true (administrator & approver)
    'mfa_required' => (bool) env('ASSETCARE_MFA_REQUIRED', false),
    'mfa_roles' => ['super-admin', 'it-admin', 'owner', 'gm', 'chief-engineering', 'finance'],

    // Kalender kerja untuk P3/P4 (P1/P2 memakai kalender 24 jam)
    'business_calendar' => [
        'days' => [1, 2, 3, 4, 5, 6],      // Senin..Sabtu (ISO: 1 = Senin)
        'start' => '08:00',
        'end' => '17:00',
        'holidays' => array_filter(array_map('trim', explode(',', (string) env('ASSETCARE_HOLIDAYS', '')))), // YYYY-MM-DD
    ],

    // Eskalasi SLA: persentase target untuk peringatan pertama
    'sla_warning_pct' => 80,

    // Jendela kegagalan berulang (hari)
    'repeat_failure_days' => 30,

    // Profil media label (mm). Kolom/baris untuk A4; roll = satu label per halaman.
    'label_profiles' => [
        'a4-50x30' => [
            'name' => 'A4 · 50 × 30 mm (3 × 9)', 'type' => 'a4', 'page_w' => 210, 'page_h' => 297,
            'label_w' => 50, 'label_h' => 30, 'cols' => 3, 'rows' => 9,
            'margin_top' => 13.5, 'margin_left' => 25, 'gap_x' => 5, 'gap_y' => 0, 'qr_mm' => 23,
        ],
        'a4-75x50' => [
            'name' => 'A4 · 75 × 50 mm (2 × 5)', 'type' => 'a4', 'page_w' => 210, 'page_h' => 297,
            'label_w' => 75, 'label_h' => 50, 'cols' => 2, 'rows' => 5,
            'margin_top' => 23.5, 'margin_left' => 25, 'gap_x' => 10, 'gap_y' => 0, 'qr_mm' => 25,
        ],
        'roll-50x30' => [
            'name' => 'Printer label roll · 50 × 30 mm', 'type' => 'roll', 'page_w' => 50, 'page_h' => 30,
            'label_w' => 50, 'label_h' => 30, 'cols' => 1, 'rows' => 1,
            'margin_top' => 0, 'margin_left' => 0, 'gap_x' => 0, 'gap_y' => 0, 'qr_mm' => 23,
        ],
        'roll-75x50' => [
            'name' => 'Printer label roll · 75 × 50 mm', 'type' => 'roll', 'page_w' => 75, 'page_h' => 50,
            'label_w' => 75, 'label_h' => 50, 'cols' => 1, 'rows' => 1,
            'margin_top' => 0, 'margin_left' => 0, 'gap_x' => 0, 'gap_y' => 0, 'qr_mm' => 25,
        ],
    ],
    'label_template_version' => 'LBL-1.0',
];
