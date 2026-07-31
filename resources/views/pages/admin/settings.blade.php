<?php

use App\Models\Setting;
use App\Services\GerejaOptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.app')]
#[Title('Setting')]
class extends Component {
    use Toast;

    public string $bank_name = 'BCA';

    public string $bank_account = '4660260451';

    public string $bank_holder = 'Vera Lisiani Bong';

    public string $bank_remark = '';

    public string $transfer_amount = '150.000';

    /** @var list<string> */
    public array $items = [];

    public bool $openPayment = false;

    public bool $openGereja = false;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->bank_name = Setting::getValue('bank_name', 'BCA') ?? 'BCA';
        $this->bank_account = Setting::getValue('bank_account', '4660260451') ?? '4660260451';
        $this->bank_holder = Setting::getValue('bank_holder', 'Vera Lisiani Bong') ?? 'Vera Lisiani Bong';
        $this->bank_remark = Setting::getValue('bank_remark', '') ?? '';
        $this->transfer_amount = $this->formatRupiah(
            max(0, (int) (Setting::getValue('transfer_amount', '150000') ?? '150000'))
        );

        GerejaOptionService::ensureSeeded();
        $this->items = GerejaOptionService::all();
    }

    public function updatedTransferAmount(string $value): void
    {
        $this->transfer_amount = $this->formatRupiah($this->parseRupiah($value));
    }

    public function togglePayment(): void
    {
        $this->openPayment = ! $this->openPayment;
    }

    public function toggleGereja(): void
    {
        $this->openGereja = ! $this->openGereja;
    }

    public function addItem(): void
    {
        $this->items[] = '';
        $this->openGereja = true;
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function savePayment(): void
    {
        $amount = $this->parseRupiah($this->transfer_amount);

        $validated = $this->validate([
            'bank_name' => 'required|string|min:2|max:80',
            'bank_account' => ['required', 'string', 'min:5', 'max:40', 'regex:/^[0-9\-\s]+$/'],
            'bank_holder' => 'required|string|min:3|max:120',
            'bank_remark' => 'nullable|string|max:2000',
            'transfer_amount' => 'required|string|min:1',
        ], [
            'bank_account.regex' => 'Nomor rekening hanya boleh angka.',
            'transfer_amount.required' => 'Nominal wajib diisi.',
        ]);

        if ($amount < 1000 || $amount > 100000000) {
            $this->addError('transfer_amount', 'Nominal harus antara Rp 1.000 dan Rp 100.000.000.');

            return;
        }

        Setting::setValue('bank_name', trim($validated['bank_name']));
        Setting::setValue('bank_account', preg_replace('/\s+/', '', $validated['bank_account']) ?? $validated['bank_account']);
        Setting::setValue('bank_holder', trim($validated['bank_holder']));
        Setting::setValue('bank_remark', trim((string) ($validated['bank_remark'] ?? '')));
        Setting::setValue('transfer_amount', (string) $amount);

        $this->bank_account = Setting::getValue('bank_account', $this->bank_account) ?? $this->bank_account;
        $this->bank_remark = Setting::getValue('bank_remark', $this->bank_remark) ?? $this->bank_remark;
        $this->transfer_amount = $this->formatRupiah($amount);
        $this->openPayment = false;
        $this->success('Pembayaran disimpan.', position: 'toast-bottom');
    }

    public function saveGereja(): void
    {
        $this->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'required|string|max:120',
        ], [
            'items.*.required' => 'Nama gereja wajib diisi.',
        ]);

        try {
            GerejaOptionService::save($this->items);
        } catch (ValidationException $e) {
            $this->error($e->validator->errors()->first() ?: $e->getMessage(), position: 'toast-bottom');

            throw $e;
        }

        $this->items = GerejaOptionService::all();
        $this->openGereja = false;
        $this->success('Gereja lokal disimpan.', position: 'toast-bottom');
    }

    public function with(): array
    {
        $names = collect($this->items)->map(fn ($item) => trim((string) $item))->filter();
        $amount = $this->parseRupiah($this->transfer_amount);

        return [
            'amountLabel' => 'Rp '.$this->formatRupiah($amount),
            'gerejaCount' => $names->count(),
            'gerejaPreview' => $names->take(3)->implode(', '),
        ];
    }

    private function parseRupiah(string $value): int
    {
        return max(0, (int) preg_replace('/\D+/', '', $value));
    }

    private function formatRupiah(int $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }
}; ?>

