<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.app')]
#[Title('Pembayaran')]
class extends Component {
    use Toast;

    public string $bank_name = 'BCA';

    public string $bank_account = '4660260451';

    public string $bank_holder = 'Vera Lisiani Bong';

    public int $transfer_amount = 150000;

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
    }

    public function save(): void
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
        $this->success('Info pembayaran disimpan. Landing page ikut terbarui.', position: 'toast-bottom');
    }

    public function with(): array
    {
        return [
            'amountLabel' => 'Rp '.number_format($this->transfer_amount, 0, ',', '.'),
        ];
    }
}; ?>

<div>
    <x-header
        title="Pembayaran"
        subtitle="Atur rekening transfer dan nominal yang tampil di form pendaftaran."
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-button label="Pendaftar" class="btn-ghost btn-sm" link="{{ route('admin.registrations') }}" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Preview" subtitle="Tampilan di landing" class="xl:col-span-1" shadow>
            <div class="rounded-xl border border-teal-800/15 bg-teal-50/80 px-3.5 py-3">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.12em] text-teal-800/80">Transfer ke</p>
                    <p class="text-sm font-bold text-teal-900">{{ $amountLabel }}</p>
                </div>
                <div class="mt-2 space-y-1 text-sm leading-snug text-[#1a2433]">
                    <p><span class="opacity-60">Bank</span> · <strong>{{ $bank_name }}</strong></p>
                    <p><span class="opacity-60">No. rekening</span> · <strong class="tracking-wide">{{ $bank_account }}</strong></p>
                    <p><span class="opacity-60">Atas nama</span> · <strong>{{ $bank_holder }}</strong></p>
                </div>
            </div>
        </x-card>

        <x-card title="Rekening & nominal" subtitle="Bisa diubah kapan saja" class="xl:col-span-2" shadow>
            <x-form wire:submit="save" class="!gap-3">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-input label="Bank" wire:model="bank_name" placeholder="BCA" required />
                    <x-input
                        label="No. rekening"
                        wire:model="bank_account"
                        placeholder="4660260451"
                        inputmode="numeric"
                        required
                    />
                </div>

                <x-input
                    label="Atas nama"
                    wire:model="bank_holder"
                    placeholder="Vera Lisiani Bong"
                    required
                />

                <x-input
                    label="Nominal transfer (Rp)"
                    type="number"
                    wire:model.live="transfer_amount"
                    placeholder="150000"
                    min="1000"
                    step="1000"
                    hint="Contoh: 150000 = Rp 150.000"
                    required
                />

                <div class="pt-1">
                    <x-button label="Simpan" type="submit" class="btn-primary" icon="o-check" spinner="save" />
                </div>
            </x-form>
        </x-card>
    </div>
</div>
