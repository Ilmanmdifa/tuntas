<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Demo seed untuk reviewer portfolio (v1 Tuntas).
     * Akun: demo@tuntas.test + budi@tuntas.test (password: "password").
     * Isi: 1 team + todos personal/team berisi (priority, due_date overdue, description, assigned).
     */
    public function run(): void
    {
        $owner = User::factory()->create([
            'name' => 'Demo Owner',
            'email' => 'demo@tuntas.test',
        ]);
        $member = User::factory()->create([
            'name' => 'Budi Member',
            'email' => 'budi@tuntas.test',
        ]);

        $team = Team::create(['name' => 'Tim Demo', 'owner_id' => $owner->id]);
        $team->members()->attach($owner->id, ['role' => 'owner']);
        $team->members()->attach($member->id, ['role' => 'member']);

        Todo::create([
            'title' => 'Siapkan materi kickoff',
            'description' => 'Slide + agenda, maksimal 5 halaman.',
            'priority' => 'high',
            'due_date' => now()->addDay()->toDateString(),
            'is_done' => false,
            'user_id' => $owner->id,
        ]);
        Todo::create([
            'title' => 'Bayar hosting (contoh overdue)',
            'priority' => 'medium',
            'due_date' => now()->subDay()->toDateString(),
            'is_done' => false,
            'user_id' => $owner->id,
        ]);
        $team->todos()->create([
            'title' => 'Desain logo Tuntas',
            'description' => 'Varian monogram T, latar gelap.',
            'priority' => 'high',
            'due_date' => now()->addDays(3)->toDateString(),
            'is_done' => false,
            'user_id' => $owner->id,
            'assigned_to' => $member->id,
        ]);
        Todo::create([
            'title' => 'Baca ADR di README',
            'is_done' => true,
            'user_id' => $member->id,
        ]);
    }
}
