<?php

namespace Laravel\Envoy;

use Closure;
use Symfony\Component\Process\Process;

abstract class RemoteProcessor
{

    /**
     * Extra parameters to be sent along with te SSH connection.
     *
     * @var string
     */
    protected $extraParams;

    /**
     * Run the given task over SSH.
     *
     * @param  \Laravel\Envoy\Task  $task
     * @return void
     */
    abstract public function run(Task $task, Closure $callback = null);

    /**
     * Set any extra params to be used for remote SSH connections.
     *
     * @param $params
     */
    public function setExtraParams( $params )
    {
        $this->extraParams = $params;
    }

    /**
     * Run the given script on the given host.
     *
     * @param  string  $host
     * @param  \Laravel\Envoy\Task  $task
     * @return int
     */
    protected function getProcess($host, Task $task)
    {
        $target = $this->getConfiguredServer($host) ?: $host;

        if (in_array($target, ['local', 'localhost', '127.0.0.1'])) {
            $process = new Process($task->script);
        }

        // Here we'll run the SSH task on the server inline. We do not need to write the
        // script out to a file or anything. We will start the SSH process then pass
        // these lines of output back to the parent callback for display purposes.
        else {
            $delimiter = 'EOF-LARAVEL-ENVOY';
            $extraParams = ( ! empty( $this->extraParams ) ? $this->extraParams . ' ' : '' );

            $process = new Process(
                "ssh $target "
                    . $extraParams
                    . "'bash -se' << \\$delimiter".PHP_EOL
                    . 'set -e'.PHP_EOL
                    . $task->script.PHP_EOL
                    . $delimiter
            );
        }

        return [$target, $process->setTimeout(null)];
    }

    /**
     * Gather the cumulative exit code for the processes.
     *
     * @param  array  $processes
     * @return int
     */
    protected function gatherExitCodes(array $processes)
    {
        $code = 0;

        foreach ($processes as $process) {
            $code = $code + $process->getExitCode();
        }

        return $code;
    }
}
