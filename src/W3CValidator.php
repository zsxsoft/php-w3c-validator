<?php

namespace Zsxsoft\W3CValidator;

/**
 * W3C Validator Class
 *
 * @method W3CValidator asciiquotes() Specifies whether ASCII quotation marks are substituted for Unicode smart quotation marks in messages.
 * @method W3CValidator errors_only() Specifies that only error-level messages and non-document-error messages are reported (so that warnings and info messages are not reported).
 * @method W3CValidator Werror() Makes the checker exit non-zero if any warnings are encountered (even if there are no errors).
 * @method W3CValidator exit_zero_always() Makes the checker exit zero even if errors are reported for any documents.
 * @method W3CValidator filterfile(string $text = '') Gets or specifies a filename. Each line of the file contains either a regular expression or starts with "#" to indicate the line is a comment. Any error message or warning message that matches a regular expression in the file is filtered out (dropped/suppressed).
 * @method W3CValidator filterpattern(string $text = '') Gets or specifies a regular-expression pattern. Any error message or warning message that matches the pattern is filtered out (dropped/suppressed).
 * @method W3CValidator format(string $text = 'json') Gets or specifies the output format for reporting the results.
 * @method W3CValidator help() Shows detailed usage information.
 * @method W3CValidator skip_non_html() Skip documents that don’t have *.html, *.htm, *.xhtml, or *.xht extensions.
 * @method W3CValidator html() Forces any *.xhtml or *.xht documents to be parsed using the HTML parser.
 * @method W3CValidator no_langdetect() Disables language detection, so that documents are not checked for missing or mislabeled html[lang] attributes.
 * @method W3CValidator no_stream() Forces all documents to be be parsed in buffered mode instead of streaming mode (causes some parse errors to be treated as non-fatal document errors instead of as fatal document errors).
 * @method W3CValidator verbose() Specifies "verbose" output. (Currently this just means that the names of files being checked are written to stdout.)
 * @method W3CValidator version() Shows the vnu.jar version number.
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
        'asciiquotes' => 'boolean',
        'errors-only' => 'boolean',
        'Werror' => 'boolean',
        'exit-zero-always' => 'boolean',
        'filterfile' => 'string',
        'filterpattern' => 'string',
        'format' => 'string',
        'help' => 'boolean',
        'skip-non-html' => 'boolean',
        'html' => 'boolean',
        'no-langdetect' => 'boolean',
        'no-stream' => 'boolean',
        'verbose' => 'boolean',
        'version' => 'boolean'
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

    /**
     * @var string Java argument
     */
    protected $_javaArgument = '';


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
        if (isset($this->_availableArguments[$realName])) {
            $type = $this->_availableArguments[$realName];
            if ($type == 'boolean' && count($arguments) === 0) {
                $this->_arguments[$realName] = '';
                return $this;
            } else if (count($arguments) === 0) {
                return $this->_arguments[$realName] ?: '';
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
     * Gets or sets Java argument
     * @param string $data
     * @return $this|string
     */
    public function javaArgument($data = '')
    {
        if ($data == '') return $this->_javaArgument;
        $this->_javaArgument = $data;
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
        $process = proc_open("java {$this->_javaArgument} -jar {$this->_jar} $argument", $this->_descriptorspec, $pipes);
        if (is_resource($process)) {
            if (!is_null($callback)) {
                $errorString = $returnString = $callback($pipes);
            } else {
                fwrite($pipes[0], $this->_stdin);
                fclose($pipes[0]);
                // Notes:
                // In Windows, we have to read STDERR firstly.
                // because in most of time, STDOUT will be empty
                // but we cannot detect it, so PHP will block on stream_get_contents
                $errorString = stream_get_contents($pipes[2]);
                $returnString = stream_get_contents($pipes[1]);
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