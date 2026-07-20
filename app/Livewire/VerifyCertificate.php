<?php

namespace App\Livewire;

use App\Models\Certificate;
use Livewire\Component;

class VerifyCertificate extends Component
{
    public string $certificate_id = '';
    public bool $searched = false;
    public ?Certificate $certificate = null;

    public function verify(): void
    {
        $this->validate([
            'certificate_id' => 'required|string|min:3',
        ]);

        $this->certificate = Certificate::where('certificate_id', trim($this->certificate_id))->first();
        $this->searched = true;
    }

    public function reset_form(): void
    {
        $this->reset(['certificate_id', 'searched', 'certificate']);
    }

    public function render()
    {
        return view('livewire.verify-certificate');
    }
}
