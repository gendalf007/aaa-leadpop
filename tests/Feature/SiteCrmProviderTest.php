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

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://leadpop.ru']);
    }

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
        $this->actingAs($admin)->get(route('admin.sites.fields.create', $site))->assertOk()->assertSee('offerPrice');
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
        config(['services.drive_port.secret' => 'test-secret']);

        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        $this->assertSame(1, User::where('email', 'cs@danali.ru')->count());
        $site = Site::where('domain', 'm.leadpop.ru')->sole();
        $this->assertSame(
            ['clientName', 'clientPhone', 'comment', 'offerTitle', 'offerPrice'],
            $site->fields()->orderBy('order')->pluck('plex_key')->all()
        );
        // В API Драйв Порта обязателен только values.clientPhone.
        $this->assertSame(['clientPhone'], $site->fields()->where('required', true)->pluck('plex_key')->all());
        $this->assertTrue($site->isDrivePortConfigured());

        $admin = User::where('email', 'cs@danali.ru')->sole();

        $this->actingAs($admin)
            ->get('http://m.leadpop.ru/')
            ->assertOk()
            ->assertSee('name="_lead_type"', false)
            ->assertSee('Тест-драйв')
            ->assertSee('name="offer_title"', false)
            ->assertSee('name="offer_price"', false)
            ->assertDontSee('Не определён');
    }

    public function test_form_submit_stores_selected_lead_type(): void
    {
        config(['services.drive_port.secret' => '']); // без отправки наружу
        $this->seed(ProductionSeeder::class);
        $admin = User::where('email', 'cs@danali.ru')->sole();

        $this->actingAs($admin)
            ->post('http://m.leadpop.ru/submit', [
                'phone'       => '8 (999) 123-45-67',
                'offer_title' => 'Kia K5 2.5 AT, 2021',
                'offer_price' => '2365000',
                '_lead_type'  => 'credit',
            ])
            ->assertSessionHas('success');

        $lead = \App\Models\FormRequest::sole();
        $this->assertSame('credit', $lead->lead_type);
        $this->assertSame('79991234567', $lead->form_data['phone']);
        $this->assertSame('Kia K5 2.5 AT, 2021', $lead->form_data['offer_title']);

        $payload = app(\App\Services\DrivePort\DrivePortPayloadBuilder::class)->build($lead);
        $this->assertSame([
            'clientPhone' => '79991234567',
            'offerTitle'  => 'Kia K5 2.5 AT, 2021',
            'offerPrice'  => '2365000',
        ], $payload['values']);
    }

    public function test_seeder_brings_existing_site_fields_to_api_mapping(): void
    {
        $site = Site::create(['name' => 'Лид М', 'domain' => 'm.leadpop.ru', 'is_active' => true]);
        $site->fields()->create(['name' => 'name', 'plex_key' => 'clientName', 'label' => 'ФИО', 'type' => 'text', 'required' => true, 'order' => 1]);

        $this->seed(ProductionSeeder::class);

        $this->assertFalse((bool) $site->fields()->where('name', 'name')->value('required'));
        $this->assertSame(5, $site->fields()->count());
    }
}
