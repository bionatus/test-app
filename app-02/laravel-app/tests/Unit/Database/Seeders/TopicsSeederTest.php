<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Filesystem;
use App\Models\Subject;
use App\Models\Tool;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\TopicsSeeder;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Storage;
use Tests\TestCase;

class TopicsSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(TopicsSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_stores_topics_and_subtopics()
    {
        Storage::fake(Filesystem::DISK_DEVELOPMENT_MEDIA);
        $seeder = new TopicsSeeder();
        $seeder->run();

        foreach (TopicsSeeder::TOOLS as $toolData) {
            $tool = Tool::where('name', $toolData['name'])->first();
            $this->assertNotNull($tool);
        }

        foreach (TopicsSeeder::SUBJECTS as $topicData) {
            $topicSubject = Subject::where('name', $topicData['name'])->first();
            $this->assertNotNull($topicSubject);
            $this->assertNotNull($topic = $topicSubject->topic);
            $this->assertEquals($topicData['description'] ?? null, $topic->description);
            foreach ($topicData['subtopics'] ?? [] as $subtopicData) {
                $subtopicSubject = Subject::where('name', $subtopicData['name'])
                    ->whereHas('subtopic', function(Builder $builder) use ($topic) {
                        $builder->where('topic_id', $topic->getKey());
                    })
                    ->first();
                $this->assertNotNull($subtopicSubject);
                $this->assertNotNull($subtopic = $subtopicSubject->subtopic);
                $this->assertEquals($topic->getKey(), $subtopic->topic_id);
            }

            $toolsGroup = $topicData['tools'] ?? null;
            $toolsSlugs = TopicsSeeder::TOOL_GROUPS[$toolsGroup] ?? [];
            $this->assertCount(count($toolsSlugs), $topicSubject->tools);
            foreach ($toolsSlugs as $toolSlug) {
                $this->assertNotNull($topicSubject->tools()->where('slug', $toolSlug)->first());
            }
        }
    }
}
