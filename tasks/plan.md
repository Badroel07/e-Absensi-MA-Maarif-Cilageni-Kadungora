# Implementation Plan: GPS Auto-Recovery & Lifecycle Enhancement

## Overview
Mengatasi masalah GPS pada aplikasi e-Absensi MA Ma'arif Cilageni yang mengharuskan pengguna me-refresh halaman atau keluar-masuk browser. Masalah ini diselesaikan dengan mengimplementasikan lifecycle event listeners (`visibilitychange`, `focus`), W3C Permissions API (`permissionStatus.onchange`), penanganan `watchPosition` yang bersih (`clearWatch`), dual-tier fallback (cache/network lalu satelit), serta mekanisme auto-retry saat timeout atau sinyal dingin.

## Architecture Decisions
- **Lifecycle-Driven Re-acquisition (`visibilitychange` & `focus`)**: Menangkap momen saat pengguna kembali ke browser setelah menyalakan GPS di panel notifikasi atau pengaturan perangkat Android/iOS, lalu secara otomatis memicu pelacakan ulang tanpa reload halaman.
- **Permissions API Dynamic Listener**: Memantau perubahan izin dari prompt/denied menjadi granted secara langsung.
- **Active Watch Management (`activeWatchId`)**: Menyimpan ID watchPosition dan selalu memanggil `clearWatch(activeWatchId)` sebelum membuat pelacak baru, mencegah penumpukan listener di background yang dapat menyebabkan browser freeze atau race condition.
- **Resilient Fallback & Exponential/Throttled Auto-Retry**: Jika terjadi timeout (error code 3) atau lokasi belum terdeteksi (error code 2), sistem tidak menyerah melainkan menjadwalkan pengecekan ulang otomatis (3-5 detik) hingga koordinat terkunci.
- **Dual-Tier Scanning pada Guru Scanner**: Meningkatkan scanner QR guru yang sebelumnya hanya memanggil `getCurrentPosition` satu kali menjadi pelacakan reaktif dengan fallback cache yang cepat.

## Task List

### Phase 1: Siswa Dashboard Enhancement
- [ ] Task 1: Siswa Dashboard Geolocation Lifecycle & Auto-Recovery (`resources/views/siswa/dashboard.blade.php`)

### Phase 2: Guru Dashboard & Scan Enhancement
- [ ] Task 2: Guru Dashboard Geolocation Lifecycle & Auto-Recovery (`resources/views/guru/dashboard.blade.php`)
- [ ] Task 3: Guru QR Scanner Continuous Detection & Auto-Recovery (`resources/views/guru/scan.blade.php`)

### Checkpoint: Verification & Testing
- [ ] Feature and unit pest tests pass (`rtk vendor/bin/pest`)
- [ ] Verify seamless dev-simulator integration (`partials/time-simulator.blade.php`)

## Risks and Mitigations
| Risk | Impact | Mitigation |
|------|--------|------------|
| Multi-trigger loop saat tab aktif kembali | Low | Gunakan throttling/debounce (misal jeda minimum 3 detik antar inisiasi manual/otomatis). |
| Baterai HP terkuras oleh `watchPosition` agresif | Medium | Gunakan `maximumAge: 10000`–`30000` dan matikan/reset `watchId` saat tab tidak aktif (`visibilityState === 'hidden'`). |
| Browser lama tidak mendukung Permissions API | Low | Bungkus dalam `if (navigator.permissions && navigator.permissions.query)` dengan fallback try/catch. |

## Open Questions
Tidak ada pertanyaan terbuka. Rencana bersifat non-breaking dan backward-compatible dengan seluruh fitur yang ada (termasuk Time Simulator di local dev).
