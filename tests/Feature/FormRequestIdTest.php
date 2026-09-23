<?php

namespace Tests\Feature;

use App\Models\FormRequest;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormRequestIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_ids_start_from_1001(): void
    {
        $site = Site::create(['name' => 'S', 'domain' => 's.test', 'is_active' => true]);

        $first = FormRequest::create(['site_id' => $site->id, 'form_data' => ['phone' => '79990000000']]);
        $second = FormRequest::create(['site_id' => $site->id, 'form_data' => ['phone' => '79990000001']]);

        $this->assertSame(1001, $first->id);
        $this->assertSame(1002, $second->id);
    }
}
