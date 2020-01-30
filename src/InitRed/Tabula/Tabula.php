<?php

namespace InitRed\Tabula;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * laravel-tabula
 *
 * laravel-tabula is a tool for liberating data tables trapped inside PDF files for the Laravel framework.
 * This package was inspired by Python’s tabula-py package.
 *
 *
 * Refer to this route for the original wrapped java source code.
 * https://github.com/tabulapdf/tabula
 * https://github.com/tabulapdf/tabula-java
 *
 **/

class Tabula
{
    /**
     * Additional dir to check for java executable
     * @var
     */
    private $binDir = [];

    /**
     * Path to jar file
     */
    private $jarArchive = __DIR__ . '/../../../lib/tabula-1.0.4-jar-with-dependencies.jar';

    /**
     * Tabula constructor.
     * @param null $binDir
     */
    public function __construct($binDir = null)
    {
        if ($binDir) {
            $this->binDir = is_array($binDir) ? $binDir : [$binDir];
        }
    }

    /**
     * @return string
     */
    public function getJarArchive()
    {
        return $this->jarArchive;
    }

    /**
     * @param string $jarArchive
     */
    public function setJarArchive($jarArchive)
    {
        $this->jarArchive = $jarArchive;
    }

    /**
     * @return array|null
     */
    public function getBinDir()
    {
        return $this->binDir;
    }

    /**
     * @param $binDir
     */
    public function setBinDir($binDir)
    {
        $this->binDir = $binDir;
    }

    /**
     * @param null $format
     * @param null $target
     * @param null $output
     */
    public function parse($format = null, $target = null, $output = null)
    {
        $parameters = [];

        if(empty($format)) {
            throw new InvalidArgumentException('Convert format does not exist.');
        }

        $format = strtoupper($format);

        if($format === 'CSV' || $format === 'TSV' || $format === 'JSON') {
            $parameters = array_merge($parameters, ['-f', $format]);
        } else {
            throw new InvalidArgumentException('Invalid Format. ex) CSV, TSV, JSON');
        }

        if(!is_dir($target)) {
            throw new InvalidArgumentException('Folder to target Pdf does not exist.');
        }

        if(Str::endsWith($target, '/')) {
            $target = Str::replaceLast('/','', $target);
        }

        $parameters = array_merge($parameters, ['-b', $target]);

        if(!is_dir($output)) {
            throw new InvalidArgumentException('Folder to output Pdf does not exist.');
        }

        if(Str::endsWith($output, '/')) {
            $output = Str::replaceLast('/','', $output);
        }

        $finder = new ExecutableFinder();
        $binary = $finder->find('java', null, $this->binDir);

        if ($binary === null) {
            throw new RuntimeException('Could not find java on your system.');
        }

        $arguments = array_merge(
            ['java', '-jar', $this->getJarArchive()],
            $parameters
        );

        $process = new Process($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        $process->getOutput();

        if($output) {
            $fileLists = File::allFiles($target);

            foreach($fileLists as $file) {
                $basename = File::basename($file);

                if(Str::endsWith($basename,"." . strtolower($format))) {
                    File::move( File::dirname($file). '/' . $basename, $output.'/'.$basename);
                }
            }
        }
    }
}