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

    public int $transfer_amount = 150000;

    /** @var list<array{key: string, value: string}> */
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
        $this->transfer_amount = max(0, (int) (Setting::getValue('transfer_amount', '150000') ?? '150000'));

        GerejaOptionService::ensureSeeded();
        $this->items = GerejaOptionService::all();
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
        $this->items[] = ['key' => '', 'value' => ''];
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
        $validated = $this->validate([
            'bank_name' => 'required|string|min:2|max:80',
            'bank_account' => ['required', 'string', 'min:5', 'max:40', 'regex:/^[0-9\-\s]+$/'],
            'bank_holder' => 'required|string|min:3|max:120',
            'transfer_amount' => 'required|integer|min:1000|max:100000000',
        ], [
            'bank_account.regex' => 'Nomor rekening hanya boleh angka.',
            'transfer_amount.min' => 'Nominal minimal Rp 1.000.',
        ]);

        Setting::setValue('bank_name', trim($validated['bank_name']));
        Setting::setValue('bank_account', preg_replace('/\s+/', '', $validated['bank_account']) ?? $validated['bank_account']);
        Setting::setValue('bank_holder', trim($validated['bank_holder']));
        Setting::setValue('transfer_amount', (string) $validated['transfer_amount']);

        $this->bank_account = Setting::getValue('bank_account', $this->bank_account) ?? $this->bank_account;
        $this->openPayment = false;
        $this->success('Pembayaran disimpan.', position: 'toast-bottom');
    }

    public function saveGereja(): void
    {
        $this->validate([
            'items' => 'required|array|min:1',
            'items.*.key' => 'nullable|string|max:80',
            'items.*.value' => 'required|string|max:120',
        ], [
            'items.*.value.required' => 'Value gereja wajib diisi.',
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
        return [
            'amountLabel' => 'Rp '.number_format($this->transfer_amount, 0, ',', '.'),
            'gerejaCount' => count($this->items),
            'gerejaPreview' => collect($this->items)->pluck('value')->filter()->take(3)->implode(', '),
        ];
    }
}; ?>

<div>
    <x-header title="Setting" subtitle="Pembayaran & gereja lokal." separator progress-indicator />

    <div class="mx-auto max-w-3xl space-y-2.5">
        {{-- PEMBAYARAN --}}
        <div class="overflow-hidden rounded-xl border border-base-300 bg-base-100 shadow-sm">
            <button
                type="button"
                class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-base-200/50"
                wire:click="togglePayment"
                :aria-expanded="{{ $openPayment ? 'true' : 'false' }}"
            >
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-800">
                    <x-icon name="o-banknotes" class="h-4 w-4" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-base-content">Pembayaran</span>
                    <span class="block truncate text-xs text-base-content/55">
                        {{ $bank_name }} · {{ $bank_account }} · {{ $bank_holder }} · {{ $amountLabel }}
                    </span>
                </span>
                <x-icon
                    name="{{ $openPayment ? 'o-chevron-up' : 'o-chevron-down' }}"
                    class="h-4 w-4 shrink-0 text-base-content/40"
                />
            </button>

            @if ($openPayment)
                <div class="border-t border-base-300 px-4 py-3">
                    <x-form wire:submit="savePayment" class="!gap-2">
                        <div class="grid grid-cols-2 gap-2">
                            <x-input label="Bank" wire:model="bank_name" placeholder="BCA" required />
                            <x-input label="No. rek" wire:model="bank_account" placeholder="4660260451" inputmode="numeric" required />
                        </div>
                        <x-input label="Atas nama" wire:model="bank_holder" placeholder="Vera Lisiani Bong" required />
                        <x-input
                            label="Nominal (Rp)"
                            type="number"
                            wire:model.live="transfer_amount"
                            min="1000"
                            step="1000"
                            required
                        />
                        <div class="flex gap-2 pt-1">
                            <x-button label="Simpan" type="submit" class="btn-primary btn-sm" icon="o-check" spinner="savePayment" />
                            <x-button label="Tutup" type="button" class="btn-ghost btn-sm" wire:click="$set('openPayment', false)" />
                        </div>
                    </x-form>
                </div>
            @endif
        </div>

        {{-- GEREJA --}}
        <div class="overflow-hidden rounded-xl border border-base-300 bg-base-100 shadow-sm">
            <button
                type="button"
                class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-base-200/50"
                wire:click="toggleGereja"
            >
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                    <x-icon name="o-building-library" class="h-4 w-4" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-base-content">Gereja lokal</span>
                    <span class="block truncate text-xs text-base-content/55">
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
                <div class="border-t border-base-300 px-4 py-3">
                    <div class="mb-1.5 flex items-center gap-2 text-[0.7rem] font-medium text-base-content/50">
                        <span class="min-w-0 flex-1">Key</span>
                        <span class="min-w-0 flex-[1.2]">Value</span>
                        <span class="w-8 shrink-0"></span>
                    </div>

                    <div class="space-y-1.5">
                        @foreach ($items as $index => $item)
                            <div wire:key="gereja-item-{{ $index }}" class="flex items-center gap-2">
                                <input
                                    type="text"
                                    class="input input-bordered input-sm min-w-0 flex-1 !w-auto"
                                    placeholder="key"
                                    wire:model="items.{{ $index }}.key"
                                >
                                <input
                                    type="text"
                                    class="input input-bordered input-sm min-w-0 flex-[1.2] !w-auto"
                                    placeholder="value"
                                    wire:model="items.{{ $index }}.value"
                                    required
                                >
                                <button
                                    type="button"
                                    class="btn btn-ghost btn-xs h-8 w-8 shrink-0 p-0 text-error"
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
                    @error('items.*.value')
                        <p class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror

                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-button label="Tambah" icon="o-plus" class="btn-ghost btn-sm" wire:click="addItem" />
                        <x-button label="Simpan" icon="o-check" class="btn-primary btn-sm" wire:click="saveGereja" spinner="saveGereja" />
                        <x-button label="Tutup" class="btn-ghost btn-sm" wire:click="$set('openGereja', false)" />
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
