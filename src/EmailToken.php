<?php
namespace WesHooper\PhpEmailTokens;

use Carbon\Carbon;
use DateTimeInterface;
use Tuupola\Base62;

class EmailToken
{
    private $expiryMinutes;
    private $hash;
    private $token;
    private $tokenLength;
    private $urlFormatter;

    public function __construct(array $options = [])
    {
        $this->expiryMinutes = (int) ($options['expiryMinutes'] ?? 15);
        $this->tokenLength   = (int) ($options['tokenLength'] ?? 24);
    }

    public function getDatabaseHash(): string
    {
        if (! isset($this->hash)) {
            $this->generate();
        }

        return $this->hash;
    }

    public function getEmailToken(): string
    {
        if (! isset($this->token)) {
            $this->generate();
        }

        return $this->token;
    }

    public function getTokenLength(): int
    {
        return $this->tokenLength;
    }

    public function getExpiryMinutes(): int
    {
        return $this->expiryMinutes;
    }

    public function hashFromToken(string $token): string
    {
        return hash('sha512', $token); // unsalted is fine, since brute forcing such random tokens unlikely
    }

    public function stillValid(DateTimeInterface $created): bool
    {
        return (Carbon::instance($created)->diffInMinutes() < $this->expiryMinutes);
    }

    private function generate(): void
    {
        $this->token = mb_substr((new Base62)->encode(random_bytes(128)), 0, $this->tokenLength);
        $this->hash  = $this->hashFromToken($this->token);
    }
}
