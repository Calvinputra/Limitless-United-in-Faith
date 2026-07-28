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

    public function addItem(): void
    {
        $this->items[] = ['key' => '', 'value' => ''];
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
        $this->success('Gereja lokal disimpan.', position: 'toast-bottom');
    }

    public function with(): array
    {
        return [
            'amountLabel' => 'Rp '.number_format($this->transfer_amount, 0, ',', '.'),
        ];
    }
}; ?>

<div>
    <x-header title="Setting" subtitle="Pembayaran & gereja lokal." separator progress-indicator />

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
        {{-- PEMBAYARAN --}}
        <x-card title="Pembayaran" class="!p-4" shadow>
            <div class="mb-3 rounded-lg border border-teal-800/15 bg-teal-50/70 px-3 py-2 text-sm">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[0.65rem] font-semibold uppercase tracking-wider text-teal-800/70">Transfer ke</span>
                    <strong class="text-teal-900">{{ $amountLabel }}</strong>
                </div>
                <p class="mt-1 text-[#1a2433]">
                    <strong>{{ $bank_name }}</strong> · {{ $bank_account }} · {{ $bank_holder }}
                </p>
            </div>

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
                <x-button label="Simpan pembayaran" type="submit" class="btn-primary btn-sm w-full" icon="o-check" spinner="savePayment" />
            </x-form>
        </x-card>

        {{-- GEREJA --}}
        <x-card title="Gereja lokal" subtitle="Key disimpan · Value ditampilkan" class="!p-4" shadow>
            <div class="space-y-2">
                <div class="hidden grid-cols-[1fr_1.3fr_2rem] gap-2 px-1 text-xs font-medium text-base-content/50 sm:grid">
                    <span>Key</span>
                    <span>Value</span>
                    <span></span>
                </div>

                @foreach ($items as $index => $item)
                    <div wire:key="gereja-item-{{ $index }}" class="grid grid-cols-1 gap-1.5 sm:grid-cols-[1fr_1.3fr_2rem] sm:items-center">
                        <input
                            type="text"
                            class="input input-bordered input-sm w-full"
                            placeholder="key"
                            wire:model="items.{{ $index }}.key"
                        >
                        <input
                            type="text"
                            class="input input-bordered input-sm w-full"
                            placeholder="value"
                            wire:model="items.{{ $index }}.value"
                            required
                        >
                        <button
                            type="button"
                            class="btn btn-ghost btn-xs text-error justify-self-end sm:justify-self-center"
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
                <x-button label="Simpan gereja" icon="o-check" class="btn-primary btn-sm" wire:click="saveGereja" spinner="saveGereja" />
            </div>
        </x-card>
    </div>
</div>
