<?php

namespace Zsxsoft\W3CValidator;

/**
 * W3C Validator Class
 *
 * @method W3CValidator asciiquotes(string $text = '') Gets or specifies whether ASCII quotation marks are substituted for Unicode smart quotation marks in messages.
 * @method W3CValidator errors_only(string $text = '') Gets or specifies that only error-level messages and non-document-error messages are reported (so that warnings and info messages are not reported).
 * @method W3CValidator Werror(string $text = '') Gets or makes the checker exit non-zero if any warnings are encountered (even if there are no errors).
 * @method W3CValidator exit_zero_always(string $text = '') Gets or makes the checker exit zero even if errors are reported for any documents.
 * @method W3CValidator filterfile(string $text = '') Gets or specifies a filename. Each line of the file contains either a regular expression or starts with "#" to indicate the line is a comment. Any error message or warning message that matches a regular expression in the file is filtered out (dropped/suppressed).
 * @method W3CValidator filterpattern(string $text = '') Gets or specifies a regular-expression pattern. Any error message or warning message that matches the pattern is filtered out (dropped/suppressed).
 * @method W3CValidator format(string $text = 'json') Gets or specifies the output format for reporting the results.
 * @method W3CValidator help(string $text = '') Shows detailed usage information.
 * @method W3CValidator skip_non_html(string $text = '') Skip documents that don’t have *.html, *.htm, *.xhtml, or *.xht extensions.
 * @method W3CValidator html(string $text = '') Forces any *.xhtml or *.xht documents to be parsed using the HTML parser.
 * @method W3CValidator no_langdetect(string $text = '') Disables language detection, so that documents are not checked for missing or mislabeled html[lang] attributes.
 * @method W3CValidator no_stream(string $text = '') Forces all documents to be be parsed in buffered mode instead of streaming mode (causes some parse errors to be treated as non-fatal document errors instead of as fatal document errors).
 * @method W3CValidator verbose(string $text = '') Gets or specifies "verbose" output. (Currently this just means that the names of files being checked are written to stdout.)
 * @method W3CValidator version(string $text = '') Shows the vnu.jar version number.
 * @package Zsxsoft\W3CValidator
 */
class W3CValidator
{
    /**
     * @var string vnu.jar path
     */
    protected $_jar = '';
    /**
     * @var array available argument names list
     */
    protected $_availableArguments = [
        'asciiquotes',
        'errors-only',
        'Werror',
        'exit-zero-always',
        'filterfile',
        'filterpattern',
        'format',
        'help',
        'skip-non-html',
        'html',
        'no-langdetect',
        'no-stream',
        'verbose',
        'version',
    ];
    /**
     * @var array the arguments data
     */
    protected $_arguments = [];

    /**
     * @var string stdin data
     */
    protected $_stdin = '';
    /**
     * @var bool check is use stdin
     */
    protected $_fileName = '-';


    protected $_descriptorspec = [
        0 => ["pipe", "r"],  // Standard input
        1 => ["pipe", "w"],  // Standard output
        2 => ["pipe", "w"], // Standard error
    ];


    /**
     * W3CValidator constructor.
     * @param string $jarPath
     */
    public function __construct($jarPath = NULL)
    {
        if (is_null($jarPath)) {
            $this->_jar = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vnu.jar';
        } else {
            $this->_jar = $jarPath;
        }
        $this->format('json');
    }

    public function __call($name, $arguments)
    {
        $realName = str_replace('_', '-', $name);
        if (in_array($realName, $this->_availableArguments)) {
            if (!isset($this->_arguments[$realName])) {
                $this->_arguments[$realName] = '';
            }
            if (count($arguments) === 0) {
                return $this->_arguments[$realName];
            } else if (count($arguments) === 1) {
                $this->_arguments[$realName] = $arguments[0];
                return $this;
            }
            throw new \InvalidArgumentException();
        }
        throw new \BadMethodCallException($name);
    }

    /**
     * Gets or directly sets HTML data
     * @param string $data
     * @return $this|string
     */
    public function data($data = '') {
        if ($data === '') return $this->_stdin;
        $this->_stdin = $data;
        return $this;
    }


    /**
     * Gets or sets files
     * @param string $data
     * @return $this|string
     */
    public function fileName($data = '')
    {
        if ($data == '') return $this->_fileName;
        $this->_fileName = $data;
        return $this;
    }

    /**
     * Run the validator
     * @return mixed|array
     * @throws ValidatorException
     */
    public function run()
    {
        $argumentArray = [];
        foreach ($this->_arguments as $name => $argument) {
            $argumentArray[] = '--' . $name;
            $argumentArray[] = $argument;
        }
        $argumentString = implode(' ', $argumentArray);
        $argumentString .= ' ' . $this->fileName();
        $string = $this->exec($argumentString);
        if ($this->format() === 'json') {
            return [
                'normal' => json_decode($string[0]),
                'error' => json_decode($string[1])
            ];
        }
        return $string;
    }

    /**
     * Run the validator with custom argument
     * @param $argument
     * @param $callback callable callback for piping
     *
     * @return array
     * @throws ValidatorException
     */
    public function exec($argument, callable $callback = NULL)
    {
        $pipes = [];
        $process = proc_open("java -jar {$this->_jar} $argument", $this->_descriptorspec, $pipes);
        if (is_resource($process)) {
            if (!is_null($callback)) {
                $errorString = $returnString = $callback($pipes);
            } else {
                fwrite($pipes[0], $this->_stdin);
                fclose($pipes[0]);
                stream_set_blocking($pipes[1], 0);
                stream_set_blocking($pipes[2], 0);
                $errorString = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                $returnString = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
            }

            array_walk($pipes, function ($pipe) {
                if (is_resource($pipe)) fclose($pipe);
            });
            $returnInt = proc_close($process);

            if ($returnInt > 1) { // 1 == has errors
                throw new ValidatorException($errorString, $returnInt);
            }
            return [$returnString, $errorString];
        } else {
            throw new ValidatorException('Failed to start java');
        }
    }

}