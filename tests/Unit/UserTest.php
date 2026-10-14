<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_role_and_status_helpers_use_exact_supported_values(): void
    {
        $admin = new User(['role' => 'admin', 'status' => 'aktif']);
        $blocked = new User(['role' => 'user', 'status' => 'diblokir']);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isActive());
        $this->assertFalse($blocked->isAdmin());
        $this->assertFalse($blocked->isActive());
        $this->assertFalse((new User)->isAdmin());
        $this->assertFalse((new User)->isActive());
    }

    public function test_password_cast_hashes_plaintext_and_preserves_existing_hash(): void
    {
        $user = new User(['password' => 'testpassword']);
        $this->assertNotSame('testpassword', $user->password);
        $this->assertTrue(Hash::check('testpassword', $user->password));
        $existingHash = $user->password;
        $user->password = $existingHash;
        $this->assertSame($existingHash, $user->password);
    }
}
