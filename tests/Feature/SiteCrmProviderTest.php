<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteCrmProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_switch_site_to_drive_port(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $site = Site::create(['name' => 'S', 'domain' => 's.test', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.sites.create'))->assertOk()->assertSee('Драйв Порт');
        $this->actingAs($admin)->get(route('admin.sites.edit', $site))->assertOk()->assertSee('name="crm_provider"', false);

        $this->actingAs($admin)
            ->put(route('admin.sites.update', $site), [
                'name'         => 'S',
                'domain'       => 's.test',
                'is_active'    => 1,
                'send_to_crm'  => 1,
                'crm_provider' => 'drive_port',
            ])
            ->assertRedirect(route('admin.sites.index'));

        $this->assertSame('drive_port', $site->refresh()->crm_provider);
        $this->actingAs($admin)->get(route('admin.sites.show', $site))->assertOk()->assertSee('Драйв Порт');
    }

    public function test_unknown_provider_is_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $site = Site::create(['name' => 'S', 'domain' => 's.test', 'is_active' => true]);

        $this->actingAs($admin)
            ->put(route('admin.sites.update', $site), [
                'name' => 'S', 'domain' => 's.test', 'crm_provider' => 'amocrm',
            ])
            ->assertSessionHasErrors('crm_provider');
    }

    public function test_production_seeder_is_idempotent_and_form_has_lead_type_dropdown(): void
    {
        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        $this->assertSame(1, User::where('email', 'cs@danali.ru')->count());
        $site = Site::where('domain', 'm.leadpop.ru')->sole();
        $this->assertSame(3, $site->fields()->count());
        $this->assertTrue($site->isDrivePortConfigured() || config('services.drive_port.secret') === null);

        $admin = User::where('email', 'cs@danali.ru')->sole();

        $this->actingAs($admin)
            ->get('http://m.leadpop.ru/')
            ->assertOk()
            ->assertSee('name="_lead_type"', false)
            ->assertSee('Тест-драйв')
            ->assertDontSee('Не определён');
    }

    public function test_form_submit_stores_selected_lead_type(): void
    {
        config(['services.drive_port.secret' => null]); // без отправки наружу
        $this->seed(ProductionSeeder::class);
        $admin = User::where('email', 'cs@danali.ru')->sole();

        $this->actingAs($admin)
            ->post('http://m.leadpop.ru/submit', [
                'name'       => 'Иван',
                'phone'      => '8 (999) 123-45-67',
                '_lead_type' => 'credit',
            ])
            ->assertSessionHas('success');

        $lead = \App\Models\FormRequest::sole();
        $this->assertSame('credit', $lead->lead_type);
        $this->assertSame('79991234567', $lead->form_data['phone']);
    }
}
