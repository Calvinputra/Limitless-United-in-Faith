<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Pendaftaran Fellow')] class extends Component {
    use Toast;

    public string $nama = '';

    public string $email = '';

    public string $whatsapp = '';

    public string $tanggal_lahir = '';

    public string $jenis_kelamin = '';

    public string $kota = '';

    public string $gereja = '';

    public string $status_pelayanan = '';

    public string $motivasi = '';

    public bool $bersedia = false;

    public function jenisKelaminOptions(): array
    {
        return [
            ['id' => 'L', 'name' => 'Laki-laki'],
            ['id' => 'P', 'name' => 'Perempuan'],
        ];
    }

    public function statusPelayananOptions(): array
    {
        return [
            ['id' => 'belum', 'name' => 'Belum melayani'],
            ['id' => 'aktif', 'name' => 'Sedang melayani'],
            ['id' => 'pernah', 'name' => 'Pernah melayani'],
        ];
    }

    public function clearForm(): void
    {
        $this->reset([
            'nama',
            'email',
            'whatsapp',
            'tanggal_lahir',
            'jenis_kelamin',
            'kota',
            'gereja',
            'status_pelayanan',
            'motivasi',
            'bersedia',
        ]);

        $this->resetValidation();
    }

    public function submit(): void
    {
        $this->validate([
            'nama' => 'required|string|min:3|max:120',
            'email' => 'required|email|max:120',
            'whatsapp' => 'required|string|min:10|max:20',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'kota' => 'required|string|max:100',
            'gereja' => 'required|string|max:150',
            'status_pelayanan' => 'required|in:belum,aktif,pernah',
            'motivasi' => 'required|string|min:20|max:1000',
            'bersedia' => 'accepted',
        ], [
            'bersedia.accepted' => 'Kamu harus menyetujui komitmen Fellow DM Umum.',
        ]);

        // Placeholder: simpan ke database / kirim email di langkah berikutnya.
        $this->success('Pendaftaran terkirim. Tim akan menghubungi kamu segera.', position: 'toast-bottom');

        $this->clearForm();
    }
}; ?>

<div>
    <x-header title="Pendaftaran Fellow DM Umum" subtitle="Isi data dengan lengkap agar proses follow-up lebih cepat." separator progress-indicator />

    <div class="max-w-3xl mx-auto">
        <x-form wire:submit="submit">
            <x-card title="Data diri" subtitle="Informasi dasar peserta" shadow separator>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Nama lengkap"
                        wire:model="nama"
                        placeholder="Nama sesuai identitas"
                        icon="o-user"
                        required
                    />

                    <x-input
                        label="Email"
                        type="email"
                        wire:model="email"
                        placeholder="nama@email.com"
                        icon="o-envelope"
                        required
                    />

                    <x-input
                        label="No. WhatsApp"
                        wire:model="whatsapp"
                        placeholder="08xxxxxxxxxx"
                        icon="o-phone"
                        required
                    />

                    <x-input
                        label="Tanggal lahir"
                        type="date"
                        wire:model="tanggal_lahir"
                        icon="o-calendar-days"
                        required
                    />

                    <x-select
                        label="Jenis kelamin"
                        wire:model="jenis_kelamin"
                        :options="$this->jenisKelaminOptions()"
                        placeholder="Pilih jenis kelamin"
                        icon="o-user-group"
                        required
                    />

                    <x-input
                        label="Kota / domisili"
                        wire:model="kota"
                        placeholder="Contoh: Jakarta"
                        icon="o-map-pin"
                        required
                    />
                </div>
            </x-card>

            <x-card title="Latar belakang" subtitle="Informasi gereja dan pelayanan" class="mt-6" shadow separator>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Gereja / komunitas"
                        wire:model="gereja"
                        placeholder="Nama gereja"
                        icon="o-building-library"
                        required
                    />

                    <x-select
                        label="Status pelayanan"
                        wire:model="status_pelayanan"
                        :options="$this->statusPelayananOptions()"
                        placeholder="Pilih status"
                        icon="o-briefcase"
                        required
                    />
                </div>

                <div class="mt-4">
                    <x-textarea
                        label="Motivasi bergabung"
                        wire:model="motivasi"
                        placeholder="Ceritakan singkat kenapa kamu ingin bergabung di Fellow DM Umum..."
                        rows="5"
                        hint="Minimal 20 karakter"
                        required
                    />
                </div>

                <div class="mt-4">
                    <x-checkbox
                        label="Saya bersedia mengikuti proses Fellow DM Umum dengan komitmen yang ditentukan."
                        wire:model="bersedia"
                    />
                </div>
            </x-card>

            <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
                <x-button label="Reset" type="button" icon="o-arrow-path" class="btn-ghost" wire:click="clearForm" />
                <x-button label="Kirim pendaftaran" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="submit" />
            </div>
        </x-form>
    </div>
</div>
