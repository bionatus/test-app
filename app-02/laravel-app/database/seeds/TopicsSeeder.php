<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Constants\Filesystem;
use App\Constants\MediaCollectionNames;
use App\Constants\RelationsMorphs;
use App\Models\Subject;
use App\Models\Subtopic;
use App\Models\Tool;
use App\Models\Topic;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Storage;

class TopicsSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const SOURCE_DISK = Filesystem::DISK_DEVELOPMENT_MEDIA;
    const TOOLS       = [
        'analog-gauges'  => [
            'name' => 'Gauges',
        ],
        'digital-gauges' => [
            'name' => 'Gauges',
        ],
        'monometer'      => [
            'name' => 'Monometer',
        ],
        'multimeter'     => [
            'name' => 'Multimeter/Voltmeter',
        ],
        'shsc'           => [
            'name' => 'SH/SC Measurements',
        ],
        'wires'          => [
            'name' => 'Jumper Wires',
        ],
    ];
    const TOOL_GROUPS = [
        'residential'             => ['multimeter', 'monometer', 'wires'],
        'residential_alternative' => ['multimeter', 'digital-gauges', 'wires'],
        'commercial'              => ['analog-gauges', 'multimeter', 'wires', 'shsc'],
    ];
    const SUBJECTS    = [
        'manuals'     => [
            'name' => 'Manuals & Diagrams',
        ],
        'residential' => [
            'name'        => 'Residential',
            'description' => 'Minisplits, HPs, ACs, Furnaces',
            'subtopics'   => [
                'mini'    => [
                    'name'  => 'Mini Split',
                    'tools' => 'residential_alternative',
                ],
                'heat'    => [
                    'name'  => 'Heat Pump',
                    'tools' => 'residential',
                ],
                'cooling' => [
                    'name'  => 'Cooling Only (AC)',
                    'tools' => 'residential',
                ],
                'furnace' => [
                    'name'  => 'Furnace',
                    'tools' => 'residential',
                ],
                'other'   => [
                    'name'  => 'Other',
                    'tools' => 'residential',
                ],
            ],
        ],
        'commercial'  => [
            'name'        => 'Commercial',
            'description' => '5-130+ Tons',
            'subtopics'   => [
                'rtu'   => [
                    'name'  => 'RTU',
                    'tools' => 'commercial',
                ],
                'gas'   => [
                    'name'  => 'Gas Electric',
                    'tools' => 'commercial',
                ],
                'heat'  => [
                    'name'  => 'Heat Pump',
                    'tools' => 'commercial',
                ],
                'split' => [
                    'name'  => 'Split/Buildup',
                    'tools' => 'commercial',
                ],
                'other' => [
                    'name'  => 'Other',
                    'tools' => 'commercial',
                ],
            ],
        ],
        'chillers'    => [
            'name'  => 'Chillers',
            'tools' => 'commercial',
        ],
    ];
    private Collection        $tools;
    private FilesystemAdapter $sourceDisk;

    public function __construct()
    {
        Relation::morphMap(RelationsMorphs::MAP);
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $this->sourceDisk = Storage::disk(self::SOURCE_DISK);
        $this->cleanTables();
        $this->tools = $this->insertTools();

        $this->insertTopics()->each(function(Topic $topic) {
            $topicSubject = $topic->subject;
            $rawTopic     = self::SUBJECTS[$topicSubject->slug] ?? [];

            $topicToolsGroup = $rawTopic['tools'] ?? null;
            if ($topicToolsGroup && ($toolsForTopic = $this->toolsFromGroup($topicToolsGroup))->isNotEmpty()) {
                $this->insertSubjectTools($topicSubject, $toolsForTopic);
            }
            $rawSubtopics = $rawTopic['subtopics'] ?? [];
            if (!$rawSubtopics) {
                return;
            }

            $this->insertSubtopics($topic, $rawSubtopics)->each(function(Subtopic $subtopic) use ($rawSubtopics) {
                $subtopicSubject = $subtopic->subject;
                $rawSubtopic     = $rawSubtopics[$this->baseSlug($subtopicSubject->slug)];

                $subtopicToolsGroup = $rawSubtopic['tools'] ?? null;
                if ($subtopicToolsGroup && ($toolsForSubtopic = $this->toolsFromGroup($subtopicToolsGroup))->isNotEmpty()) {
                    $this->insertSubjectTools($subtopicSubject, $toolsForSubtopic);
                }
            });
        });
    }

    private function cleanTables(): void
    {
        /** @var Subject $topicSubject */
        foreach (Subject::all() as $topicSubject) {
            if ($subtopic = $topicSubject->subtopic) {
                $subtopic->delete();
            }
            if ($topic = $topicSubject->topic) {
                $topic->delete();
            }
            $topicSubject->delete();
        }

        /** @var Tool $tool */
        foreach (Tool::all() as $tool) {
            $tool->delete();
        }
    }

    private function insertTools(): Collection
    {
        $tools = Collection::make();
        foreach (self::TOOLS as $slug => $toolData) {
            $name = $toolData['name'];
            $tool = Tool::create([
                'slug' => $slug,
                'name' => $name,
            ]);
            $tools->push($tool);

            $this->storeMedia(Filesystem::FOLDER_DEVELOPMENT_TOOLS_IMAGES, $tool);
        }

        return $tools;
    }

    private function insertTopics(): Collection
    {
        $collection = Collection::make();
        foreach (self::SUBJECTS as $slug => $raw) {
            $collection->push($this->insertTopic($slug, $raw));
        }

        return $collection;
    }

    private function insertTopic(string $slug, array $raw): Topic
    {
        $name    = $raw['name'];
        $subject = Subject::create([
            'slug' => $slug,
            'name' => $name,
            'type' => Subject::TYPE_TOPIC,
        ]);

        $this->storeMedia(Filesystem::FOLDER_DEVELOPMENT_TOPICS_IMAGES, $subject);

        /** @var Topic $topic */
        $topic = $subject->topic()->create([
            'description' => $raw['description'] ?? null,
        ]);

        return $topic;
    }

    /**
     * @param string             $directory
     * @param Model|Subject|Tool $model
     */
    private function storeMedia(string $directory, Model $model): void
    {
        $file = $directory . "{$this->baseSlug($model->slug)}.png";

        if (!$this->sourceDisk->exists($file)) {
            return;
        }

        try {
            $model->addMediaFromDisk($file, self::SOURCE_DISK)
                ->preservingOriginal()
                ->toMediaCollection(MediaCollectionNames::IMAGES);
        } catch (Exception $exception) {
            // Silently ignored
        }
    }

    private function insertSubtopics(Topic $topic, array $rawSubtopics): Collection
    {
        $subtopics = Collection::make();
        foreach ($rawSubtopics as $slug => $rawSubtopic) {
            $subtopics->push($this->insertSubtopic($topic, $slug, $rawSubtopic));
        }

        return $subtopics;
    }

    /**
     * @return Subtopic|Model
     */
    private function insertSubtopic(Topic $topic, string $slug, array $rawSubtopic): Subtopic
    {
        $subject = Subject::create([
            'slug' => $slug,
            'name' => $rawSubtopic['name'],
            'type' => Subject::TYPE_SUBTOPIC,
        ]);

        $directory = Filesystem::FOLDER_DEVELOPMENT_TOPICS_IMAGES . $this->baseSlug($topic->subject->slug) . '/';

        $this->storeMedia($directory, $subject);

        return $subject->subtopic()->create([
            'topic_id' => $topic->getKey(),
        ]);
    }

    private function insertSubjectTools(Subject $subject, Collection $tools): void
    {
        $subject->tools()->sync($tools->modelKeys());
    }

    private function toolsFromGroup(string $group): Collection
    {
        $slugsInGroup = self::TOOL_GROUPS[$group] ?? [];

        return $this->tools->filter(fn(Tool $tool) => in_array($tool->getRouteKey(), $slugsInGroup));
    }

    private function baseSlug(string $slug): string
    {
        $hyphenPosition = strpos($slug, '-');

        if (!$hyphenPosition) {
            return $slug;
        }

        if (!is_numeric(substr($slug, $hyphenPosition + 1))) {
            return $slug;
        }

        return substr($slug, 0, $hyphenPosition);
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
