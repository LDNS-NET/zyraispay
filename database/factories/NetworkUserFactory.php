<?php

namespace Database\Factories;

use App\Models\Tenants\NetworkUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NetworkUserFactory extends Factory
{
    protected $model = NetworkUser::class;

    public function definition()
    {
        return [
            'account_number' => $this->generateAccountNumber(),
            'full_name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'password' => bcrypt('password'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'location' => $this->faker->city(),
            'package_id' => 1,
            'type' => 'hotspot',
            'registered_at' => now(),
            'expires_at' => now()->addDays(30),
            'online' => false,
        ];
    }

    private function generateAccountNumber()
    {
        // Example: 10-digit random number prefixed with 'NU'
        return 'NU' . mt_rand(1000000000, 9999999999);
    }
}
