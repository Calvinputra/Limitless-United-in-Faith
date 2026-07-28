<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Limitless: United in Faith — membangun kebersamaan dan saling menguatkan dalam iman.">
    <meta name="color-scheme" content="light">
    <title>{{ isset($title) ? $title.' — Limitless' : 'Limitless: United in Faith' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased text-[var(--ink)] bg-[var(--canvas)]">
    @if (request()->routeIs('landing'))
        <div
            wire:ignore
            class="music-dock"
            x-data="{
                playing: false,
                volume: 10,
                init() {
                    const saved = localStorage.getItem('limitless-volume');
                    if (saved !== null && !Number.isNaN(Number(saved))) {
                        this.volume = Math.min(100, Math.max(0, Number(saved)));
                    }
                    this.applyVolume();
                    window.limitlessMusic = {
                        toggle: () => this.toggle(),
                        start: () => this.play(),
                    };
                    this.$nextTick(() => this.autoStart());
                },
                applyVolume() {
                    const audio = this.$refs.bgm;
                    if (!audio) return;
                    audio.volume = this.volume / 100;
                    localStorage.setItem('limitless-volume', String(this.volume));
                },
                async autoStart() {
                    const ok = await this.play();
                    if (ok) return;

                    const unlock = async () => {
                        await this.play();
                        window.removeEventListener('pointerdown', unlock);
                        window.removeEventListener('touchstart', unlock);
                        window.removeEventListener('keydown', unlock);
                    };

                    window.addEventListener('pointerdown', unlock, { once: true });
                    window.addEventListener('touchstart', unlock, { once: true });
                    window.addEventListener('keydown', unlock, { once: true });
                },
                async play() {
                    const audio = this.$refs.bgm;
                    if (!audio) return false;
                    this.applyVolume();
                    try {
                        await audio.play();
                        this.playing = true;
                        return true;
                    } catch (e) {
                        this.playing = false;
                        return false;
                    }
                },
                async toggle() {
                    const audio = this.$refs.bgm;
                    if (!audio) return;
                    if (this.playing && !audio.paused) {
                        audio.pause();
                        this.playing = false;
                        return;
                    }
                    await this.play();
                }
            }"
        >
            <audio
                x-ref="bgm"
                loop
                preload="auto"
                playsinline
                class="hidden"
                @play="playing = true"
                @pause="playing = false"
            >
                <source src="{{ asset('audio/limitless-cozy.mp3') }}?v=2" type="audio/mpeg">
            </audio>

            <div class="music-panel">
                <button
                    type="button"
                    class="music-fab"
                    @click="toggle()"
                    :aria-pressed="playing.toString()"
                    :aria-label="playing ? 'Matikan musik' : 'Nyalakan musik'"
                    title="Musik"
                >
                    <span class="music-eq" :class="playing ? '' : 'is-paused'" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </span>
                </button>

                <div class="music-panel__volume">
                    <label class="sr-only" for="music-volume">Volume musik</label>
                    <input
                        id="music-volume"
                        type="range"
                        min="0"
                        max="100"
                        step="1"
                        class="music-volume"
                        x-model.number="volume"
                        @input="applyVolume()"
                        :aria-valuetext="volume + ' persen'"
                    >
                    <span class="music-volume__value" x-text="volume + '%'"></span>
                </div>
            </div>
        </div>
    @endif

    {{ $slot }}

    <x-toast />
</body>
</html>
