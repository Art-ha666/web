<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Services\Ai\AiBlogWriter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('blog:generate {--count= : How many posts to generate} {--provider= : openai|gemini|template}')]
#[Description('Generate AI blog draft articles from recent industry trends')]
class GenerateBlogPosts extends Command
{
    public function handle(AiBlogWriter $writer): int
    {
        $count = (int) ($this->option('count') ?: SiteSetting::current()->ai_blog_per_run ?: 1);
        $count = max(1, min($count, 5));

        try {
            $this->info("Generating {$count} blog draft(s) via ".$writer->generator($this->option('provider'))->key().'…');

            $created = $writer->generate($count, $this->option('provider'));
        } catch (\Throwable $e) {
            report($e);
            $this->error('AI generation failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($created->isEmpty()) {
            $this->warn('No new articles were created (topics may already be covered).');

            return self::SUCCESS;
        }

        foreach ($created as $article) {
            $this->line("  • {$article->title}  [{$article->status}]");
        }

        $this->info("Done - {$created->count()} article(s) created.");

        return self::SUCCESS;
    }
}
