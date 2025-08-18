<?php

namespace App\Tests;

use App\Entity\Key;
use PHPUnit\Framework\TestCase;

class KeyTest extends TestCase
{
    public function testNewKeyHasCorrectDefaults(): void
    {
        // 1. Preparation
        $key = new Key();
        $key->setKeyIdentifier('TEST-ID-123');

        // This simulates what Doctrine's PrePersist listener does
        $key->setCreatedAtValue();

        // 2. Action & Assertion
        // Check if the constructor correctly set the default status
        $this->assertSame('active', $key->getStatus(), 'A new key should have the status "active".');

        // Check if the PrePersist callback correctly set the createdAt timestamp
        $this->assertInstanceOf(\DateTimeImmutable::class, $key->getCreatedAt(), 'A new key should have a createdAt timestamp.');
    }

    public function testCanChangeStatus(): void
    {
        // 1. Preparation
        $key = new Key();

        // 2. Action
        $key->setStatus('inactive');

        // 3. Assertion
        $this->assertSame('inactive', $key->getStatus(), 'The status should be changeable to "inactive".');
    }
}
