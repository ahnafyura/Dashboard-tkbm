<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>SENSE-TKBM — Port Safety Command Center</title>

    {{-- Fonts: Rajdhani (display) + JetBrains Mono (data) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />

    {{-- Tailwind CDN (replace with compiled build in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Rajdhani', 'sans-serif'],
                        mono:    ['JetBrains Mono', 'monospace'],
                        body:    ['Inter', 'sans-serif'],
                    },
                    colors: {
                        // SENSE-TKBM brand palette
                        harbor: {
                            50:  '#f0fdf9',
                            100: '#ccfbef',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            900: '#064e3b',
                        },
                        slate: {
                            850: '#0f1623',
                            900: '#0b1120',
                            950: '#060d18',
                        },
                        danger: '#ef4444',
                        warn:   '#f59e0b',
                        safe:   '#10b981',
                    },
                    animation: {
                        'pulse-slow':  'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-in':    'slideIn 0.3s ease-out',
                        'fade-in':     'fadeIn 0.5s ease-out',
                        'scan-line':   'scanLine 4s linear infinite',
                    },
                    keyframes: {
                        slideIn:  { '0%': { opacity: 0, transform: 'translateX(1rem)' }, '100%': { opacity: 1, transform: 'translateX(0)' } },
                        fadeIn:   { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
                        scanLine: { '0%': { top: '0%' }, '100%': { top: '100%' } },
                    },
                    boxShadow: {
                        'harbor-glow': '0 0 20px rgba(16, 185, 129, 0.2)',
                        'danger-glow': '0 0 20px rgba(239, 68, 68, 0.25)',
                        'card':        '0 4px 24px rgba(0,0,0,0.4)',
                    },
                },
            },
        };
    </script>

    {{-- Alpine.js (Pinned CDN) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        /* ── Global resets ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4 { font-family: 'Rajdhani', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* ── Custom scrollbar ──────────────────────────────── */
        ::-webkit-scrollbar       { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: #0b1120; }
        ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 2px; }

        /* ── Glassmorphism card ────────────────────────────── */
        .glass-card {
            background: rgba(15, 22, 35, 0.85);
            border: 1px solid rgba(16, 185, 129, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* ── Scan-line overlay (map) ───────────────────────── */
        .scan-overlay { position: relative; overflow: hidden; }
        .scan-overlay::after {
            content: '';
            position: absolute;
            left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, rgba(16,185,129,0.6), transparent);
            animation: scanLine 4s linear infinite;
            pointer-events: none;
            z-index: 800;
        }

        /* ── HUD corner brackets ───────────────────────────── */
        .hud-bracket {
            position: relative;
        }
        .hud-bracket::before, .hud-bracket::after {
            content: '';
            position: absolute;
            width: 12px; height: 12px;
            border-color: #10b981;
            border-style: solid;
        }
        .hud-bracket::before { top: 0; left: 0; border-width: 2px 0 0 2px; }
        .hud-bracket::after  { bottom: 0; right: 0; border-width: 0 2px 2px 0; }

        /* ── Grid noise texture ────────────────────────────── */
        .noise-bg {
            background-image:
                url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
        }

        /* ── Leaflet dark map overrides ────────────────────── */
        .leaflet-tile { filter: brightness(0.5) hue-rotate(155deg) saturate(1.5); }
        .leaflet-container { background: #060d18; }

        /* ── Status badge ──────────────────────────────────── */
        .badge-fit      { background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.4); }
        .badge-warning  { background: rgba(245,158,11,0.15);  color: #f59e0b; border: 1px solid rgba(245,158,11,0.4); }
        .badge-critical { background: rgba(239,68,68,0.15);   color: #ef4444; border: 1px solid rgba(239,68,68,0.4); }
        .badge-inactive { background: rgba(100,116,139,0.15); color: #94a3b8; border: 1px solid rgba(100,116,139,0.3); }

        /* ── Progress bar ──────────────────────────────────── */
        .cap-bar-fill {
            height: 6px;
            border-radius: 3px;
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ── Alert item animation ──────────────────────────── */
        .alert-item { animation: slideIn 0.35s ease-out; }

        /* ── Blink for critical ────────────────────────────── */
        @keyframes criticalBlink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }
        .blink-critical { animation: criticalBlink 1.2s ease-in-out infinite; }

        /* ── Grid dots background ──────────────────────────── */
        .dot-grid {
            background-image: radial-gradient(circle, rgba(16,185,129,0.12) 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
</head>

{{-- ═══════════════════════════════════════════════════════════════════
     ROOT: Alpine.js App
     ═══════════════════════════════════════════════════════════════════ --}}
<body
    class="bg-slate-950 text-gray-100 min-h-screen noise-bg"
    x-data="commandCenter()"
    x-init="init()"
>

{{-- ── TOPBAR ────────────────────────────────────────────────────────── --}}
<header class="fixed top-0 inset-x-0 z-50 border-b border-harbor-500/20 bg-slate-950/90 backdrop-blur-md">
    <div class="flex items-center justify-between px-5 h-14">

        {{-- Brand --}}
        <div class="flex items-center gap-3">
            <div class="relative w-8 h-8">
                <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <polygon points="16,2 30,26 2,26" stroke="#10b981" stroke-width="2" fill="none"/>
                    <circle cx="16" cy="19" r="4" fill="#10b981" opacity="0.8"/>
                </svg>
                <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-harbor-400 rounded-full animate-pulse-slow"></span>
            </div>
            <div>
                <h1 class="font-display text-xl font-700 tracking-widest text-harbor-400 leading-none">SENSE-TKBM</h1>
                <p class="text-[10px] text-gray-500 tracking-[0.2em] uppercase leading-none mt-0.5">Port Safety Command Center</p>
            </div>
        </div>

        {{-- Center: Live indicator --}}
        <div class="hidden md:flex items-center gap-6">
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <span class="w-1.5 h-1.5 bg-harbor-400 rounded-full animate-pulse"></span>
                <span class="font-mono">LIVE FEED</span>
            </div>
            <div class="font-mono text-xs text-gray-500" x-text="currentTime"></div>
            <div class="text-xs text-gray-400">
                Pelabuhan Tanjung Perak — Surabaya
            </div>
        </div>

        {{-- Right: KPI pills --}}
        <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-harbor-500/10 border border-harbor-500/20">
                <span class="text-harbor-400 text-xs font-mono" x-text="kpi.active_workers + ' / 10 Aktif'"></span>
            </div>
            <div
                class="flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-mono transition-all"
                :class="kpi.critical_alerts > 0 ? 'bg-red-500/10 border-red-500/30 text-red-400 blink-critical' : 'bg-slate-800 border-slate-700 text-gray-400'"
            >
                <span x-text="kpi.critical_alerts + ' Critical'"></span>
            </div>
        </div>
    </div>
</header>

{{-- ── MAIN LAYOUT ───────────────────────────────────────────────────── --}}
<main class="pt-14 min-h-screen">

    {{-- ── KPI CARDS ──────────────────────────────────────────────────── --}}
    <section class="px-4 pt-5 pb-0">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

            {{-- Active Workers --}}
            <div class="glass-card rounded-xl p-4 hud-bracket shadow-card">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-[10px] tracking-[0.18em] uppercase text-gray-500 font-display">Active Workers</span>
                    <svg class="w-4 h-4 text-harbor-400 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="flex items-end gap-2">
                    <span class="font-display text-4xl font-700 text-harbor-400 leading-none" x-text="kpi.active_workers"></span>
                    <span class="text-gray-500 text-sm mb-1 font-mono">workers</span>
                </div>
                <div class="mt-3 h-1 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-harbor-500 rounded-full transition-all duration-700"
                         :style="'width:' + (kpi.active_workers / 10 * 100) + '%'"></div>
                </div>
            </div>

            {{-- Avg Capability Index --}}
            <div class="glass-card rounded-xl p-4 hud-bracket shadow-card">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-[10px] tracking-[0.18em] uppercase text-gray-500 font-display">Avg Capability</span>
                    <svg class="w-4 h-4 opacity-70" :class="kpi.avg_capability >= 70 ? 'text-harbor-400' : kpi.avg_capability >= 40 ? 'text-yellow-400' : 'text-red-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div class="flex items-end gap-2">
                    <span class="font-display text-4xl font-700 leading-none"
                          :class="kpi.avg_capability >= 70 ? 'text-harbor-400' : kpi.avg_capability >= 40 ? 'text-yellow-400' : 'text-red-400'"
                          x-text="kpi.avg_capability + '%'"></span>
                </div>
                <div class="mt-3 h-1 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700"
                         :class="kpi.avg_capability >= 70 ? 'bg-harbor-500' : kpi.avg_capability >= 40 ? 'bg-yellow-500' : 'bg-red-500'"
                         :style="'width:' + kpi.avg_capability + '%'"></div>
                </div>
            </div>

            {{-- Critical Alerts --}}
            <div class="glass-card rounded-xl p-4 hud-bracket shadow-card"
                 :class="kpi.critical_alerts > 0 ? 'border-red-500/30 shadow-danger-glow' : ''">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-[10px] tracking-[0.18em] uppercase text-gray-500 font-display">Critical Alerts</span>
                    <svg class="w-4 h-4 opacity-70" :class="kpi.critical_alerts > 0 ? 'text-red-400 blink-critical' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="flex items-end gap-2">
                    <span class="font-display text-4xl font-700 leading-none"
                          :class="kpi.critical_alerts > 0 ? 'text-red-400' : 'text-gray-400'"
                          x-text="kpi.critical_alerts"></span>
                    <span class="text-gray-500 text-sm mb-1 font-mono">active</span>
                </div>
                <div class="mt-3 text-xs font-mono" :class="kpi.critical_alerts > 0 ? 'text-red-400' : 'text-gray-600'">
                    <span x-text="kpi.critical_alerts > 0 ? '⚠ IMMEDIATE RESPONSE REQUIRED' : '✓ All Clear'"></span>
                </div>
            </div>

            {{-- Near-Miss Reports --}}
            <div class="glass-card rounded-xl p-4 hud-bracket shadow-card">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-[10px] tracking-[0.18em] uppercase text-gray-500 font-display">Near-Miss Reports</span>
                    <svg class="w-4 h-4 text-yellow-400 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="flex items-end gap-2">
                    <span class="font-display text-4xl font-700 text-yellow-400 leading-none" x-text="kpi.near_miss_reports"></span>
                    <span class="text-gray-500 text-sm mb-1 font-mono">today</span>
                </div>
                <div class="mt-3 text-xs font-mono text-yellow-600" x-text="kpi.near_miss_reports > 0 ? '⚡ SOS EVENTS LOGGED' : '— No SOS Events'"></div>
            </div>

        </div>
    </section>

    {{-- ── MAIN GRID: Map + Chart | Alert Feed ────────────────────────── --}}
    <section class="px-4 pt-4 pb-0">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            {{-- LEFT: Map + HR Chart (2/3 width) --}}
            <div class="xl:col-span-2 space-y-4">

                {{-- GPS Map --}}
                <div class="glass-card rounded-xl overflow-hidden shadow-card">
                    <div class="flex items-center justify-between px-4 py-2.5 border-b border-harbor-500/10">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-harbor-400 rounded-full animate-pulse"></span>
                            <h2 class="font-display text-sm font-600 tracking-widest text-harbor-400 uppercase">GPS Live Map — Zona Panas</h2>
                        </div>
                        <span class="font-mono text-xs text-gray-500" x-text="'Workers tracked: ' + workers.filter(w => w.gps_lat).length"></span>
                    </div>
                    <div class="scan-overlay" style="height:280px;">
                        <div id="map" class="w-full h-full"></div>
                    </div>
                </div>

                {{-- Heart Rate Chart --}}
                <div class="glass-card rounded-xl overflow-hidden shadow-card">
                    <div class="flex items-center justify-between px-4 py-2.5 border-b border-harbor-500/10">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/></svg>
                            <h2 class="font-display text-sm font-600 tracking-widest text-gray-300 uppercase">Real-Time Heart Rate Trends</h2>
                        </div>
                        {{-- Device selector --}}
                        <select
                            class="bg-slate-900 border border-slate-700 text-gray-400 text-xs rounded-md px-2 py-1 font-mono focus:outline-none focus:border-harbor-500"
                            x-model="selectedDevice"
                            @change="fetchHRTrend()"
                        >
                            <template x-for="w in workers" :key="w.device_id">
                                <option :value="w.device_id" x-text="w.device_id"></option>
                            </template>
                        </select>
                    </div>
                    <div class="px-4 py-3" style="height:200px;">
                        <canvas id="hrChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- RIGHT: Alert Feed Sidebar (1/3 width) --}}
            <div class="glass-card rounded-xl overflow-hidden shadow-card flex flex-col" style="max-height:500px;">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-harbor-500/10 shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-red-400 rounded-full animate-pulse"></span>
                        <h2 class="font-display text-sm font-600 tracking-widest text-gray-300 uppercase">Live Alert Feed</h2>
                    </div>
                    <span class="font-mono text-[10px] text-gray-600" x-text="alertFeed.length + ' events'"></span>
                </div>

                {{-- Scrollable feed --}}
                <div class="overflow-y-auto flex-1 px-3 py-2 space-y-1.5" id="alertScroll">
                    <template x-if="alertFeed.length === 0">
                        <div class="flex flex-col items-center justify-center h-32 text-gray-600">
                            <svg class="w-8 h-8 mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs font-mono">No active alerts</span>
                        </div>
                    </template>
                    <template x-for="(alert, i) in alertFeed" :key="i">
                        <div
                            class="alert-item rounded-lg px-3 py-2.5 border text-xs font-mono"
                            :class="{
                                'bg-red-500/8 border-red-500/20 text-red-300':    alert.level === 'critical',
                                'bg-yellow-500/8 border-yellow-500/20 text-yellow-300': alert.level === 'warning',
                            }"
                        >
                            <div class="flex items-start gap-2">
                                <span class="text-base leading-none mt-0.5" x-text="alert.icon"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-600 truncate" x-text="alert.msg"></p>
                                    <p class="text-[10px] opacity-60 mt-0.5" x-text="alert.ago"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Feed footer --}}
                <div class="shrink-0 px-4 py-2 border-t border-harbor-500/10 flex items-center justify-between">
                    <span class="text-[10px] font-mono text-gray-600 uppercase tracking-widest">Auto-refresh 10s</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full animate-pulse" :class="isLoading ? 'bg-yellow-400' : 'bg-harbor-400'"></span>
                        <span class="text-[10px] font-mono" :class="isLoading ? 'text-yellow-400' : 'text-harbor-400'" x-text="isLoading ? 'SYNCING...' : 'CONNECTED'"></span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ── WORKER TABLE ────────────────────────────────────────────────── --}}
    <section class="px-4 pt-4 pb-8">
        <div class="glass-card rounded-xl overflow-hidden shadow-card">

            <div class="flex items-center justify-between px-4 py-2.5 border-b border-harbor-500/10">
                <h2 class="font-display text-sm font-600 tracking-widest text-gray-300 uppercase">Active Worker Registry</h2>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-mono text-gray-600 uppercase">Last sync: <span x-text="lastSync" class="text-harbor-400"></span></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800">
                            <th class="text-left px-4 py-3 text-[10px] font-display tracking-widest text-gray-500 uppercase">Device ID</th>
                            <th class="text-left px-4 py-3 text-[10px] font-display tracking-widest text-gray-500 uppercase">Zone</th>
                            <th class="text-left px-4 py-3 text-[10px] font-display tracking-widest text-gray-500 uppercase">HR (BPM)</th>
                            <th class="text-left px-4 py-3 text-[10px] font-display tracking-widest text-gray-500 uppercase hidden md:table-cell">Battery</th>
                            <th class="text-left px-4 py-3 text-[10px] font-display tracking-widest text-gray-500 uppercase">Capability Index</th>
                            <th class="text-left px-4 py-3 text-[10px] font-display tracking-widest text-gray-500 uppercase hidden lg:table-cell">IMU State</th>
                            <th class="text-left px-4 py-3 text-[10px] font-display tracking-widest text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        <template x-if="workers.length === 0">
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-600 font-mono text-xs">
                                    No workers tracked yet. Waiting for ESP32 telemetry...
                                </td>
                            </tr>
                        </template>
                        <template x-for="w in workers" :key="w.device_id">
                            <tr class="hover:bg-slate-800/30 transition-colors group">
                                {{-- Device ID --}}
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs font-600 text-harbor-400" x-text="w.device_id"></span>
                                </td>
                                {{-- Zone (derived from GPS or placeholder) --}}
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-gray-400">DOCK-1</span>
                                </td>
                                {{-- HR --}}
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs font-600"
                                          :class="w.hr_bpm > 140 ? 'text-red-400' : w.hr_bpm > 110 ? 'text-yellow-400' : 'text-gray-300'"
                                          x-text="w.hr_bpm ?? '—'"></span>
                                </td>
                                {{-- Battery --}}
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500"
                                                 :class="w.battery_level >= 50 ? 'bg-harbor-500' : w.battery_level >= 20 ? 'bg-yellow-500' : 'bg-red-500'"
                                                 :style="'width:' + (w.battery_level ?? 0) + '%'"></div>
                                        </div>
                                        <span class="font-mono text-[11px] text-gray-500" x-text="(w.battery_level ?? '?') + '%'"></span>
                                    </div>
                                </td>
                                {{-- Capability Index --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-24 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                            <div class="cap-bar-fill"
                                                 :class="w.capability_index >= 70 ? 'bg-harbor-500' : w.capability_index >= 40 ? 'bg-yellow-500' : 'bg-red-500'"
                                                 :style="'width:' + (w.capability_index ?? 0) + '%'"></div>
                                        </div>
                                        <span class="font-mono text-[11px]"
                                              :class="w.capability_index >= 70 ? 'text-harbor-400' : w.capability_index >= 40 ? 'text-yellow-400' : 'text-red-400'"
                                              x-text="(w.capability_index ?? 0) + '%'"></span>
                                    </div>
                                </td>
                                {{-- IMU State --}}
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <span class="font-mono text-[11px] text-gray-500 capitalize" x-text="w.imu_state ?? 'unknown'"></span>
                                </td>
                                {{-- Status Badge --}}
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-mono font-600 uppercase tracking-wider"
                                          :class="{
                                              'badge-fit':      w.worker_status === 'fit',
                                              'badge-warning':  w.worker_status === 'warning',
                                              'badge-critical': w.worker_status === 'critical',
                                              'badge-inactive': w.worker_status === 'inactive',
                                          }"
                                          x-text="w.worker_status">
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</main>

{{-- ── FOOTER ────────────────────────────────────────────────────────── --}}
<footer class="px-5 py-3 border-t border-slate-800 flex items-center justify-between">
    <span class="text-[10px] font-mono text-gray-700 uppercase tracking-widest">SENSE-TKBM v1.0 — Surabaya Port Authority</span>
    <span class="text-[10px] font-mono text-gray-700">ESP32-C3 Wearable Network</span>
</footer>

{{-- ═══════════════════════════════════════════════════════════════════
     JS DEPENDENCIES
     ═══════════════════════════════════════════════════════════════════ --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<script>
/* ──────────────────────────────────────────────────────────────────
   Alpine.js Component: commandCenter()
   ────────────────────────────────────────────────────────────────── */
function commandCenter() {
    return {
        // ── State ──────────────────────────────────────────────────
        kpi: @json($kpi),
        workers: @json($workers),
        alertFeed: [],
        selectedDevice: '',
        currentTime: '',
        lastSync: '--:--:--',
        isLoading: false,

        // Internal refs
        _map: null,
        _markers: {},
        _hrChart: null,
        _pollTimer: null,
        _alertTimer: null,

        // ── Lifecycle ──────────────────────────────────────────────
        init() {
            this.currentTime = new Date().toLocaleTimeString('id-ID');
            setInterval(() => this.currentTime = new Date().toLocaleTimeString('id-ID'), 1000);

            this.$nextTick(() => {
                this.initMap();
                this.initHRChart();
                this.renderWorkers();

                if (this.workers.length > 0) {
                    this.selectedDevice = this.workers[0].device_id;
                    this.fetchHRTrend();
                }
            });

            // Poll KPI + workers every 10s
            this._pollTimer  = setInterval(() => this.pollLatest(), 10_000);
            // Poll alert feed every 8s
            this._alertTimer = setInterval(() => this.pollAlerts(), 8_000);
            this.pollAlerts(); // initial
        },

        // ── Leaflet Map ────────────────────────────────────────────
        initMap() {
            const center = [-7.1986, 112.7302]; // Tanjung Perak, Surabaya
            this._map = L.map('map', {
                center,
                zoom: 15,
                zoomControl: false,
                attributionControl: false,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(this._map);

            // Port zone circle
            L.circle(center, {
                color:     '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.05,
                weight:    1,
                radius:    300,
            }).addTo(this._map);
        },

        renderWorkers() {
            const statusColors = {
                fit:      '#10b981',
                warning:  '#f59e0b',
                critical: '#ef4444',
                inactive: '#64748b',
            };

            this.workers.forEach(w => {
                if (!w.gps_lat || !w.gps_long) return;
                const color = statusColors[w.worker_status] ?? '#94a3b8';
                const icon  = L.divIcon({
                    className: '',
                    html: `<div style="
                        width:14px;height:14px;
                        background:${color};
                        border:2px solid rgba(255,255,255,0.6);
                        border-radius:50%;
                        box-shadow:0 0 8px ${color};
                        ${w.worker_status === 'critical' ? 'animation:criticalBlink 1.2s infinite' : ''}
                    "></div>`,
                    iconSize: [14, 14],
                    iconAnchor: [7, 7],
                });

                if (this._markers[w.device_id]) {
                    this._markers[w.device_id]
                        .setLatLng([w.gps_lat, w.gps_long])
                        .setIcon(icon);
                } else {
                    const m = L.marker([w.gps_lat, w.gps_long], { icon })
                        .addTo(this._map)
                        .bindPopup(`
                            <div style="font-family:'JetBrains Mono',monospace;font-size:11px;line-height:1.6;color:#e2e8f0;background:#0b1120;padding:8px 10px;border-radius:6px;border:1px solid rgba(16,185,129,0.3)">
                                <strong style="color:#10b981">${w.device_id}</strong><br>
                                HR: ${w.hr_bpm ?? '?'} BPM<br>
                                CAP: ${w.capability_index ?? 0}%<br>
                                Status: <span style="color:${color}">${w.worker_status.toUpperCase()}</span>
                            </div>
                        `, { className: 'dark-popup' });
                    this._markers[w.device_id] = m;
                }
            });
        },

        // ── Chart.js HR Chart ──────────────────────────────────────
        initHRChart() {
            const ctx = document.getElementById('hrChart');
            if (!ctx) return;

            this._hrChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Heart Rate (BPM)',
                        data: [],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239,68,68,0.08)',
                        borderWidth: 1.5,
                        pointRadius: 2,
                        pointBackgroundColor: '#ef4444',
                        tension: 0.4,
                        fill: true,
                    }, {
                        label: 'Safe Zone (100)',
                        data: [],
                        borderColor: 'rgba(16,185,129,0.3)',
                        borderWidth: 1,
                        borderDash: [4, 4],
                        pointRadius: 0,
                        fill: false,
                        tension: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 300 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0b1120',
                            borderColor: 'rgba(16,185,129,0.3)',
                            borderWidth: 1,
                            titleColor: '#10b981',
                            bodyColor: '#e2e8f0',
                            titleFont: { family: 'JetBrains Mono', size: 11 },
                            bodyFont:  { family: 'JetBrains Mono', size: 11 },
                        },
                    },
                    scales: {
                        x: {
                            ticks: { color: '#475569', font: { family: 'JetBrains Mono', size: 9 }, maxTicksLimit: 8 },
                            grid:  { color: 'rgba(255,255,255,0.03)' },
                        },
                        y: {
                            min: 50, max: 180,
                            ticks: { color: '#475569', font: { family: 'JetBrains Mono', size: 9 }, stepSize: 20 },
                            grid:  { color: 'rgba(255,255,255,0.04)' },
                        },
                    },
                },
            });
        },

        async fetchHRTrend() {
            if (!this.selectedDevice) return;
            try {
                const res  = await fetch(`/api/telemetry/hr-trend/${this.selectedDevice}`);
                const json = await res.json();
                if (!json.success || !this._hrChart) return;

                const safeZone = Array(json.data.length).fill(100);
                this._hrChart.data.labels             = json.labels;
                this._hrChart.data.datasets[0].data   = json.data;
                this._hrChart.data.datasets[0].label  = `${this.selectedDevice} — HR (BPM)`;
                this._hrChart.data.datasets[1].data   = safeZone;
                this._hrChart.update('none');
            } catch (e) {
                console.warn('[SENSE-TKBM] HR trend fetch failed:', e);
            }
        },

        // ── Polling ────────────────────────────────────────────────
        async pollLatest() {
            this.isLoading = true;
            try {
                const res  = await fetch('/api/telemetry/latest');
                const json = await res.json();
                if (!json.success) return;

                this.kpi     = json.kpi;
                this.workers = json.workers;
                this.lastSync = new Date().toLocaleTimeString('id-ID');
                this.renderWorkers();
                this.fetchHRTrend();
            } catch (e) {
                console.warn('[SENSE-TKBM] Poll failed:', e);
            } finally {
                this.isLoading = false;
            }
        },

        async pollAlerts() {
            try {
                const res  = await fetch('/api/telemetry/alerts');
                const json = await res.json();
                if (!json.success) return;

                this.alertFeed = json.feed;

                // Auto-scroll feed to top when new alert arrives
                this.$nextTick(() => {
                    const el = document.getElementById('alertScroll');
                    if (el) el.scrollTop = 0;
                });
            } catch (e) {
                console.warn('[SENSE-TKBM] Alert feed fetch failed:', e);
            }
        },
    };
}
</script>
</body>
</html>