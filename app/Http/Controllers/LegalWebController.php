<?php

namespace App\Http\Controllers;

use App\Models\PrivacyPolicy;
use App\Models\TermsOfService;

class LegalWebController extends Controller
{
    public function terms()
    {
        $terms = TermsOfService::first();

        return view('legal.legal-page', [
            'title' => $terms?->title ?? 'Perjanjian Pengguna',
            'sections' => $terms?->content ?? [],
            'updatedAt' => $terms?->updated_at,
        ]);
    }

    public function privacy()
    {
        $privacy = PrivacyPolicy::first();

        return view('legal.legal-page', [
            'title' => $privacy?->title ?? 'Kebijakan Privasi',
            'sections' => $privacy?->content ?? [],
            'updatedAt' => $privacy?->updated_at,
        ]);
    }
}
