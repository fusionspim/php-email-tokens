<?php
use PHPUnit\Framework\TestCase;
use WesHooper\PhpPasswordWorkflow\PasswordToken;

class PasswordTokenTest extends TestCase
{
    public function test_length_option()
    {
        $this->assertEquals(24, (new PasswordToken)->getTokenLength());
        $this->assertEquals(32, (new PasswordToken(['tokenLength' => 32]))->getTokenLength());
    }

    public function test_expiry_option()
    {
        $this->assertEquals(15, (new PasswordToken)->getExpiryMinutes());
        $this->assertEquals(60, (new PasswordToken(['expiryMinutes' => 60]))->getExpiryMinutes());
    }

    public function test_email_token_default()
    {
        $token = new PasswordToken;

        $this->assertTrue(ctype_alnum($token->getEmailToken()));
        $this->assertEquals(24, strlen($token->getEmailToken()));
    }

    public function test_email_token_longer()
    {
        $length = 32;
        $token  = new PasswordToken(['tokenLength' => $length]);

        $this->assertTrue(ctype_alnum($token->getEmailToken()));
        $this->assertEquals($length, strlen($token->getEmailToken()));
    }

    public function test_database_hash_default()
    {
        $this->assertEquals(128, strlen((new PasswordToken)->getDatabaseHash()));
    }

    public function test_database_hash_longer()
    {
        $this->assertEquals(128, strlen((new PasswordToken(['tokenLength' => 32]))->getDatabaseHash()));
    }

    public function test_still_valid_default()
    {
        $token = new PasswordToken;

        $this->assertTrue($token->stillValid(new DateTime('0 minutes ago')));
        $this->assertTrue($token->stillValid(new DateTime('5 minutes ago')));
        $this->assertTrue($token->stillValid(new DateTime('14 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTime('15 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTime('16 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTime('20 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTime('20 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTime('2 weeks ago')));
    }

    public function test_still_valid_longer()
    {
        $token = new PasswordToken(['expiryMinutes' => 30]);

        $this->assertTrue($token->stillValid(new DateTime('0 minutes ago')));
        $this->assertTrue($token->stillValid(new DateTime('10 minutes ago')));
        $this->assertTrue($token->stillValid(new DateTime('20 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTime('30 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTime('40 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTime('80 minutes ago')));
        $this->assertFalse($token->stillValid(new DateTime('3 days ago')));
    }

    public function test_hash_from_token_valid()
    {
        $tokenFromUser    = 'UAjERUW04rM2MGGt5w1Ysqh6';
        $hashFromDatabase = 'a565613c72da2d2a96023947c3369005149d4279de2d521094bd02de01327d0e4de3ad3d8ea4c257a94c156637baa13e8e56a4c9f54c90e857d333c996b14ec0';

        $this->assertEquals($hashFromDatabase, (new PasswordToken)->hashFromToken($tokenFromUser));
    }

    public function test_hash_from_token_invalid()
    {
        $tokenFromUser    = 'AAjERUW04rM2MGGt5w1Ysqh6'; // as above but changed first character
        $hashFromDatabase = 'a565613c72da2d2a96023947c3369005149d4279de2d521094bd02de01327d0e4de3ad3d8ea4c257a94c156637baa13e8e56a4c9f54c90e857d333c996b14ec0';

        $this->assertNotEquals($hashFromDatabase, (new PasswordToken)->hashFromToken($tokenFromUser));
    }

    public function test_inputs_and_outputs()
    {
        $token = new PasswordToken;

        $this->assertEquals($token->getDatabaseHash(), $token->hashFromToken($token->getEmailToken()));
    }
}
