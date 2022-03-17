<?php
use PHPUnit\Framework\TestCase;
use FusionsPim\PhpEmailTokens\EmailToken;

class EmailTokenTest extends TestCase
{
    public function test_length_option(): void
    {
        $this->assertSame(24, (new EmailToken)->getTokenLength());
        $this->assertSame(32, (new EmailToken(['tokenLength' => 32]))->getTokenLength());
    }

    public function test_expiry_option(): void
    {
        $this->assertSame(15, (new EmailToken)->getExpiryMinutes());
        $this->assertSame(60, (new EmailToken(['expiryMinutes' => 60]))->getExpiryMinutes());
    }

    public function test_email_token_default(): void
    {
        $token = new EmailToken;

        $this->assertTrue(ctype_alnum($token->getEmailToken()));
        $this->assertSame(24, mb_strlen($token->getEmailToken()));
    }

    public function test_email_token_longer(): void
    {
        $length = 32;
        $token  = new EmailToken(['tokenLength' => $length]);

        $this->assertTrue(ctype_alnum($token->getEmailToken()));
        $this->assertSame($length, mb_strlen($token->getEmailToken()));
    }

    public function test_database_hash_default(): void
    {
        $this->assertSame(128, mb_strlen((new EmailToken)->getDatabaseHash()));
    }

    public function test_database_hash_longer(): void
    {
        $this->assertSame(128, mb_strlen((new EmailToken(['tokenLength' => 32]))->getDatabaseHash()));
    }

    public function test_still_valid_default(): void
    {
        $token = new EmailToken;

        $this->assertTrue($token->stillValid(new DateTimeImmutable('0 minutes ago')));
        $this->assertTrue($token->stillValid(new DateTimeImmutable('5 minutes ago')));
        $this->assertTrue($token->stillValid(new DateTimeImmutable('14 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTimeImmutable('15 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTimeImmutable('16 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTimeImmutable('20 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTimeImmutable('20 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTimeImmutable('2 weeks ago')));
    }

    public function test_still_valid_longer(): void
    {
        $token = new EmailToken(['expiryMinutes' => 30]);

        $this->assertTrue($token->stillValid(new DateTimeImmutable('0 minutes ago')));
        $this->assertTrue($token->stillValid(new DateTimeImmutable('10 minutes ago')));
        $this->assertTrue($token->stillValid(new DateTimeImmutable('20 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTimeImmutable('30 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTimeImmutable('40 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTimeImmutable('80 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTimeImmutable('3 days ago')));
    }

    public function test_hash_from_token_valid(): void
    {
        $tokenFromUser    = 'UAjERUW04rM2MGGt5w1Ysqh6';
        $hashFromDatabase = 'a565613c72da2d2a96023947c3369005149d4279de2d521094bd02de01327d0e4de3ad3d8ea4c257a94c156637baa13e8e56a4c9f54c90e857d333c996b14ec0';

        $this->assertSame($hashFromDatabase, (new EmailToken)->hashFromToken($tokenFromUser));
    }

    public function test_hash_from_token_invalid(): void
    {
        $tokenFromUser    = 'AAjERUW04rM2MGGt5w1Ysqh6'; // as above but changed first character
        $hashFromDatabase = 'a565613c72da2d2a96023947c3369005149d4279de2d521094bd02de01327d0e4de3ad3d8ea4c257a94c156637baa13e8e56a4c9f54c90e857d333c996b14ec0';

        $this->assertNotSame($hashFromDatabase, (new EmailToken)->hashFromToken($tokenFromUser));
    }

    public function test_inputs_and_outputs(): void
    {
        $token = new EmailToken;

        $this->assertSame($token->getDatabaseHash(), $token->hashFromToken($token->getEmailToken()));
    }
}
