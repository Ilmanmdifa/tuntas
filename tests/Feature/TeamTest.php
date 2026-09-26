<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_teams(): void
    {
        $this->get('/teams')->assertRedirect('/login');
    }

    public function test_auth_can_create_team(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/teams', ['name' => 'Tim A'])->assertRedirect();
        $this->assertDatabaseHas('teams', ['name' => 'Tim A', 'owner_id' => $user->id]);
        $this->assertDatabaseHas('team_user', ['team_id' => 1, 'user_id' => $user->id, 'role' => 'owner']);
    }

    public function test_member_can_view_but_not_update(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $team = Team::create(['name' => 'Tim X', 'owner_id' => $owner->id]);
        $team->members()->attach($owner->id, ['role' => 'owner']);
        $team->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member)->get("/teams/{$team->id}")->assertOk();
        $this->actingAs($outsider)->get("/teams/{$team->id}")->assertForbidden();
        $this->actingAs($member)->post("/teams/{$team->id}/members", ['email' => $outsider->email])->assertForbidden();
        $this->actingAs($owner)->post("/teams/{$team->id}/members", ['email' => $outsider->email])->assertRedirect();
        $this->assertDatabaseHas('team_invites', ['team_id' => $team->id, 'email' => $outsider->email, 'status' => 'pending']);
    }

    public function test_decline_clears_bell(): void
    {
        $owner = User::factory()->create();
        $calon = User::factory()->create();
        $team = Team::create(['name' => 'Tim Y', 'owner_id' => $owner->id]);
        $team->members()->attach($owner->id, ['role' => 'owner']);

        // owner invite calon → TeamInvite pending + bell (notif database)
        $this->actingAs($owner)->post("/teams/{$team->id}/members", ['email' => $calon->email])->assertRedirect();

        // bell menyala: 1 belum dibaca
        $this->assertEquals(1, $calon->fresh()->unreadNotifications()->count());

        // ambil invite-nya, lalu decline sebagai calon
        $invite = \App\Models\TeamInvite::where('team_id', $team->id)->where('email', $calon->email)->first();
        $this->actingAs($calon)->post("/invites/{$invite->id}/decline")->assertStatus(302); // back() = 302

        // bell padam + status declined
        $this->assertEquals(0, $calon->fresh()->unreadNotifications()->count());
        $this->assertDatabaseHas('team_invites', ['id' => $invite->id, 'status' => 'declined']); // invite_id + status declined
    }
}
