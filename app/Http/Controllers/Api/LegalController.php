<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use App\Models\PrivacyPolicy;
use App\Models\TermsOfService;
use App\Models\WeddingDecorationPolicy;
use Illuminate\Http\JsonResponse;

class LegalController extends Controller
{
    public function getTerms(): JsonResponse
    {
        $terms = TermsOfService::first();

        if (! $terms) {
            return response()->json([
                'success' => false,
                'message' => 'Syarat & Ketentuan belum tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $terms->id,
                'title' => $terms->title,
                'content' => $terms->content,
            ],
        ]);
    }

    public function getPrivacy(): JsonResponse
    {
        $privacy = PrivacyPolicy::first();

        if (! $privacy) {
            return response()->json([
                'success' => false,
                'message' => 'Kebijakan Privasi belum tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $privacy->id,
                'title' => $privacy->title,
                'content' => $privacy->content,
                'updated_at' => $privacy->updated_at->format('d M Y'),
            ],
        ]);
    }

    public function getWeddingDecorationPolicy(): JsonResponse
    {
        $policy = WeddingDecorationPolicy::first();

        if (! $policy) {
            return response()->json([
                'success' => false,
                'message' => 'Kebijakan Wedding Flowers Decorasi belum tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $policy->id,
                'title' => $policy->title,
                'content' => $policy->content,
                'updated_at' => $policy->updated_at->format('d M Y'),
            ],
        ]);
    }

    public function getAbout(): JsonResponse
    {
        // Assuming about info is stored in AppSettings or a LegalPage with slug 'about'
        $about = LegalPage::where('slug', 'about')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $about->title ?? 'About WeddingApp',
                'content' => $about->content['text'] ?? 'Wedding Flowers Organizer is your ultimate companion.',
                'mission' => $about->content['mission'] ?? null,
                'owner' => config('app.name'),
            ],
        ]);
    }

    public function getHelp(): JsonResponse
    {
        $help = LegalPage::where('slug', 'help')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $help->title ?? 'Help Center',
                'subtitle' => $help->content['subtitle'] ?? 'Our team is ready to assist you',
                'faqs' => $help->content['faqs'] ?? [],
                'contact_options' => $help->content['contacts'] ?? null,
            ],
        ]);
    }
}
