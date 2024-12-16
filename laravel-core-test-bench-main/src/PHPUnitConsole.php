<?php

namespace AttractCores\LaravelCoreTestBench;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait PHPUnitConsole
 *
 * @version 1.0.0
 * @date    2019-03-12
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait PHPUnitConsole
{

    /**
     * Output interface.
     *
     * @var ConsoleOutput
     */
    protected $output;

    /**
     * Format input to textual table.
     *
     * @param  array                                         $headers
     * @param  \Illuminate\Contracts\Support\Arrayable|array $rows
     * @param  string                                        $tableStyle
     * @param  array                                         $columnStyles
     *
     * @return void
     */
    public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
    {
        if ($this->isVerboseOutput()) {
            $table = new Table($this->output);

            if ($rows instanceof Arrayable) {
                $rows = $rows->toArray();
            }

            $table->setHeaders((array) $headers)->setRows($rows)->setStyle($tableStyle);

            foreach ($columnStyles as $columnIndex => $columnStyle) {
                $table->setColumnStyle($columnIndex, $columnStyle);
            }

            $table->render();
        }
    }

    /**
     * Check if output must be verbose
     *
     * @return boolean
     */
    public function isVerboseOutput()
    {
        return env('VERBOSE_OUTPUT', false);
    }

    /**
     * Write a string as information output.
     *
     * @param  string          $string
     * @param  int|string|null $verbosity
     *
     * @return void
     */
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as standard output.
     *
     * @param  string          $string
     * @param  string          $style
     * @param  int|string|null $verbosity
     *
     * @return void
     */
    public function line($string, $style = null, $verbosity = null)
    {
        if ($this->isVerboseOutput()) {
            $styled = $style ? "<$style>$string</$style>" : $string;

            $this->output->writeln($styled, $verbosity ?? OutputInterface::VERBOSITY_NORMAL);
        }
    }

    /**
     * Write a string as question output.
     *
     * @param  string          $string
     * @param  int|string|null $verbosity
     *
     * @return void
     */
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param  string          $string
     * @param  int|string|null $verbosity
     *
     * @return void
     */
    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param  string          $string
     * @param  int|string|null $verbosity
     *
     * @return void
     */
    public function warn($string, $verbosity = null)
    {
        if ( ! $this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');

            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Write a string in an alert box.
     *
     * @param  string $string
     *
     * @return void
     */
    public function alert($string)
    {
        $this->newLine();
        $length = Str::length(strip_tags($string)) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment('*     ' . $string . '     *');
        $this->comment(str_repeat('*', $length));

        $this->newLine();
    }

    /**
     * Draw new line into console.
     */
    public function newLine()
    {
        if ($this->isVerboseOutput()) {
            $this->output->write(str_repeat(PHP_EOL, 1));
        }
    }

    /**
     * Write a string as comment output.
     *
     * @param  string          $string
     * @param  int|string|null $verbosity
     *
     * @return void
     */
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Run console output.
     */
    protected function runConsoleOutput()
    {
        $this->output = new ConsoleOutput(OutputInterface::VERBOSITY_NORMAL);
    }
}
