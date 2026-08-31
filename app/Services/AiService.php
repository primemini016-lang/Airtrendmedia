<?php
namespace App\Services;

use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AiService
{
    public function text(User $user, string $prompt, int $maxTokens = 2000): string
    {
        $this->ensureEnabled();
        $key = (string) SiteSetting::get('openai_api_key', '');
        if (!$key) throw new RuntimeException('AI is not configured by the administrator.');
        $model = (string) SiteSetting::get('openai_model', 'gpt-4.1-mini');
        $response = Http::withToken($key)->acceptJson()->timeout(90)->post('https://api.openai.com/v1/responses', [
            'model' => $model,
            'input' => $prompt,
            'max_output_tokens' => $maxTokens,
        ]);
        if (!$response->successful()) throw new RuntimeException('AI request failed: HTTP '.$response->status().'.');
        $json = $response->json();
        $text = $json['output_text'] ?? null;
        if (!$text && !empty($json['output'])) {
            foreach ($json['output'] as $out) foreach (($out['content'] ?? []) as $part) {
                if (isset($part['text'])) $text .= $part['text'];
            }
        }
        $text = trim((string)$text);
        if ($text === '') throw new RuntimeException('AI returned an empty response.');
        return $text;
    }

    public function image(User $user, string $prompt): string
    {
        $this->ensureEnabled();
        $price = (float) SiteSetting::get('ai_image_price', 0.20);
        $key = (string) SiteSetting::get('openai_api_key', '');
        if (!$key) throw new RuntimeException('AI is not configured by the administrator.');

        return DB::transaction(function () use ($user, $prompt, $price, $key) {
            DB::table('ai_credit_wallets')->insertOrIgnore([
                'user_id' => $user->id, 'credits' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $wallet = DB::table('ai_credit_wallets')->where('user_id', $user->id)->lockForUpdate()->first();
            $credits = (float)($wallet->credits ?? 0);
            if ($credits + 1e-9 < $price) throw new RuntimeException('Insufficient AI credits. Please add AI credits before generating an image.');

            $model = (string) SiteSetting::get('ai_image_model', 'gpt-image-1');
            $response = Http::withToken($key)->acceptJson()->timeout(180)->post('https://api.openai.com/v1/images/generations', [
                'model' => $model, 'prompt' => $prompt, 'size' => '1024x1024', 'n' => 1,
            ]);
            if (!$response->successful()) throw new RuntimeException('AI image generation failed: HTTP '.$response->status().'.');
            $data = $response->json('data.0') ?? [];
            $path = null;
            if (!empty($data['b64_json'])) {
                $binary = base64_decode($data['b64_json'], true);
                if ($binary === false) throw new RuntimeException('AI returned invalid image data.');
                $path = 'ai/'.date('Y/m').'/'.str()->uuid().'.png';
                Storage::disk('public')->put($path, $binary);
            } elseif (!empty($data['url'])) {
                $image = Http::timeout(60)->get($data['url']);
                if (!$image->successful()) throw new RuntimeException('Generated image could not be downloaded.');
                $path = 'ai/'.date('Y/m').'/'.str()->uuid().'.png';
                Storage::disk('public')->put($path, $image->body());
            } else {
                throw new RuntimeException('AI did not return usable image data.');
            }

            DB::table('ai_credit_wallets')->where('user_id',$user->id)->update(['credits'=>$credits-$price,'updated_at'=>now()]);
            DB::table('ai_credit_transactions')->insert([
                'user_id'=>$user->id,'credits'=>-$price,'type'=>'spend',
                'reference'=>'AI-'.str()->upper(str()->random(12)),
                'description'=>'AI image generation','provider'=>'openai','created_at'=>now(),'updated_at'=>now(),
            ]);
            return storage_asset($path);
        });
    }

    private function ensureEnabled(): void
    {
        if (!(bool) SiteSetting::get('ai_enabled', true)) throw new RuntimeException('AI features are disabled by the administrator.');
    }
}
