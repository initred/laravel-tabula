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
    public $input = null;
    public $options = [
        'pages' => null,
        'guess' => true,
        'area' => [],
        'relativeArea' => false,
        'lattice' => false,
        'stream' => false,
        'password' => null,
        'silent' => false,
        'columns' => null,
        'format' => null,
        'batch' => null,
        'outfile' => null,
    ];

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
     * @param string $encoding
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
     * @return Tabula
     */
    public function setJarArchive(string $jarArchive)
    {
        $this->jarArchive = $jarArchive;

        return $this;
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
     * @return Tabula
     */
    public function setBinDir($binDir)
    {
        $this->binDir = $binDir;

        return $this;
    }

    /**
     * @param string $input
     * @return Tabula
     */
    public function setPdf(string $input)
    {
        $this->input = $input;

        return  $this;
    }

    /**
     * @param array $options
     * @return Tabula
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
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

    /**
     * @param $format
     * @return string
     */
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

    /**
     * @param $path
     * @return bool
     */
    public function existFileCheck($path)
    {
        if(!file_exists($path)) {
            throw new InvalidArgumentException('File does not exist.');
        }

        if (!is_readable($path)) {
            throw new InvalidArgumentException("Could not read `{$path}`");
        }

        return true;
    }

    /**
     * @param $path
     * @return bool
     */
    public function existDirectoryCheck($path)
    {
        if(!is_dir($path)) {
            throw new InvalidArgumentException('Folder to target Pdf does not exist.');
        }

        return true;
    }


    /**
     * @param array $options
     */
    public function buildOptions(array $options)
    {
        $buildOptions = [];

        if (! is_null($this->input)) {
            if ($this->existFileCheck($this->input)) {
                $buildOptions = array_merge($buildOptions, [$this->input]);
            }
        }

        if (! is_null($options['pages'])) {
            if ($options['pages'] === 'all') {
                $buildOptions = array_merge($buildOptions, ['--page', 'all']);
            } else {
                $options['area'] = implode(',', [$options['pages']]);
                $buildOptions = array_merge($buildOptions, ['--page', $options['pages']]);
            }
        }

        $multipleArea = false;

        if (! is_null($options['area']) && !empty($options['area'])) {
            $options['guess'] = false;

            foreach ($options['area'] as $key => $value) {
                if (substr($value, 0, 1) === '%') {
                    if ($options['relativeArea']) {
                        $options['area'][$key] = str_replace('%', '', $options['area'][$key]);
                    }
                }
            }

            $options['area'] = implode(',', $options['area']);

            $buildOptions = array_merge($buildOptions, ['--area', $options['area']]);
        }

        if ($options['lattice']) {
            $buildOptions = array_merge($buildOptions, ['--lattice']);
        }

        if ($options['stream']) {
            $buildOptions = array_merge($buildOptions, ['--stream']);
        }

        if ($options['guess'] && ! $multipleArea) {
            $buildOptions = array_merge($buildOptions, ['--guess']);
        }

        if (! is_null($options['format'])) {
            $format = $this->extractFormatForConversion($options['format']);
            $buildOptions = array_merge($buildOptions, ['--format', $format]);
        }

        if (! is_null($options['outfile'])) {
            $buildOptions = array_merge($buildOptions, ['--outfile', $options['outfile']]);
        }

        if (! is_null($options['columns'])) {
            $columns = implode(',', $options['columns']);
            $buildOptions = array_merge($buildOptions, ['--columns', $columns]);
        }

        if (! is_null($options['password'])) {
            $buildOptions = array_merge($buildOptions, ['--password', $options['password']]);
        }

        if (! is_null($options['batch'])) {
            if ($this->existDirectoryCheck($options['batch'])) {
                $buildOptions = array_merge($buildOptions, ['--batch', $options['batch']]);
            }
        }

        if ($options['silent']) {
            $buildOptions = array_merge($buildOptions, ['--silent']);
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

    public function convert()
    {
        self::buildJavaOptions();
        self::buildOptions($this->options);
        self::run();
    }
}