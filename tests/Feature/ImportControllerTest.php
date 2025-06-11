<?php

namespace Tests\Feature;

use App\Import\Jobs\ImportStartJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportControllerTest extends TestCase
{
    public function test_handle_upload_dispatches_import_start_job()
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('import.xlsx', 100);

        $response = $this->postJson('/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'progress_key']);

        Queue::assertPushed(ImportStartJob::class, function ($job) use ($file) {
            //проверяем, что файл существует
            return file_exists($job->path);
        });
    }
}
