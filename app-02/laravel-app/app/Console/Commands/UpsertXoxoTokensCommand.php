<?php

namespace App\Console\Commands;

use App;
use App\Exceptions\XoxoException;
use App\Models\ServiceToken;
use App\Models\ServiceToken\Scopes\ByServiceName;
use App\Models\ServiceToken\Scopes\ByTokenName;
use App\Services\Xoxo\Xoxo;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpsertXoxoTokensCommand extends Command
{
    protected $signature   = 'xoxo:upsert-tokens {--refresh-token=}';
    protected $description = 'Upsert Xoxo Tokens';

    /**
     * @throws \App\Exceptions\XoxoException
     */
    public function handle()
    {
        $refreshToken = $this->option('refresh-token');
        if ($refreshToken) {
            ServiceToken::updateOrCreate([
                'service_name' => ServiceToken::XOXO,
                'token_name'   => ServiceToken::REFRESH_TOKEN,
            ], [
                'value'      => $refreshToken,
                'expired_at' => Carbon::now()->addDays(30),
            ]);
        }

        /** @var ServiceToken $serviceToken */
        $serviceToken = ServiceToken::scoped(new ByServiceName(ServiceToken::XOXO))
            ->scoped(new ByTokenName(ServiceToken::REFRESH_TOKEN))
            ->first();
        if (!$serviceToken) {
            throw new XoxoException('Refresh token is required');
        }

        if (Carbon::now()->addWeek()->greaterThanOrEqualTo($serviceToken->expired_at)) {
            App::make(Xoxo::class);
        }
    }
}
