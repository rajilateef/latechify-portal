<?php

namespace App\Livewire;

use App\Models\NewsletterSubscriber;
use Livewire\Component;

class NewsletterForm extends Component
{
    public string $email = '';
    public bool $sent = false;

    public function submit(): void
    {
        $this->validate(['email' => 'required|email']);

        NewsletterSubscriber::firstOrCreate(['email' => $this->email]);

        $this->reset('email');
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.newsletter-form');
    }
}
