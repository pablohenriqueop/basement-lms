<?php


namespace Tests\Feature\LMS\Course\Http\Controllers;


use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use LMS\User\Models\User;
use LMS\Courses\Models\Course;
use Tests\TestCase;

class ViewControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->seed();
    }

    public function testUserCanViewCoursesPage() {
        // Prepare
        Storage::fake();
        $course = Course::factory()->create();
        $course->addMedia(storage_path('app/tests/doge.jpeg'))
            ->preservingOriginal()
            ->toMediaCollection();

        // Act
        $this->actingAs($course->author);
        $response = $this->get(route('instructor-courses'));

        // Assert
        $response->assertOk()
            ->assertSee($course->title);
    }

    public function testUserCanViewCoursesCreationPage() {
        // Prepare
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);
        $response = $this->get(route('instructor-courses-new'));

        // Assert
        $response->assertOk()
            ->assertSee('Novo Curso');
    }

    public function testUserCanViewCourseManagementPage() {
        // Prepare
        Storage::fake();
        $course = Course::factory()->create();
        $course->addMedia(storage_path('app/tests/doge.jpeg'))
            ->preservingOriginal()
            ->toMediaCollection();


        // Act
        $this->actingAs($course->author);
        $response = $this->get(route('instructor-course-manage', ['course' => $course->id]));

        // Assert
        $response->assertOk()
            ->assertSee($course->title);
    }
}