<?php

namespace Tests\Unit;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    const COMMANDS = 'commands';
    const JOBS     = 'jobs';
    private Schedule   $schedule;
    private Collection $groupedScheduledTasks;
    private Collection $tasks;

    public function setUp(): void
    {
        parent::setUp();

        $this->schedule = app()->make(Schedule::class);

        $this->groupedScheduledTasks = Collection::make();
        $this->tasks                 = Collection::make($this->schedule->events());

        $commands = $this->tasks->filter(function(Event $command) {
            return !is_a($command, CallbackEvent::class);
        });
        $jobs     = $this->tasks->filter(function(Event $item) {
            return is_a($item, CallbackEvent::class);
        });

        $this->groupedScheduledTasks->put(self::COMMANDS, $commands);
        $this->groupedScheduledTasks->put(self::JOBS, $jobs);
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

            if (!$ourExpressions->contains(function($value) use ($expression, $timezone) {
                return $value['expression'] == $expression && $value['timezone'] == $timezone;
            })) {
                $failed->push("Failed asserting that $taskName has schedule $expression with timezone $timezone.");
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

    private function ourCommands(): Collection
    {
        return Collection::make([
            'export:invoices'           => [
                ['expression' => '0 7 8 * *', 'timezone' => 'UTC'],
            ],
            'export:invoices-customers' => [
                ['expression' => '0 7 8 * *', 'timezone' => 'UTC'],
            ],
            'xoxo:upsert-tokens'        => [
                ['expression' => '0 0 * * 0', 'timezone' => 'UTC'],
            ],
            'xoxo:upsert-vouchers'      => [
                ['expression' => '0 1 * * *', 'timezone' => 'UTC'],
            ],
        ]);
    }

    private function ourJobs(): Collection
    {
        return Collection::make([
            'App\Jobs\SendOrderPendingApprovalReminder' => [
                ['expression' => '0 7 * * *', 'timezone' => 'America/Adak'],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Anchorage'],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Los_Angeles'],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Phoenix'],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Chicago'],
                ['expression' => '0 7 * * *', 'timezone' => 'America/Denver'],
                ['expression' => '0 7 * * *', 'timezone' => 'America/New_York'],
            ],
        ]);
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
            $this->assertTrue($tasks->contains(function(Event $task) use ($schedule) {
                return $task->getExpression() == $schedule['expression'] && $task->timezone == $schedule['timezone'];
            }), "$taskName is not scheduled {$schedule['expression']} in timezone {$schedule['timezone']}.");
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
