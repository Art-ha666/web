<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

/**
 * Zero-dependency fallback "generator". When no AI provider key is configured
 * (and in tests) this produces a clean, valid draft from the requested topic so
 * the whole pipeline keeps working without external calls or secrets.
 */
class TemplateGenerator implements AiTextGenerator
{
    public function key(): string
    {
        return 'template';
    }

    public function configured(): bool
    {
        return true;
    }

    public function generate(string $system, string $user): string
    {
        preg_match('/PRIMARY_TOPIC:\s*(.+)/', $user, $m);
        $topic = trim($m[1] ?? 'Modern software delivery');
        $title = Str::limit($topic, 68, '');
        $lower = Str::lower(Str::limit($topic, 80, ''));

        $body = '<h2>Why this matters</h2>'
            ."<p>{$topic} keeps coming up in conversations with engineering leaders - and for good reason. The teams that stay ahead treat it as a first-class concern rather than an afterthought.</p>"
            .'<h2>How we approach it at AKH</h2>'
            ."<p>At AKH Solutions we make the early decisions, document the trade-offs, and own the reliability. When it comes to {$lower}, that means:</p>"
            .'<ul><li>Architecting for change before writing the first line of code.</li><li>Shipping in two-week increments with a working demo every Friday.</li><li>Wiring in observability and rollback from day one.</li></ul>'
            .'<h2>Takeaways</h2>'
            ."<p>Done well, {$lower} is a durable advantage - not a one-off project. If you are weighing it for your own roadmap, we are happy to compare notes.</p>";

        return (string) json_encode([
            'title' => $title,
            'excerpt' => 'A practical engineering note on '.$lower.' from the AKH Solutions team.',
            'body_html' => $body,
            'tags' => ['Engineering', 'Delivery'],
            'reading_time' => 6,
            'seo_title' => Str::limit($title, 58, ''),
            'seo_description' => 'AKH Solutions on '.$lower.' - practical, production-focused engineering.',
        ]);
    }
}