<div>
    <x-header title="Setting" subtitle="Pembayaran & gereja lokal." separator progress-indicator />

    <div class="mx-auto max-w-3xl space-y-4">
        {{-- PEMBAYARAN --}}
        <div class="overflow-hidden rounded-2xl border border-base-300 bg-base-100 shadow-sm">
            <button
                type="button"
                class="flex w-full items-center gap-3 px-4 py-4 text-left transition hover:bg-base-200/40 sm:px-5"
                wire:click="togglePayment"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-800">
                    <x-icon name="o-banknotes" class="h-4 w-4" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-base-content">Pembayaran</span>
                    <span class="mt-0.5 block truncate text-xs text-base-content/55">
                        {{ $bank_name }} · {{ $bank_account }} · {{ $bank_holder }} · {{ $amountLabel }}
                        @if (trim($bank_remark) !== '')
                            · {{ $bank_remark }}
                        @endif
                    </span>
                </span>
                <x-icon
                    name="{{ $openPayment ? 'o-chevron-up' : 'o-chevron-down' }}"
                    class="h-4 w-4 shrink-0 text-base-content/40"
                />
            </button>

            @if ($openPayment)
                <div class="border-t border-base-300 px-4 py-4 sm:px-5">
                    <x-form wire:submit="savePayment" class="!gap-3">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <x-input label="Bank" wire:model="bank_name" placeholder="BCA" required />
                            <x-input label="No. rek" wire:model="bank_account" placeholder="4660260451" inputmode="numeric" required />
                        </div>
                        <x-input label="Atas nama" wire:model="bank_holder" placeholder="Vera Lisiani Bong" required />
                        <label class="form-control w-full">
                            <span class="label-text mb-1.5 text-sm font-medium">Keterangan / remark</span>
                            <textarea
                                class="textarea textarea-bordered min-h-32 w-full leading-relaxed"
                                rows="5"
                                wire:model="bank_remark"
                                placeholder="Pastikan foto/screenshot bukti transfer menampilkan:&#10;✓ Nominal berakhiran 001&#10;✓ Berita transfer bertuliskan DM - (Nama Gereja Lokal)&#10;(Format file: JPG, PNG, atau PDF. Maksimal 4 MB)"
                            ></textarea>
                            <span class="mt-1 text-xs text-base-content/50">Bisa Enter untuk baris baru. Tampil di landing di bawah atas nama.</span>
                            @error('bank_remark')
                                <span class="mt-1 text-sm text-error">{{ $message }}</span>
                            @enderror
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text mb-1.5 text-sm font-medium">
                                Nominal <span class="text-error">*</span>
                            </span>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-semibold text-base-content/55">Rp</span>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    class="input input-bordered w-full pl-10 tabular-nums"
                                    wire:model.live.debounce.200ms="transfer_amount"
                                    placeholder="100.000"
                                    required
                                >
                            </div>
                            @error('transfer_amount')
                                <span class="mt-1 text-sm text-error">{{ $message }}</span>
                            @enderror
                        </label>
                        <div class="flex flex-wrap gap-2 pt-1">
                            <x-button label="Simpan" type="submit" class="btn-primary btn-sm" icon="o-check" spinner="savePayment" />
                            <x-button label="Tutup" type="button" class="btn-ghost btn-sm" wire:click="$set('openPayment', false)" />
                        </div>
                    </x-form>
                </div>
            @endif
        </div>

        {{-- GEREJA --}}
        <div class="overflow-hidden rounded-2xl border border-base-300 bg-base-100 shadow-sm">
            <button
                type="button"
                class="flex w-full items-center gap-3 px-4 py-4 text-left transition hover:bg-base-200/40 sm:px-5"
                wire:click="toggleGereja"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                    <x-icon name="o-building-library" class="h-4 w-4" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-base-content">Gereja lokal</span>
                    <span class="mt-0.5 block truncate text-xs text-base-content/55">
                        {{ $gerejaCount }} opsi
                        @if ($gerejaPreview)
                            · {{ $gerejaPreview }}{{ $gerejaCount > 3 ? '…' : '' }}
                        @endif
                    </span>
                </span>
                <x-icon
                    name="{{ $openGereja ? 'o-chevron-up' : 'o-chevron-down' }}"
                    class="h-4 w-4 shrink-0 text-base-content/40"
                />
            </button>

            @if ($openGereja)
                <div class="border-t border-base-300 px-4 py-4 sm:px-5">
                    <div class="space-y-2.5">
                        @foreach ($items as $index => $item)
                            <div wire:key="gereja-item-{{ $index }}" class="flex items-center gap-2.5">
                                <input
                                    type="text"
                                    class="input input-bordered input-sm min-h-10 min-w-0 flex-1"
                                    placeholder="Nama gereja"
                                    wire:model="items.{{ $index }}"
                                    required
                                >
                                <button
                                    type="button"
                                    class="btn btn-ghost btn-sm h-10 w-10 shrink-0 p-0 text-error"
                                    wire:click="removeItem({{ $index }})"
                                    title="Hapus"
                                >
                                    <x-icon name="o-trash" class="h-4 w-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>

                    @error('items')
                        <p class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                    @error('items.*')
                        <p class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror

                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-button label="Tambah" icon="o-plus" class="btn-ghost btn-sm" wire:click="addItem" />
                        <x-button label="Simpan" icon="o-check" class="btn-primary btn-sm" wire:click="saveGereja" spinner="saveGereja" />
                        <x-button label="Tutup" class="btn-ghost btn-sm" wire:click="$set('openGereja', false)" />
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
