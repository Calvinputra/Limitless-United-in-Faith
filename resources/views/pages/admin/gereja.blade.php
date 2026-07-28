<?php

use App\Services\GerejaOptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.app')]
#[Title('Gereja Lokal')]
class extends Component {
    use Toast;

    /** @var list<array{key: string, value: string}> */
    public array $items = [];

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        GerejaOptionService::ensureSeeded();
        $this->items = GerejaOptionService::all();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'key' => '',
            'value' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(): void
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
            $message = $e->validator->errors()->first() ?: $e->getMessage();
            $this->error($message, position: 'toast-bottom');

            throw $e;
        }

        $this->items = GerejaOptionService::all();
        $this->success('Daftar gereja lokal disimpan.', position: 'toast-bottom');
    }

    public function resetDefaults(): void
    {
        GerejaOptionService::save(GerejaOptionService::defaults());
        $this->items = GerejaOptionService::all();
        $this->success('Daftar gereja dikembalikan ke default.', position: 'toast-bottom');
    }
}; ?>

<div>
    <x-header
        title="Gereja Lokal"
        subtitle="Kelola pilihan dropdown gereja (key = disimpan di data, value = teks yang tampil)."
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-button label="Pendaftar" class="btn-ghost btn-sm" link="{{ route('admin.registrations') }}" />
        </x-slot:actions>
    </x-header>

    <x-card title="Daftar gereja" subtitle="Key tidak sebaiknya sering diganti agar data lama tetap cocok. Value boleh diubah kapan saja." shadow>
        <div class="mb-4 rounded-xl border border-teal-800/15 bg-teal-50/70 px-3.5 py-3 text-sm text-[#1a2433]">
            Contoh: key <code class="rounded bg-white px-1">central-park</code>,
            value <code class="rounded bg-white px-1">GMS Central Park</code>.
            Form pendaftaran akan menampilkan value, tapi menyimpan key.
        </div>

        <div class="space-y-3">
            @foreach ($items as $index => $item)
                <div wire:key="gereja-item-{{ $index }}" class="grid grid-cols-1 gap-2 rounded-xl border border-base-300 bg-base-100 p-3 sm:grid-cols-[1fr_1.4fr_auto] sm:items-end">
                    <x-input
                        label="Key"
                        wire:model="items.{{ $index }}.key"
                        placeholder="contoh: central-park"
                    />
                    <x-input
                        label="Value (tampil di form)"
                        wire:model="items.{{ $index }}.value"
                        placeholder="Contoh: Central Park"
                        required
                    />
                    <x-button
                        icon="o-trash"
                        class="btn-ghost btn-sm text-error"
                        wire:click="removeItem({{ $index }})"
                        title="Hapus"
                    />
                </div>
            @endforeach
        </div>

        @error('items')
            <p class="mt-2 text-sm text-error">{{ $message }}</p>
        @enderror
        @error('items.*.value')
            <p class="mt-2 text-sm text-error">{{ $message }}</p>
        @enderror

        <div class="mt-4 flex flex-wrap gap-2">
            <x-button label="Tambah gereja" icon="o-plus" class="btn-ghost" wire:click="addItem" />
            <x-button label="Simpan" icon="o-check" class="btn-primary" wire:click="save" spinner="save" />
            <x-button
                label="Reset default"
                icon="o-arrow-path"
                class="btn-ghost"
                wire:click="resetDefaults"
                wire:confirm="Kembalikan ke 5 gereja default?"
                spinner="resetDefaults"
            />
        </div>
    </x-card>
</div>
