<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.public')]
#[Title('Admin Login')]
class extends Component {
    use Toast;

    public string $email = '';

    public string $password = '';

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('admin.registrations'), navigate: true);
        }
    }

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (! Auth::attempt($credentials, remember: true)) {
            $this->addError('email', 'Email atau password salah.');

            return;
        }

        session()->regenerate();

        $this->redirect(route('admin.registrations'), navigate: true);
    }
}; ?>

<div class="flex min-h-[100svh] items-center bg-[#e8eef2] px-5 py-10">
    <div class="mx-auto w-full max-w-md">
        <div class="mb-8 text-center">
            <p class="font-display text-4xl tracking-tight text-[#1a2433]">Limitless</p>
            <p class="mt-2 text-[#4a5a6d]">Login admin</p>
        </div>

        <div class="rounded-2xl border border-slate-300/50 bg-[#f7fafb] p-5 shadow-sm sm:p-7">
            <x-form wire:submit="login" class="space-y-4">
                <x-input
                    label="Email"
                    type="email"
                    wire:model="email"
                    placeholder="admin@gmail.com"
                    icon="o-envelope"
                    required
                />

                <x-password
                    label="Password"
                    wire:model="password"
                    placeholder="••••••••"
                    required
                />

                <x-button
                    label="Masuk"
                    type="submit"
                    class="btn-primary w-full"
                    icon="o-arrow-right-end-on-rectangle"
                    spinner="login"
                />
            </x-form>
        </div>

        <p class="mt-6 text-center text-sm text-[#4a5a6d]">
            <a href="{{ route('landing') }}" class="font-semibold text-teal-800 hover:underline" wire:navigate>Kembali ke landing</a>
        </p>
    </div>
</div>
