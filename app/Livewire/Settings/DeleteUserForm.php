<?php

namespace App\Livewire\Settings;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        // count role admin if just 1, and its current user, block delete
        $adminCount = \App\Models\User::where('role', 'admin')->count();
        if ($adminCount <= 1 && Auth::user()->role === 'admin') {
            LivewireAlert::title('Terjadi Kesalahan')->text('Tidak bisa menghapus user karena hanya tinggal 1 admin.')->error()->show();
            return;
        }

        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}
