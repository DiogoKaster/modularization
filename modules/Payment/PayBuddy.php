<?php

namespace Modules\Payment;

use Illuminate\Support\Number;
use Illuminate\Support\Str;

final class PayBuddy
{
    public function charge(string $token, int $amountInCents, string $statementDescription): array
    {
        $this->validateToken($token);

        $numberFormatter = Number::format($amountInCents, locale: 'en');

        return [
            'id' => (string) Str::uuid(),
            'amount_in_cents' => $amountInCents,
            'localized_amount' => $numberFormatter,
            'statement_description' => $statementDescription,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    public static function make(): self
    {
        return new self();
    }

    public static function validToken(): string
    {
        return (string) Str::uuid();
    }

    public static function invalidToken(): string
    {
        return substr(self::validToken(), -35);
    }

    protected function validateToken(string $token): void
    {
        if (! Str::isUuid($token)) {
            throw new \RuntimeException('The given payment token is not valid.');
        }
    }
}
