<?php

namespace App\View\Components\vendor\health;

use Illuminate\View\Component;
use Illuminate\View\View;

class Logo extends Component
{
    public function render(): View
    {
        return view('health::logo');
    }
}
