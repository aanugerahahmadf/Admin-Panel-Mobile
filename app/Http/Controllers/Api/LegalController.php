<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Help;
use App\Models\LegalPage;
use App\Models\PrivacyPolicy;
use App\Models\TermsOfService;
use App\Models\WeddingDecorationPolicy;
use Illuminate\Http\JsonResponse;

class LegalController extends Controller
{
    protected function locale(): string
    {
        return app()->getLocale();
    }

    public function getTerms(): JsonResponse
    {
        $terms = TermsOfService::first();

        if (! $terms) {
            return response()->json([
                'success' => false,
                'message' => 'Terms & Conditions not available.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $terms->id,
                'title' => $terms->trans('title', $this->locale()),
                'content' => $terms->trans('content', $this->locale()),
                'updated_at' => $terms->updated_at?->format('d M Y'),
            ],
        ]);
    }

    public function getPrivacy(): JsonResponse
    {
        $privacy = PrivacyPolicy::first();

        if (! $privacy) {
            return response()->json([
                'success' => false,
                'message' => 'Privacy Policy not available.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $privacy->id,
                'title' => $privacy->trans('title', $this->locale()),
                'content' => $privacy->trans('content', $this->locale()),
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
                'message' => 'Wedding Decoration Policy not available.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $policy->id,
                'title' => $policy->trans('title', $this->locale()),
                'content' => $policy->trans('content', $this->locale()),
                'updated_at' => $policy->updated_at->format('d M Y'),
            ],
        ]);
    }

    public function getAbout(): JsonResponse
    {
        $about = LegalPage::where('slug', 'about')->first();

        if (! $about) {
            return response()->json([
                'success' => true,
                'data' => [
                    'title' => 'About WeddingApp',
                    'content' => 'Wedding Flowers Organizer is your ultimate companion.',
                    'mission' => null,
                    'owner' => config('app.name'),
                ],
            ]);
        }

        $aboutTitle = $about->trans('title', $this->locale());
        $aboutContent = $about->trans('content', $this->locale());

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $aboutTitle ?? 'About WeddingApp',
                'content' => is_array($aboutContent) ? ($aboutContent['text'] ?? $aboutContent) : $aboutContent,
                'mission' => is_array($aboutContent) ? ($aboutContent['mission'] ?? null) : null,
                'owner' => config('app.name'),
            ],
        ]);
    }

    public function getHelp(): JsonResponse
    {
        $help = Help::first();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $help?->trans('title', $this->locale()) ?? 'Help Center',
                'subtitle' => $help?->trans('subtitle', $this->locale()) ?? 'We are here to help you.',
                'faqs' => $help?->trans('faqs', $this->locale()) ?? [],
                'contact_options' => $help?->contact_options ?? null,
            ],
        ]);
    }
}
