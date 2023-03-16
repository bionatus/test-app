<?php

namespace Database\Seeders;

use App;
use Exception;
use ReflectionClass;

trait SeedsEnvironment
{
    /**
     * @throws Exception
     */
    public function canRunInEnvironment(): bool
    {
        $environment = App::environment();

        return in_array($environment, $this->environments());
    }

    /**
     * @throws Exception
     */
    public function __invoke(array $parameters = [])
    {
        if ($this instanceof EnvironmentSeeder && !$this->canRunInEnvironment()) {
            if (!$this->command) {
                return null;
            }

            $reflection  = new ReflectionClass(static::class);
            $environment = App::environment();
            $this->command->line("Omitting {$reflection->getShortName()} in current environment ({$environment})");

            return null;
        }

        return parent::__invoke($parameters);
    }
}
