<?php

namespace App\Types;

use Exception;
use Illuminate\Support\Collection;
use Str;

class XoxoVoucher
{
    const KEY_PRODUCT_ID                        = 'productId';
    const KEY_NAME                              = 'name';
    const KEY_IMAGE_URL                         = 'imageUrl';
    const KEY_VALUE_DENOMINATIONS               = 'valueDenominations';
    const KEY_DESCRIPTION                       = 'description';
    const KEY_REDEMPTION_INSTRUCTIONS           = 'redemptionInstructions';
    const KEY_TERMS_AND_CONDITIONS_INSTRUCTIONS = 'termsAndConditionsInstructions';
    private int     $code;
    private string  $name;
    private string  $image;
    private string  $valueDenominations;
    private ?string $description;
    private ?string $instructions;
    private ?string $termConditions;
    private array   $requiredKeys   = [
        self::KEY_PRODUCT_ID,
        self::KEY_NAME,
        self::KEY_VALUE_DENOMINATIONS,
        self::KEY_IMAGE_URL,
        self::KEY_DESCRIPTION,
        self::KEY_REDEMPTION_INSTRUCTIONS,
        self::KEY_TERMS_AND_CONDITIONS_INSTRUCTIONS,
    ];
    private array   $requiredValues = [
        self::KEY_PRODUCT_ID,
        self::KEY_NAME,
        self::KEY_VALUE_DENOMINATIONS,
    ];

    /**
     * @throws Exception
     */
    public function __construct(array $item)
    {
        Collection::make($this->requiredKeys)->each(function($value) use ($item) {
            if (!array_key_exists($value, $item)) {
                throw new Exception('Invalid voucher. The ' . $value . ' is not exist');
            }
        });

        Collection::make($this->requiredValues)->each(function($value) use ($item) {
            if (empty($item[$value])) {
                throw new Exception('Invalid voucher. The ' . $value . ' is required');
            }
        });

        $this->code               = $item[self::KEY_PRODUCT_ID];
        $this->name               = $item[self::KEY_NAME];
        $this->image              = $item[self::KEY_IMAGE_URL];
        $this->valueDenominations = $item[self::KEY_VALUE_DENOMINATIONS];
        $this->description        = $item[self::KEY_DESCRIPTION] ?: null;
        $this->instructions       = $item[self::KEY_REDEMPTION_INSTRUCTIONS] ?: null;
        $this->termConditions     = $item[self::KEY_TERMS_AND_CONDITIONS_INSTRUCTIONS] ?: null;
    }

    public function code(): int
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function image(): string
    {
        return $this->image;
    }

    public function valueDenominations(): Collection
    {
        return Str::of($this->valueDenominations)->explode(',');
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function instructions(): ?string
    {
        return $this->instructions;
    }

    public function termsConditions(): ?string
    {
        return $this->termConditions;
    }
}
