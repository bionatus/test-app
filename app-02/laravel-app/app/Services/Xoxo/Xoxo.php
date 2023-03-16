<?php

namespace App\Services\Xoxo;

use App;
use App\Events\Service\Log as LogEvent;
use App\Exceptions\XoxoException;
use App\Models\ServiceToken;
use App\Models\ServiceToken\Scopes\ByServiceName;
use App\Models\ServiceToken\Scopes\ByTokenName;
use App\Models\XoxoVoucher as XoxoVoucherModel;
use App\Types\XoxoVoucher;
use Auth;
use Carbon\CarbonInterface;
use Config;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class Xoxo
{
    const SERVICE_NAME = 'xoxo';
    private ?string $domain;
    private ?string $clientId;
    private ?string $clientSecret;
    private int     $notifyReceiverEmail;
    private int     $notifyAdminEmail;
    private ?string $accessToken;
    private ?string $refreshToken;
    private bool    $generateAccessTokenCalled;

    /**
     * @throws XoxoException
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->domain              = Config::get('xoxo.domain');
        $this->clientId            = Config::get('xoxo.client_id');
        $this->clientSecret        = Config::get('xoxo.client_secret');
        $this->notifyAdminEmail    = Config::get('xoxo.notify_admin_email');
        $this->notifyReceiverEmail = 1;

        if (!$this->clientId || !$this->clientSecret) {
            throw new XoxoException('Xoxo credentials are required');
        }

        if (!$this->domain) {
            throw new XoxoException('Xoxo domain is required');
        }

        $this->refreshToken = $this->getRefreshToken();
        $this->accessToken  = $this->getAccessToken();

        $this->generateAccessTokenCalled = false;
    }

    /**
     * @throws XoxoException
     * @throws InvalidArgumentException
     */
    private function generateAccessToken(): string
    {
        $url     = $this->domain . '/v1/oauth/token/user';
        $payload = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        $response = Http::post($url, $payload);

        $request      = Collection::make(['method' => Request::METHOD_POST, 'url' => $url, 'payload' => $payload]);
        $responseData = Collection::make(['status' => $response->status(), 'content' => $response->body()]);
        LogEvent::dispatch(self::SERVICE_NAME, $request, $responseData, Auth::user());

        if ($response->failed()) {
            throw new XoxoException(json_encode(json_decode($response->body())));
        }
        $accessToken  = $response['access_token'];
        $refreshToken = $response['refresh_token'];

        $this->setToken(ServiceToken::ACCESS_TOKEN, $accessToken, Carbon::now()->addDays(15));
        $this->setToken(ServiceToken::REFRESH_TOKEN, $refreshToken, Carbon::now()->addDays(30));

        return $accessToken;
    }

    /**
     * @throws XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Exception
     */
    public function getRedeemMethods(): Collection
    {
        $url     = $this->domain . '/v1/oauth/api';
        $payload = [
            'query'     => 'plumProAPI.mutation.getVouchers',
            'tag'       => 'plumProAPI',
            'variables' => [
                'data' => [
                    'filters' => [
                        [
                            'key'   => 'country',
                            'value' => 'usa',
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withToken($this->accessToken)->post($url, $payload);

        $request      = Collection::make(['method' => Request::METHOD_POST, 'url' => $url, 'payload' => $payload]);
        $responseData = Collection::make(['status' => $response->status(), 'content' => $response->body()]);
        LogEvent::dispatch(self::SERVICE_NAME, $request, $responseData, Auth::user());

        if (!$this->generateAccessTokenCalled && $this->isAccessTokenError($response)) {
            $this->generateAccessToken();
            $this->generateAccessTokenCalled = true;
            $this->getRedeemMethods();
        }

        if ($response->failed()) {
            $this->handleError($response);
        }

        $responseData = $response['data']['getVouchers']['data'];
        $vouchers     = Collection::make([]);
        foreach ($responseData as $item) {
            if ($item['valueType'] === XoxoVoucherModel::TYPE_OPEN_VALUE) {
                continue;
            }

            $voucher = new XoxoVoucher($item);
            $vouchers->add($voucher);
        }

        return $vouchers;
    }

    /**
     * @throws XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function redeem(
        int $productId,
        int $quantity,
        float $denomination,
        string $poNumber,
        string $email,
        ?string $contact
    ): array {
        $url     = $this->domain . '/v1/oauth/api';
        $payload = [
            'query'     => 'plumProAPI.mutation.placeOrder',
            'tag'       => 'plumProAPI',
            'variables' => [
                'data' => [
                    'productId'           => $productId,
                    'quantity'            => $quantity,
                    'denomination'        => $denomination,
                    'poNumber'            => $poNumber,
                    'email'               => $email,
                    'contact'             => $contact,
                    'notifyReceiverEmail' => $this->notifyReceiverEmail,
                    'notifyAdminEmail'    => $this->notifyAdminEmail,
                ],
            ],
        ];

        $response = Http::withToken($this->accessToken)->post($url, $payload);

        $request      = Collection::make(['method' => Request::METHOD_POST, 'url' => $url, 'payload' => $payload]);
        $responseData = Collection::make(['status' => $response->status(), 'content' => $response->body()]);
        LogEvent::dispatch(self::SERVICE_NAME, $request, $responseData, Auth::user());

        if (!$this->generateAccessTokenCalled && $this->isAccessTokenError($response)) {
            $this->generateAccessToken();
            $this->generateAccessTokenCalled = true;
            $this->redeem($productId, $quantity, $denomination, $poNumber, $email, $contact);
        }

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response['data']['placeOrder']['data'];
    }

    /**
     * @throws XoxoException
     */
    public function handleError($response): void
    {
        if (isset($response['errors']) || isset($response['error'])) {
            throw new XoxoException(json_encode($response->json()));
        }
    }

    private function isAccessTokenError(Response $response): bool
    {
        $isAccessTokenError = isset($response['error']) && $response['error'] === 'invalid_token';
        $isDescriptionError = isset($response['error_description']) && $response['error_description'] === 'invalid/expired token';

        return $response->failed() && $isAccessTokenError && $isDescriptionError;
    }

    private function setToken(string $tokenName, string $value, CarbonInterface $expiredAt): void
    {
        ServiceToken::updateOrCreate([
            'service_name' => ServiceToken::XOXO,
            'token_name'   => $tokenName,
        ], [
            'value'      => $value,
            'expired_at' => $expiredAt,
        ]);
    }

    /**
     * @throws \App\Exceptions\XoxoException
     */
    private function getRefreshToken(): string
    {
        /** @var ServiceToken $refreshToken */
        $refreshToken = ServiceToken::scoped(new ByServiceName(ServiceToken::XOXO))
            ->scoped(new ByTokenName(ServiceToken::REFRESH_TOKEN))
            ->first();
        if (!$refreshToken) {
            throw new XoxoException('Refresh token is required');
        }

        return $refreshToken->value;
    }

    /**
     * @throws \App\Exceptions\XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function getAccessToken(): string
    {
        /** @var ServiceToken $accessToken */
        $accessToken          = ServiceToken::scoped(new ByServiceName(ServiceToken::XOXO))
            ->scoped(new ByTokenName(ServiceToken::ACCESS_TOKEN))
            ->first();
        $isInvalidAccessToken = !$accessToken || Carbon::now()->gte($accessToken->expired_at);

        return ($isInvalidAccessToken) ? $this->generateAccessToken() : $accessToken->value;
    }
}
