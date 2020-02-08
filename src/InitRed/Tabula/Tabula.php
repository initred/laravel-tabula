<?php

namespace InitRed\Tabula;

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
    public $os;
    public $encoding = 'utf-8';
    public $javaOptions = [];
    public $options = [];
    public $input = null;

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
    public function __construct($binDir = null, $encoding = 'utf-8')
    {
        $this->osCheck();
        $this->encoding = $encoding;

        if ($binDir) {
            $this->binDir = is_array($binDir) ? $binDir : [$binDir];
        }
    }

    public function osCheck()
    {
        if (stripos(PHP_OS, 'win') === 0) {
            $this->os = 'Window';
        } elseif (stripos(PHP_OS, 'darwin') === 0) {
            $this->os = 'Mac';
        } elseif (stripos(PHP_OS, 'linux') === 0) {
            $this->os = 'Linux';
        }
    }

    public function isEncodeUTF8()
    {
        return $this->encoding === 'utf-8';
    }

    public function isOsWindow()
    {
        return $this->os === 'Window';
    }

    public function isOsMac()
    {
        return $this->os === 'Mac';
    }

    public function isLinux()
    {
        return $this->os === 'Linux';
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

    public function buildJavaOptions()
    {
        $javaOptions = ['java'];

        $finder = new ExecutableFinder();
        $binary = $finder->find('java', null, $this->binDir);

        if ($binary === null) {
            throw new RuntimeException('Could not find java on your system.');
        }

        array_push($javaOptions, '-Xmx256m');

        if($this->isOsMac()) {
            array_push($javaOptions, '-Djava.awt.headless=true');
        }

        if($this->isEncodeUTF8()) {
            array_push($javaOptions, '-Dfile.encoding=UTF8');
        }

        array_push($javaOptions, '-jar');
        array_push($javaOptions, $this->getJarArchive());

        $this->javaOptions = $javaOptions;
    }

    public function extractFormatForConversion($format)
    {
        if(empty($format)) {
            throw new InvalidArgumentException('Convert format does not exist.');
        }

        $format = strtoupper($format);

        if ($format === 'CSV' || $format === 'TSV' || $format === 'JSON') {
            return $format;
        } else {
            throw new InvalidArgumentException('Invalid Format. ex) CSV, TSV, JSON');
        }
    }

    public function existFileCheck($path)
    {
        if(!file_exists($path)) {
            throw new InvalidArgumentException('File does not exist.');
        }

        return true;
    }

    public function existDirectoryCheck($path)
    {
        if(!is_dir($path)) {
            throw new InvalidArgumentException('Folder to target Pdf does not exist.');
        }
    }

    public function addInputOption($input)
    {
        $this->input = $input;
    }

    /**
     * @param null $pages
     * @param bool $guess
     * @param null $area
     * @param bool $relativeArea
     * @param bool $lattice
     * @param bool $stream
     * @param null $password
     * @param bool $silent
     * @param null $columns
     * @param null $format
     * @param null $batch
     * @param null $outputPath
     * @param string $options
     */
    public function buildOptions(
        $pages = null,
        $guess = true,
        $area = null,
        $relativeArea = false,
        $lattice = false,
        $stream = false,
        $password = null,
        $silent = false,
        $columns = null,
        $format = null,
        $batch = null,
        $outputPath = null,
        $options = ''
    ) {
        $this->options = [];

        $buildOptions = [];

        if(!is_null($this->input)) {
            if($this->existFileCheck($this->input)) {
                $buildOptions = array_merge($buildOptions, [$this->input]);
            }
        }

        if(!is_null($pages)) {
            if($pages === 'all') {
                $buildOptions = array_merge($buildOptions, ['--page', 'all']);
            } else {
                $area = implode( ',', $pages);
                $buildOptions = array_merge($buildOptions, ['--page', $pages]);
            }
        }

        $multipleArea = false;

        if(!is_null($area)) {
            $guess = false;

            foreach($area as $key => $value) {
                if(substr( $value, 0, 1 ) === '%') {
                    if($relativeArea) {
                        $area[$key] = str_replace('%', '', $area[$key]);
                    }
                }
            }

            $area = implode( ',', $area);

            $buildOptions = array_merge($buildOptions, ["--area", $area]);
        }

        if($lattice) {
            $buildOptions = array_merge($buildOptions, ["--lattice"]);
        }

        if($stream) {
            $buildOptions = array_merge($buildOptions, ["--stream"]);
        }

        if($guess && !$multipleArea) {
            $buildOptions = array_merge($buildOptions, ["--guess"]);
        }

        if(!is_null($format)) {

            $format = $this->extractFormatForConversion($format);
            $buildOptions = array_merge($buildOptions, ["--format", $format]);
        }

        if(!is_null($outputPath)) {
            $buildOptions = array_merge($buildOptions, ["--outfile", $outputPath]);
        }

        if(!is_null($columns)) {
            $columns = implode( ',', $columns );
            $buildOptions = array_merge($buildOptions, ["--columns", $columns]);
        }

        if(!is_null($password)) {
            $buildOptions = array_merge($buildOptions, ["--password", $password]);
        }

        if(!is_null($batch)) {

            if(!is_dir($batch)) {
                throw new InvalidArgumentException('Folder to output Pdf does not exist.');
            }

            $buildOptions = array_merge($buildOptions, ['--batch', $batch]);
        }

        if($silent) {
            $buildOptions = array_merge($buildOptions, ["--silent"]);
        }

        $this->options = $buildOptions;
    }

    public function run()
    {
        $parameters = array_merge($this->javaOptions, $this->options);

        $process = new Process($parameters);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        $process->getOutput();
    }

    /**
     * @param $input
     * @param $output
     * @param string $format
     * @param string $pages
     */
    public function convertInto($input, $output, $format = 'csv', $pages = 'all')
    {
        self::buildJavaOptions();
        self::addInputOption($input);
        self::buildOptions(
            $pages,
            true,
            null,
            false,
            false,
            false,
            null,
            false,
            null,
            $format,
            null,
            $output,
            null
        );
        self::run();
    }

    /**
     * @param $directory
     * @param string $format
     * @param string $pages
     */
    public function convertIntoByBatch($directory, $format = 'csv', $pages = 'all')
    {
        self::buildJavaOptions();
        self::buildOptions(
            $pages,
            true,
            null,
            false,
            false,
            false,
            null,
            false,
            null,
            $format,
            $directory,
            null,
            null
        );
        self::run();
    }
}
