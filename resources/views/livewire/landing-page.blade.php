<div class="relative overflow-x-hidden bg-[#e4edf7] text-[#1a2433]">
    {{-- HERO --}}
    <header class="relative flex min-h-[100svh] items-end overflow-hidden text-[#f7fafb]">
        <img
            src="{{ asset('images/new_banner.jpeg') }}?v=4"
            alt="Sahabat bergandengan dalam kebersamaan"
            class="hero-kenburns absolute inset-0 h-full w-full object-cover object-[center_40%]"
        >
        <div class="absolute inset-0 bg-gradient-to-b from-[#1e3a5f]/15 via-[#1e3a5f]/38 to-[#16283c]/85"></div>
        <div class="absolute inset-0 bg-gradient-to-tr from-[#1e3a5f]/15 via-transparent to-[#f6ecd8]/15"></div>
        <div class="hero-shimmer absolute inset-0 opacity-15 mix-blend-soft-light"></div>

        <div class="orb -left-10 top-24 h-40 w-40 bg-sky-200/30"></div>
        <div class="orb right-0 bottom-32 h-48 w-48 bg-amber-100/25" style="animation-delay: 1.5s"></div>

        <div class="relative z-10 mx-auto w-full max-w-xl px-5 pb-12 pt-28">
            <p class="font-display text-[clamp(3.25rem,15vw,5.4rem)] font-semibold leading-[0.9] tracking-[-0.03em]" style="animation: rise-in 1s ease-out both">
                Limitless
            </p>
            <h1 class="mt-3 font-display text-[clamp(1.45rem,5.4vw,2.1rem)] font-normal tracking-tight text-white/95" style="animation: rise-in 1s ease-out 0.14s both">
                United in Faith
            </h1>
            <p class="mt-4 max-w-md text-[1.05rem] leading-relaxed text-white/88" style="animation: rise-in 1s ease-out 0.26s both">
                Satu iman, satu perjalanan. Mari saling mengenal, saling menguatkan, dan bertumbuh bersama di dalam Tuhan.
            </p>
            <div class="mt-8 flex flex-wrap gap-3" style="animation: rise-in 1s ease-out 0.4s both">
                <button
                    type="button"
                    onclick="document.querySelector('#daftar')?.scrollIntoView({ behavior: 'smooth' })"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#f7fafb] px-6 text-sm font-bold text-[#12343a] shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-white"
                >
                    Gabung sekarang
                </button>
                <button
                    type="button"
                    onclick="document.querySelector('#firman')?.scrollIntoView({ behavior: 'smooth' })"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/60 px-6 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-white/10"
                >
                    Renungkan firman
                </button>
            </div>

            <button
                type="button"
                class="hero-float mt-10 inline-flex items-center gap-2 text-sm text-white/75"
                onclick="document.querySelector('#firman')?.scrollIntoView({ behavior: 'smooth' })"
            >
                <span class="h-8 w-px bg-white/50"></span>
                Telusuri lebih dalam
            </button>
        </div>
    </header>

    {{-- <svg class="wave-divider -mt-px" viewBox="0 0 1440 56" preserveAspectRatio="none" aria-hidden="true">
        <path fill="currentColor" d="M0,32 C240,56 480,0 720,16 C960,32 1200,56 1440,24 L1440,56 L0,56 Z"></path>
    </svg> --}}

    {{-- AYAT --}}
    <section id="firman" class="dawn-sky-wash relative px-5 pb-16 pt-6">
        <div class="orb left-1/2 top-0 h-56 w-56 -translate-x-1/2 bg-sky-200/40"></div>
        <div class="reveal relative mx-auto max-w-xl">
            <p class="mb-4 text-sm font-semibold uppercase tracking-[0.16em] text-teal-800/80">
                Yesaya 43:19
            </p>
            <blockquote class="font-display text-[clamp(1.25rem,4.8vw,1.75rem)] leading-relaxed tracking-tight text-[#1a2433]">
                “Lihat, Aku hendak membuat sesuatu yang baru, yang sekarang sudah tumbuh, belumkah kamu mengetahuinya?
                Ya, Aku hendak membuat jalan di padang gurun dan sungai-sungai di padang belantara.”
            </blockquote>
            <p class="reveal reveal-delay-1 mt-6 text-sm leading-relaxed text-[#4a5a6d]">
                Tuhan sedang mengerjakan yang baru. Mari kita temukan jalannya — bersama, dalam iman yang satu.
            </p>
        </div>
    </section>

    {{-- MAKNA --}}
    <section class="dawn-glow-wash relative overflow-hidden px-5 py-16">
        <div class="orb -right-8 top-10 h-44 w-44 bg-amber-200/30"></div>
        <div class="orb -left-10 bottom-0 h-52 w-52 bg-sky-300/25" style="animation-delay: 2s"></div>

        <div class="reveal relative mx-auto max-w-xl">
            <h2 class="font-display text-3xl tracking-tight text-[#1a2433] sm:text-4xl">
                Fellowship yang menghidupkan
            </h2>
            <p class="reveal reveal-delay-1 mt-4 text-base leading-relaxed text-[#4a5a6d]">
                Limitless adalah ruang untuk saling mengenal, saling menopang, dan saling menguatkan dalam Kristus —
                supaya kita mengalami sendiri karya Tuhan yang baru, baik dalam kehidupan maupun pelayanan.
            </p>

            <div class="reveal reveal-delay-2 mt-8 grid gap-3 sm:grid-cols-3">
                <div class="dawn-panel rounded-2xl border border-white/70 p-4">
                    <p class="font-display text-lg text-teal-800">Berjumpa</p>
                    <p class="mt-1 text-sm text-[#4a5a6d]">Mengenal hati satu sama lain dengan tulus.</p>
                </div>
                <div class="dawn-panel rounded-2xl border border-white/70 p-4">
                    <p class="font-display text-lg text-teal-800">Berdoa</p>
                    <p class="mt-1 text-sm text-[#4a5a6d]">Menjadi sandaran iman di musim apa pun.</p>
                </div>
                <div class="dawn-panel rounded-2xl border border-white/70 p-4">
                    <p class="font-display text-lg text-teal-800">Bertumbuh</p>
                    <p class="mt-1 text-sm text-[#4a5a6d]">Melangkah ke hal baru yang Tuhan bukakan.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- FORM --}}
    <section id="daftar" class="dawn-valley-wash relative px-5 pb-12 pt-3">
        <div class="reveal mx-auto max-w-xl">
            <div class="mb-2">
                <h2 class="font-display text-3xl tracking-tight text-[#1a2433] sm:text-4xl">Siap bergabung?</h2>
                <p class="mt-1 text-sm leading-snug text-[#4a5a6d]">
                    Lengkapi data singkat di bawah. Siapkan bukti transfer agar pendaftaranmu segera diproses.
                </p>
            </div>

            @if ($submitted)
                <div class="form-glow rounded-2xl border border-teal-800/10 p-4" role="status">
                    <p class="font-display text-3xl text-teal-800">Terima kasih sudah mendaftar!</p>
                    <p class="mt-2 leading-relaxed text-[#4a5a6d]">
                        Kami sudah menerima datamu. Tim akan menghubungi melalui WhatsApp segera.
                    </p>
                    <button
                        type="button"
                        class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-teal-800 px-5 text-sm font-bold text-white transition hover:-translate-y-0.5"
                        wire:click="$set('submitted', false)"
                    >
                        Daftarkan peserta lain
                    </button>
                </div>
            @else
                <div class="form-glow limitless-form rounded-2xl border border-slate-300/60 p-4 shadow-[0_12px_30px_rgba(26,36,51,0.06)] sm:p-5">
                    <x-form wire:submit="submit" class="!gap-2.5">
                        <x-input
                            label="Nama"
                            wire:model="nama"
                            placeholder="Nama lengkap"
                            icon="o-user"
                            required
                        />

                        <div>
                            <p class="mb-1.5 text-sm font-medium text-[#1a2433]">
                                Gender <span class="text-error">*</span>
                            </p>
                            <div
                                class="grid grid-cols-2 gap-2.5"
                                role="radiogroup"
                                aria-label="Pilih gender"
                            >
                                <button
                                    type="button"
                                    role="radio"
                                    aria-checked="{{ $gender === 'Laki-laki' ? 'true' : 'false' }}"
                                    wire:click="$set('gender', 'Laki-laki')"
                                    class="gender-pick gender-pick--male {{ $gender === 'Laki-laki' ? 'is-active' : '' }}"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5 shrink-0" aria-hidden="true">
                                        <circle cx="10" cy="14" r="5.2" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.2 9.8 20 4M15.2 4H20v4.8" />
                                    </svg>
                                    <span>Pria</span>
                                </button>

                                <button
                                    type="button"
                                    role="radio"
                                    aria-checked="{{ $gender === 'Perempuan' ? 'true' : 'false' }}"
                                    wire:click="$set('gender', 'Perempuan')"
                                    class="gender-pick gender-pick--female {{ $gender === 'Perempuan' ? 'is-active' : '' }}"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5 shrink-0" aria-hidden="true">
                                        <circle cx="12" cy="9" r="5.2" />
                                        <path stroke-linecap="round" d="M12 14.2V21M9.2 18.2h5.6" />
                                    </svg>
                                    <span>Wanita</span>
                                </button>
                            </div>
                            @error('gender')
                                <p class="mt-1 text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-input
                            label="Umur"
                            type="number"
                            wire:model="umur"
                            placeholder="Contoh: 21"
                            icon="o-calendar-days"
                            min="12"
                            max="80"
                            required
                        />

                        <x-input
                            label="No. telepon (WhatsApp)"
                            wire:model.live="whatsapp"
                            placeholder="08xxxxxxxxxx"
                            icon="o-phone"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="15"
                            autocomplete="tel"
                            required
                        />

                        <x-select
                            label="Gereja lokal"
                            wire:model="gereja_lokal"
                            :options="$this->gerejaOptions()"
                            placeholder="Pilih gereja lokal"
                            icon="o-building-library"
                            required
                        />

                        <div
                            wire:ignore.self
                            class="mt-1 rounded-xl border border-teal-800/15 bg-teal-50/80 px-3.5 py-3"
                            x-data="{ copied: false }"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-[0.7rem] font-semibold uppercase tracking-[0.12em] text-teal-800/80">Transfer ke</p>
                                <p class="text-sm font-bold text-teal-900">{{ $transferAmountLabel }}</p>
                            </div>

                            <div class="mt-2 space-y-1 text-sm leading-snug text-[#1a2433]">
                                <p><span class="opacity-60">Bank</span> · <strong>{{ $bankName }}</strong></p>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="min-w-0">
                                        <span class="opacity-60">No. rekening</span> ·
                                        <strong id="bank-account-value" class="tracking-wide">{{ $bankAccount }}</strong>
                                    </p>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-teal-800/20 bg-white px-2.5 py-1 text-xs font-semibold text-teal-800"
                                        x-on:click="
                                            navigator.clipboard.writeText('{{ $bankAccount }}').then(() => {
                                                copied = true;
                                                setTimeout(() => copied = false, 1800);
                                            }).catch(() => {
                                                copied = true;
                                                setTimeout(() => copied = false, 1800);
                                            })
                                        "
                                    >
                                        <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
                                    </button>
                                </div>
                                <p><span class="opacity-60">Atas nama</span> · <strong>{{ $bankHolder }}</strong></p>
                                @if ($bankRemark !== '')
                                    <div class="mt-1.5 rounded-lg border border-teal-800/10 bg-white/60 px-2.5 py-2">
                                        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.12em] text-teal-800/70">Keterangan</p>
                                        <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-[#1a2433]">{{ $bankRemark }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div
                            class="mt-1"
                            wire:key="bukti-upload"
                            x-data="{ localPreview: null }"
                        >
                            <label class="mb-1.5 block text-sm font-medium text-[#1a2433]">
                                Bukti transfer <span class="text-error">*</span>
                            </label>

                            <input
                                type="file"
                                wire:model="bukti_tf"
                                accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"
                                class="file-input file-input-bordered w-full"
                                x-on:change="
                                    const file = $event.target.files[0];
                                    if (file && file.type.startsWith('image/')) {
                                        localPreview = URL.createObjectURL(file);
                                    } else {
                                        localPreview = null;
                                    }
                                "
                            />

                            <div wire:loading.flex wire:target="bukti_tf" class="mt-2 items-center gap-2 text-sm text-teal-800">
                                Mengunggah file...
                            </div>

                            @error('bukti_tf')
                                <p class="mt-1 text-sm text-error">{{ $message }}</p>
                            @enderror

                            <div class="mt-2 rounded-xl border border-slate-200 bg-white p-2.5" x-show="localPreview" x-cloak>
                                <img
                                    :src="localPreview"
                                    alt="Preview bukti transfer"
                                    class="max-h-56 w-full rounded-lg object-contain bg-slate-50"
                                >
                                <button
                                    type="button"
                                    class="btn btn-ghost btn-xs mt-2"
                                    wire:click="removeBuktiTf"
                                    x-on:click="localPreview = null"
                                >
                                    Ganti / hapus file
                                </button>
                            </div>

                            @if ($this->buktiFileName)
                                <div class="mt-2 text-sm text-teal-800" x-show="!localPreview">
                                    ✓ File siap: <strong>{{ $this->buktiFileName }}</strong>
                                </div>
                            @endif

                            <p class="mt-1.5 text-xs text-[#4a5a6d]">JPG, PNG, WEBP, atau PDF — maksimal 4MB</p>
                        </div>

                        <div class="pt-2">
                            <x-button
                                label="Kirim pendaftaran"
                                type="submit"
                                class="btn-primary w-full !min-h-11 !rounded-xl"
                                icon="o-paper-airplane"
                                spinner="submit"
                            />
                        </div>
                    </x-form>
                </div>
            @endif
        </div>
    </section>

    <footer class="dawn-dusk-wash px-5 pb-24 pt-2">
        <div class="reveal mx-auto max-w-xl border-t border-white/60 pt-8">
            <p class="font-display text-2xl text-[#1a2433]">Limitless</p>
            <p class="mt-1 text-sm text-[#4a5a6d]">United in Faith</p>
        </div>
    </footer>
</div>
