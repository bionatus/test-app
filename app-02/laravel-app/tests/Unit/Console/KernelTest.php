<?php

namespace Tests\Unit\Console;

use App;
use App\Jobs\SendOrderPendingApprovalReminder;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Tests\TestCase;

class KernelTest extends TestCase
{
    const COMMANDS = 'commands';
    const JOBS     = 'jobs';
    private Collection $groupedScheduledTasks;

    public function setUp(): void
    {
        parent::setUp();

        $schedule = App::make(Schedule::class);

        $this->groupedScheduledTasks = Collection::make();
        $tasks                       = Collection::make($schedule->events());

        $commands = $tasks->filter(function(Event $command) {
            return !is_a($command, CallbackEvent::class);
        });
        $jobs     = $tasks->filter(function(Event $item) {
            return is_a($item, CallbackEvent::class);
        });

        $this->groupedScheduledTasks->put(self::COMMANDS, $commands);
        $this->groupedScheduledTasks->put(self::JOBS, $jobs);
    }

    private function ourCommands(): Collection
    {
        return Collection::make([
            'export:invoices'           => [
                ['expression' => '0 7 8 * *', 'timezone' => 'UTC', 'times' => 1],
            ],
            'export:invoices-customers' => [
                ['expression' => '0 7 8 * *', 'timezone' => 'UTC', 'times' => 1],
            ],
            'xoxo:upsert-tokens'        => [
                ['expression' => '0 0 * * 0', 'timezone' => 'UTC', 'times' => 1],
            ],
            'xoxo:upsert-vouchers'      => [
                ['expression' => '0 1 * * *', 'timezone' => 'UTC', 'times' => 1],
            ],
        ]);
    }

    private function ourJobs(): Collection
    {
        return Collection::make([
            SendOrderPendingApprovalReminder::class => [
                ['expression' => '0 7 * * *', 'timezone' => 'America/Adak', 'times' => 1],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Anchorage', 'times' => 1],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Los_Angeles', 'times' => 2],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Phoenix', 'times' => 1],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Chicago', 'times' => 1],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Denver', 'times' => 1],
                ['expression' => '0 7 * * *', 'timezone' => 'America/New_York', 'times' => 1],
            ],
        ]);
    }

    /**
     * @test
     * @dataProvider registeredTasksProvider
     */
    public function registered_tasks_should_be_tested(string $taskType, Collection $ourTasks)
    {
        $failed = Collection::make();

        $this->groupedScheduledTasks->get($taskType)->each(function(Event $task) use ($taskType, $ourTasks, $failed) {
            $taskName = $this->getTaskName($task, $taskType);
            if (!$ourTasks->has($taskName)) {
                $failed->push("Failed asserting that $taskName is tested.");

                return;
            }

            $expression     = $task->getExpression();
            $timezone       = $task->timezone;
            $ourExpressions = Collection::make($ourTasks->get($taskName));
            switch ($taskType) {
                case self::COMMANDS:
                    $taskFieldName = 'command';
                    break;
                case self::JOBS:
                default:
                    $taskFieldName = 'description';
            }

            $times = $this->groupedScheduledTasks->get($taskType)
                ->groupBy($taskFieldName)
                ->get($task->$taskFieldName)
                ->groupBy('expression')
                ->get($expression)
                ->groupBy('timezone')
                ->get($timezone)
                ->count();

            if (!$ourExpressions->contains(function($value) use ($expression, $timezone, $times) {
                return $value['expression'] == $expression && $value['timezone'] == $timezone && $value['times'] == $times;
            })) {
                $failed->push("Failed asserting that $taskName has schedule $expression with timezone $timezone $times times.");
            };
        });

        $messages = $failed->implode("\n");
        $this->assertEmpty($failed, $messages);
    }

    private function getTaskName(Event $task, string $taskType): ?string
    {
        if (self::COMMANDS == $taskType) {
            return Collection::make(explode(' ', $task->command))->last();
        }

        if (self::JOBS == $taskType) {
            return $task->description;
        }

        return null;
    }

    public function registeredTasksProvider(): array
    {
        return [
            [
                'taskType' => self::COMMANDS,
                'ourTasks' => $this->ourCommands(),
            ],
            [
                'taskType' => self::JOBS,
                'ourTasks' => $this->ourJobs(),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider tasksProvider
     */
    public function intended_tasks_should_be_registered(string $taskType, string $taskName, array $expressions)
    {
        $tasks = $this->groupedScheduledTasks->get($taskType)->filter(function(Event $task) use ($taskType, $taskName) {
            return $this->getTaskName($task, $taskType) == $taskName;
        });
        $this->assertGreaterThan(0, $tasks->count(), "Schedule $taskName for group $taskType does not exist");

        foreach ($expressions as $schedule) {
            $this->assertSame($schedule['times'], $tasks->groupBy('expression')
                ->get($schedule['expression'])
                ->groupBy('timezone')
                ->get($schedule['timezone'])
                ->count(),
                "$taskName is not scheduled {$schedule['expression']} in timezone {$schedule['timezone']} {$schedule['times']} times.");
        }
    }

    public function tasksProvider(): array
    {
        $commands = $this->ourCommands()->map(function(array $expressions, string $taskName) {
            return [
                self::COMMANDS,
                $taskName,
                $expressions,
            ];
        })->values()->toArray();
        $jobs     = $this->ourJobs()->map(function(array $expressions, string $taskName) {
            return [
                self::JOBS,
                $taskName,
                $expressions,
            ];
        })->values()->toArray();

        return array_merge($commands, $jobs);
    }
}

