<?php

declare(strict_types=1);

namespace Moncine\Tests\Unit;

use Moncine\UserProfile;
use PHPUnit\Framework\TestCase;

final class UserProfileTest extends TestCase
{
    public function testDisplayNamePrefersPseudo(): void
    {
        $name = UserProfile::displayName([
            'pseudo' => 'CineFan',
            'prenom' => 'Jean',
            'nom' => 'Dupont',
        ]);
        $this->assertSame('CineFan', $name);
    }

    public function testDisplayNameCombinesPrenomAndNom(): void
    {
        $name = UserProfile::displayName([
            'prenom' => 'Jean',
            'nom' => 'Dupont',
        ]);
        $this->assertSame('Jean Dupont', $name);
    }

    public function testValidateIdentityRequiresAtLeastOneField(): void
    {
        $result = UserProfile::validateIdentityFields('', '', '');
        $this->assertIsString($result);
    }
}
