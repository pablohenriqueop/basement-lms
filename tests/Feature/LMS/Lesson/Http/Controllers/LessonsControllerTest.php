<?php

namespace Tests\Feature\LMS\Lesson\Http\Controllers;

use FFMpeg\FFProbe;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use LMS\Lessons\Jobs\AzureStreamingEncode;
use LMS\Lessons\Models\Lesson;
use LMS\Modules\Models\Module;
use LMS\User\Models\User;
use Tests\TestCase;

class LessonsControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->seed();
    }

    public function testUserCanCreateALesson()
    {
        // Prepare
        $module = Module::factory()->create();

        $payload = [
            'type_id' => 1,
            'title' => 'dasdsadasdasdsa',
            'description' => 'dasdsadsa'
        ];
        // Act
        $this->actingAs($module->course->author);
        $response = $this->post(
            route('instructor-course-lesson-new', ['course' => $module->course, 'module' => $module]),
            $payload
        );

        // Assert
        $response->assertOk();
    }

    public function testUserShouldNotCreateALessonInACourseThatDoesNotBelongsToHim()
    {
        // Prepare
        $module1 = Module::factory()->create();
        $module2 = Module::factory()->create();

        $payload = [
            'type_id' => 1,
            'title' => 'dasdsadasdasdsa',
            'description' => 'dasdsadsa'
        ];
        // Act
        $this->actingAs($module1->course->author);
        $response = $this->post(
            route('instructor-course-lesson-new', ['course' => $module1->course, 'module' => $module2]),
            $payload
        );

        // Assert
        $response->assertStatus(422);
    }

    public function testUserCanUploadVideoAtLesson()
    {
        // Prepare
        Storage::fake();
        Bus::fake();

        $lesson = Lesson::factory()->create();
        $lesson->initVideoStream();

        $payload = [
            'video' => UploadedFile::fake()->create('fakelesson.mp4')
        ];


        // Act
        $this->actingAs($lesson->module->course->author);
        $response = $this->post(
            route('instructor-course-lesson-video-upload', ['course' => $lesson->module->course, 'module' => $lesson->module, 'lesson' => $lesson]),
            $payload
        );

        // Assert
        Bus::assertDispatched(AzureStreamingEncode::class);
        $response->assertOk();
    }
}
