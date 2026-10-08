---
name: Blezt.id
description: Sistem keuangan perusahaan — dashboard internal.
colors:
  primary: "#2f6bff"
  background: "#0b0d14"
  surface: "#0f1420"
  surfaceAlt: "#121726"
  text: "#f4f5f8"
  textMuted: "#9aa0b0"
  accent: "#ff3d8a"
typography:
  display:
    fontFamily: "Space Grotesk"
    fontSize: "clamp(2.75rem, 7vw, 5rem)"
    fontWeight: 700
    lineHeight: 0.95
    letterSpacing: "-0.04em"
  body:
    fontFamily: "Albert Sans"
    fontSize: "1rem"
    lineHeight: 1.55
  label:
    fontFamily: "Albert Sans"
    fontSize: "0.75rem"
    fontWeight: 600
    letterSpacing: "0.14em"
    textTransform: "uppercase"
rounded:
  sm: 16px
  md: 24px
  full: 999px
spacing:
  sm: 16px
  md: 40px
  lg: 88px
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    rounded: 999px
    padding: "12px 24px"
imagery:
  filter: none
---

# Blezt.id — Sistem Keuangan Perusahaan

Dashboard keuangan internal untuk tim keuangan Blezt.id. Setiap halaman adalah layar: data dan metrik yang padat namun terbaca, di atas lapangan gelap dengan beberapa elemen kaca yang mengambang.

## Overview

Light through glass. Lapangan tinta gelap nyaris-hitam, diterangi satu-dua sumber cahaya berwarna intens — biru elektrik dan magenta — sebagai glow besar yang diburamkan di belakang konten. Beberapa elemen kaca fisik tipis (navigasi kapsul, tombol utama, satu kartu hero/ringkasan) mengambang di atasnya dengan blur nyata, tepian refraksi terang, dan kilau yang bergerak. Selebihnya datar di atas lapangan. Kaca adalah krom di atas konten yang kaya — bukan pembungkus untuk setiap seksi. Desain ini menekankan angka: setiap metrik tampil besar, terbaca, dan serius.

## Colors

Lapangan hampir hitam `#0b0d14`; panel `#0f1420` dan `#121726` untuk kartu dan seksi. Teks putih `#f4f5f8` pada lapangan, `#9aa0b0` untuk teks sekunder. Dua sumber cahaya jenuh — biru elektrik `#2f6bff` (aksen utama) dan magenta `#ff3d8a` — hanya muncul di dalam glow gradien dan pada aksen halus, tidak pernah sebagai isian datar. Gunakan aksen magenta sangat hemat (mis. satu sorotan atau tanda). Tidak ada abu-abu pada kaca, tidak ada hitam pekat sebagai panel.

## Typography

Judul: Space Grotesk 700, `clamp(2.75rem, 7vw, 5rem)`, `-0.04em`, `line-height: 0.95`, diletakkan langsung di atas lapangan, tidak di dalam panel. Isi: Albert Sans `1rem/1.55` putih pada 75% opacity. Label: uppercase kecil Albert Sans dengan `letter-spacing: 0.14em`. Angka (KPI, statistik) memakai Space Grotesk dengan `font-variant-numeric: tabular-nums` agar kolom sejajar.

## Layout

**Concept: Sidebar site.** Halaman dibagi dua kolom (`grid-template-columns: 1fr 2fr`, `gap: 0`). Kolom kiri (sidebar) tetap (`position: sticky; top: 0; height: 100dvh`) berisi: brand, navigasi kaca berbentuk pil, dan satu detail hidup (saldo tunai) di bagian bawah. Konten berada di kolom kanan (`layout: default`, bukan kontainer scroll sendiri) dan menggulir di sebelahnya. Header dan footer hidup di dalam sidebar, bukan di atas/bawah konten. Tautan sidebar menggulir halus (`scroll-behavior: smooth`, nonaktif saat reduced-motion) dan tautan seksi aktif ditandai via IntersectionObserver. Di mobile sidebar menjadi bar atas dan detail hidup pindah ke footer.

Konten tiap halaman mengikuti satu pola: baris judul (label + headline besar), baris kartu KPI, lalu tabel data. Tiap halaman Ikhtisar / Pendapatan / Pengeluaran / Laporan berbagi sidebar yang sama. `contentSize` 1100px, `wideSize` 1280px. Seksi dipisahkan oleh jarak dan tipografi, bukan kartu di mana-mana.

## Elevation & Depth

Lapangan datar. Kedalaman datang dari sumber cahaya: div bulat `border-radius: 50%` dengan `filter: blur(90px)` posisi absolut di belakang konten, diklip oleh seksi (`overflow: hidden`). Elemen kaca — paling banyak tiga per halaman — memakai `background: rgb(255 255 255 / 0.1); backdrop-filter: blur(20px) saturate(1.8) brightness(1.1); border: 1px solid transparent; background-clip: padding-box`, border gradien terang di kiri-atas, `box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.4), 0 20px 60px rgb(0 0 0 / 0.35)`. Setiap elemen kaca duduk di tempat sumber cahaya menembusnya.

## Shapes

Kapsul dan radius besar: `999px` pada navigasi dan tombol, 24px pada kartu hero. Glow cahaya satu-satunya bentuk lembut. Seksi tak punya border atau kartu — hanya jarak dan tipografi.

## Components

Tombol utama: latar biru elektrik, teks putih, pil `999px`, `padding: 12px 24px`. Navigasi: kapsul kaca tipis mengambang di atas. Kartu data: panel `#0f1420` dengan border `1px solid rgb(255 255 255 / 0.08)` dan sudut 16px.

## Do's and Don'ts

Do: biarkan angka jadi bintang; pakai kaca sangat hemat (maks 3 elemen); sorot satu elemen per layar dengan kilau yang bergerak; jaga tiap kartu data terbaca. Don't: kaca di setiap seksi atau kartu; lebih dari tiga elemen kaca; Inter atau Manrope; teks abu-abu di atas kaca; bayangan keras; elemen kaca tanpa hal terang di belakangnya; gradien ungu sebagai isian datar.

## Imagery

Minimal. Konten berbasis data dan tipografi; praktis tidak memakai foto. Bila ada foto, full-bleed di bawah scrim gradien gelap, atau render produk di atas lapangan; foto tidak pernah di dalam kaca. Ikon garis tipis dalam tinta.

## Motion

Sumber cahaya hanyut perlahan (loop translate 20–30s) dan bergeser beberapa piksel mengikuti pointer lewat skrip kecil (nonaktif saat reduced-motion), sehingga refraksi berubah mengikuti gerakan. Kilau sapuan melintasi elemen kaca saat hover (gradien bergerak pada `::before`, 600ms). Tidak ada yang memantul.

## Voice

Serius, jelas, dan datar — bahasa Indonesia netral untuk dashboard keuangan. Singkat dan tepat: label pendek ("Pendapatan", "Arus kas", "Laba bersih"), tanpa hiasan bahasa. Angka didahulukan; kata-kata mendukung.
