<?php

namespace Tests\Feature;

use App\Jobs\SendLeadToDrivePort;
use App\Jobs\SendLeadToPlexCrm;
use App\Models\FormRequest;
use App\Models\Site;
use App\Services\PlexCrm\PlexLeadDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DrivePortTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://drive-port.test/api/integrations/hooks/contact-form/drive-port';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.drive_port.url'        => self::URL,
            'services.drive_port.secret'     => 'test-secret',
            'services.drive_port.website_id' => 'Лид М',
        ]);
    }

    private function makeSite(array $attrs = []): Site
    {
        $site = Site::create(array_merge([
            'name'         => 'Лид М',
            'domain'       => 'm.leadpop.test',
            'is_active'    => true,
            'send_to_crm'  => true,
            'crm_provider' => 'drive_port',
        ], $attrs));

        $site->fields()->create(['name' => 'name', 'plex_key' => 'clientName', 'label' => 'ФИО', 'type' => 'text', 'required' => false, 'order' => 1]);
        $site->fields()->create(['name' => 'phone', 'plex_key' => 'clientPhone', 'label' => 'Телефон', 'type' => 'phone', 'required' => true, 'order' => 2]);
        $site->fields()->create(['name' => 'comment', 'plex_key' => 'comment', 'label' => 'Комментарий', 'type' => 'textarea', 'required' => false, 'order' => 3]);

        return $site;
    }

    private function makeLead(Site $site, array $attrs = []): FormRequest
    {
        return FormRequest::create(array_merge([
            'site_id'   => $site->id,
            'form_data' => ['name' => 'Иван', 'phone' => '79991234567', 'comment' => 'Заявка'],
            'source'    => 'form',
            'lead_type' => 'credit',
        ], $attrs));
    }

    public function test_sends_lead_in_drive_port_format(): void
    {
        Http::fake([self::URL => Http::response([
            'id' => 'abc-1', 'number' => 42, 'leadId' => 1, 'dealId' => 2, 'createdAt' => '2026-09-23T10:00:00Z',
        ], 201)]);

        $lead = $this->makeLead($this->makeSite());

        app(PlexLeadDispatcher::class)->dispatch($lead);

        Http::assertSent(function (Request $request) use ($lead) {
            return $request->url() === self::URL
                && $request->method() === 'POST'
                && $request->hasHeader('X-Contact-Form-Secret', 'test-secret')
                && ! str_contains($request->url(), 'secret=')
                && $request->data() === [
                    'type'       => 'credit',
                    'source'     => ['websiteId' => 'Лид М'],
                    'values'     => [
                        'clientName'  => 'Иван',
                        'clientPhone' => '79991234567',
                        'comment'     => 'Заявка',
                    ],
                    'externalId' => 'leadpop-' . $lead->id,
                ];
        });

        $lead->refresh();
        $this->assertSame('sent', $lead->crm_status);
        $this->assertSame('abc-1', $lead->crm_external_id);
        $this->assertSame(1, (int) $lead->crm_attempts);
    }

    public function test_lead_without_type_is_sent_as_unknown(): void
    {
        Http::fake([self::URL => Http::response(['id' => 'x'], 201)]);

        $lead = $this->makeLead($this->makeSite(), ['lead_type' => null]);

        app(PlexLeadDispatcher::class)->dispatch($lead);

        Http::assertSent(fn (Request $r) => $r['type'] === 'unknown');
        $this->assertSame('sent', $lead->refresh()->crm_status);
    }

    public function test_client_errors_fail_immediately_without_retry(): void
    {
        Http::fake([self::URL => Http::response(['message' => 'Forbidden'], 403)]);

        $lead = $this->makeLead($this->makeSite());

        app(PlexLeadDispatcher::class)->dispatch($lead);

        Http::assertSentCount(1);
        $lead->refresh();
        $this->assertSame('failed', $lead->crm_status);
        $this->assertStringContainsString('403', $lead->crm_last_error);
    }

    public function test_server_errors_are_retryable(): void
    {
        Http::fake([self::URL => Http::response('oops', 502)]);

        $lead = $this->makeLead($this->makeSite());

        $this->expectException(\App\Services\DrivePort\DrivePortException::class);

        try {
            (new SendLeadToDrivePort($lead->id))->handle(
                app(\App\Services\DrivePort\DrivePortClient::class),
                app(\App\Services\DrivePort\DrivePortPayloadBuilder::class),
            );
        } finally {
            // Статус не финализируется — это сделает failed() после исчерпания попыток.
            $this->assertNotSame('failed', $lead->refresh()->crm_status);
        }
    }

    public function test_dispatcher_picks_job_by_site_provider(): void
    {
        config(['services.plex_crm.token' => 'plex-token']);
        Queue::fake();

        $drive = $this->makeSite();
        $plex = $this->makeSite([
            'domain'             => 'plex.test',
            'crm_provider'       => 'plex',
            'plex_dealer_id'     => 1,
            'plex_website_id'    => 2,
            'allowed_lead_types' => ['credit'],
        ]);

        $dispatcher = app(PlexLeadDispatcher::class);
        $dispatcher->dispatch($this->makeLead($drive));
        $dispatcher->dispatch($this->makeLead($plex));

        Queue::assertPushed(SendLeadToDrivePort::class, 1);
        Queue::assertPushed(SendLeadToPlexCrm::class, 1);
    }

    public function test_site_without_secret_is_skipped(): void
    {
        config(['services.drive_port.secret' => null]);
        Queue::fake();

        $lead = $this->makeLead($this->makeSite());

        app(PlexLeadDispatcher::class)->dispatch($lead);

        Queue::assertNothingPushed();
        $this->assertSame('skipped', $lead->refresh()->crm_status);
    }

    public function test_already_sent_lead_is_not_resent_or_overwritten(): void
    {
        Queue::fake();

        $site = $this->makeSite(['send_to_crm' => false]);
        $lead = $this->makeLead($site);
        $lead->markCrmSent('abc-1');

        app(PlexLeadDispatcher::class)->dispatch($lead);

        Queue::assertNothingPushed();
        $this->assertSame('sent', $lead->refresh()->crm_status);
    }
}
