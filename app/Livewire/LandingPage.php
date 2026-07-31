<?php

namespace App\Livewire;

use App\Models\FellowRegistration;
use App\Models\Setting;
use App\Services\GerejaOptionService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Throwable;

#[Layout('layouts.public')]
#[Title('Limitless: United in Faith')]
class LandingPage extends Component
{
    use Toast;
    use WithFileUploads;

    public string $nama = '';

    public string $gender = '';

    public ?int $umur = null;

    public string $whatsapp = '';

    public string $gereja_lokal = '';

    public $bukti_tf = null;

    public bool $submitted = false;

    /**
     * @return list<array{id: string, name: string}>
     */
    public function genderOptions(): array
    {
        return [
            ['id' => 'Laki-laki', 'name' => 'Laki-laki'],
            ['id' => 'Perempuan', 'name' => 'Perempuan'],
        ];
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function gerejaOptions(): array
    {
        GerejaOptionService::ensureSeeded();

        return GerejaOptionService::selectOptions();
    }

    public function removeBuktiTf(): void
    {
        $this->bukti_tf = null;
        $this->resetValidation('bukti_tf');
    }

    public function updatedWhatsapp(string $value): void
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        $this->whatsapp = substr($digits, 0, 15);
    }

    public function getBuktiFileNameProperty(): ?string
    {
        if (! $this->bukti_tf instanceof TemporaryUploadedFile) {
            return null;
        }

        try {
            return $this->bukti_tf->getClientOriginalName();
        } catch (Throwable) {
            return 'file-terupload';
        }
    }

    public function submit(): void
    {
        $this->whatsapp = preg_replace('/\D+/', '', $this->whatsapp) ?? '';

        $validated = $this->validate([
            'nama' => 'required|string|min:3|max:120',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'umur' => 'required|integer|min:12|max:80',
            'whatsapp' => ['required', 'string', 'min:10', 'max:15', 'regex:/^[0-9]+$/'],
            'gereja_lokal' => ['required', 'string', Rule::in(GerejaOptionService::keys())],
            'bukti_tf' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ], [
            'bukti_tf.required' => 'Upload bukti transfer wajib diisi.',
            'whatsapp.regex' => 'Nomor telepon hanya boleh angka.',
            'whatsapp.min' => 'Nomor telepon minimal 10 digit.',
            'whatsapp.max' => 'Nomor telepon maksimal 15 digit.',
            'gereja_lokal.in' => 'Pilih gereja lokal yang tersedia.',
        ]);

        if (! $this->bukti_tf instanceof TemporaryUploadedFile) {
            $this->addError('bukti_tf', 'Upload bukti transfer gagal. Coba lagi.');

            return;
        }

        $path = $this->bukti_tf->store('bukti-tf', 'public');

        FellowRegistration::query()->create([
            'nama' => $validated['nama'],
            'gender' => $validated['gender'],
            'umur' => $validated['umur'],
            'whatsapp' => $validated['whatsapp'],
            'gereja_lokal' => $validated['gereja_lokal'],
            'bukti_tf_path' => $path,
        ]);

        $this->reset(['nama', 'gender', 'umur', 'whatsapp', 'gereja_lokal', 'bukti_tf']);
        $this->resetValidation();
        $this->submitted = true;

        $this->success('Pendaftaran berhasil diterima. Sampai jumpa di Limitless!', position: 'toast-bottom');
    }

    public function render()
    {
        $amount = (int) (Setting::getValue('transfer_amount', '150000') ?? '150000');

        return view('livewire.landing-page', [
            'bankName' => Setting::getValue('bank_name', 'BCA'),
            'bankAccount' => Setting::getValue('bank_account', '4660260451'),
            'bankHolder' => Setting::getValue('bank_holder', 'Vera Lisiani Bong'),
            'bankRemark' => trim((string) (Setting::getValue('bank_remark', '') ?? '')),
            'transferAmountLabel' => 'Rp '.number_format($amount, 0, ',', '.'),
        ]);
    }
}
