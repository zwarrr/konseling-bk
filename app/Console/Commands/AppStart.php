<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AppStart extends Command
{
    protected $signature   = 'app:start {--host=127.0.0.1} {--port=8000} {--reverb-port=8080}';
    protected $description = 'Start Laravel serve + Reverb WebSocket + Queue listener together';

    public function handle(): int
    {
        $host       = $this->option('host');
        $port       = $this->option('port');
        $reverbPort = $this->option('reverb-port');

        $this->info("Starting Laravel App...");
        $this->line("  <fg=cyan>Server</>      http://{$host}:{$port}");
        $this->line("  <fg=cyan>Reverb WS</>   ws://{$host}:{$reverbPort}");
        $this->line("  <fg=cyan>Queue</>        database driver");
        $this->newLine();

        $php = PHP_BINARY;

        $processes = [
            'serve'  => new Process([$php, 'artisan', 'serve', "--host={$host}", "--port={$port}"],
                base_path(), null, null, null),
            'reverb' => new Process([$php, 'artisan', 'reverb:start', "--host=0.0.0.0", "--port={$reverbPort}"],
                base_path(), null, null, null),
            'queue'  => new Process([$php, 'artisan', 'queue:listen', '--tries=1', '--timeout=0'],
                base_path(), null, null, null),
        ];

        foreach ($processes as $name => $process) {
            $process->start();
            $this->line("<fg=green>✓</> Started: <fg=yellow>{$name}</>");
        }

        $this->newLine();
        $this->line('<fg=gray>Press Ctrl+C to stop all processes.</fg=gray>');

        // Stream output from all processes (exits on SIGINT/SIGTERM or all processes stop)
        $running = true;
        if (function_exists('pcntl_signal')) {
            pcntl_signal(2,  function () use (&$running) { $running = false; }); // SIGINT
            pcntl_signal(15, function () use (&$running) { $running = false; }); // SIGTERM
        }
        while ($running) {
            foreach ($processes as $name => $process) {
                $out = $process->getIncrementalOutput();
                $err = $process->getIncrementalErrorOutput();

                if ($out) {
                    foreach (explode("\n", trim($out)) as $line) {
                        if ($line) $this->line("<fg=cyan>[{$name}]</> {$line}");
                    }
                }
                if ($err) {
                    foreach (explode("\n", trim($err)) as $line) {
                        if ($line) $this->line("<fg=red>[{$name}]</> {$line}");
                    }
                }

                if (!$process->isRunning()) {
                    $this->error("[{$name}] process stopped unexpectedly.");
                    $process->restart();
                }
            }

            usleep(200_000); // 200ms

            // Allow graceful stop (e.g. pcntl_signal on Unix)
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }

        foreach ($processes as $process) {
            $process->stop();
        }

        return self::SUCCESS;
    }
}
