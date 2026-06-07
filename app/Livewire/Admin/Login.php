<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password, 'is_admin' => true], $this->remember)) {
            $this->addError('email', 'These credentials do not match our records or you are not an admin.');
            return;
        }

        session()->regenerate();
        $this->redirect(route('admin.dashboard'));
    }

    public function render()
    {
        return view('livewire.admin.login');
    }
}
