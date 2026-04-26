<?php

namespace App\Console\Commands;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PublishArticles extends Command
{
    protected $signature = 'app:publish-articles';

    protected $description = 'Publishes articles that are scheduled but not yet published.';

    public function handle()
    {
        $this->info('Checking for articles to publish...');

        $scheduledArticles = Article::where('is_published', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->get();

        if ($scheduledArticles->isEmpty()) {
            $this->info('No articles found for publication.');

            return;
        }

        foreach ($scheduledArticles as $article) {
            $article->update(['is_published' => true]);
            $this->line("Article '{$article->title}' has been successfully published.");
        }

        $this->info("Successfully published {$scheduledArticles->count()} articles.");
    }
}
