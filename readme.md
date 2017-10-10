php-w3c-validator
============================

> _Wrapper for [The Nu Html Checker (v.Nu)](https://github.com/validator/validator) 

---

## Attention

> You need install Java for working with `php-w3c-validator`  
> Visit https://java.com for download Java if you not have it

## Installation

``composer require zsxsoft/php-w3c-validator``

## Usage

```php
<?php
use Zsxsoft\W3CValidator\W3CValidator;

$validator = new W3CValidator();
print_r($validator->data('<html></html>')->run());
}
```

Result
```php
Array(
    [normal] => NULL
    [error] => stdClass Object
        (
            [messages] => Array
                (
                    [0] => stdClass Object
                        (
                            [type] => error
                            [lastLine] => 1
                            [lastColumn] => 6
                            [firstColumn] => 1
                            [message] => Start tag seen without seeing a doctype first. Expected “<!DOCTYPE html>”.
                            [extract] => <html></html
                            [hiliteStart] => 0
                            [hiliteLength] => 6
                        )

                    [1] => stdClass Object
                        (
                            [type] => error
                            [lastLine] => 1
                            [lastColumn] => 13
                            [firstColumn] => 7
                            [message] => Element “head” is missing a required instance of child element “title”.
                            [extract] => <html></html>
                            [hiliteStart] => 6
                            [hiliteLength] => 7
                        )

                )

        )

)
```

## API

### __construct

    mixed Zsxsoft\W3CValidator\W3CValidator::__construct(string $jarPath)

W3CValidator constructor.

#### Arguments
* $jarPath **string**


### data

    $this|string Zsxsoft\W3CValidator\W3CValidator::data(string $data)

Gets or directly sets HTML data


### fileName

    $this|string Zsxsoft\W3CValidator\W3CValidator::fileName(string $data)

Gets or sets files, "-" for the text set by data.

* Default: -


### run

    mixed|array Zsxsoft\W3CValidator\W3CValidator::run()

Run the validator


### exec

    array Zsxsoft\W3CValidator\W3CValidator::exec($argument, $callback = NULL)

Run the validator with custom argument


#### Arguments
* $argument **mixed**
* $callback **mixed** - &lt;p&gt;callable callback for piping&lt;/p&gt;


### Command Line Arguments

See https://github.com/validator/validator for help.

#### asciiquotes

     $this|string Zsxsoft\W3CValidator\W3CValidator::asciiquotes()

Gets or specifies whether ASCII quotation marks are substituted for Unicode smart quotation marks in messages.



#### errors_only

     $this|string Zsxsoft\W3CValidator\W3CValidator::errors_only()

Gets or specifies that only error-level messages and non-document-error messages are reported (so that warnings and info messages are not reported).



#### Werror

     $this|string Zsxsoft\W3CValidator\W3CValidator::Werror()

Gets or makes the checker exit non-zero if any warnings are encountered (even if there are no errors).


#### exit_zero_always

     $this|string Zsxsoft\W3CValidator\W3CValidator::exit_zero_always()

Gets or makes the checker exit zero even if errors are reported for any documents.

#### filterfile

     $this|string Zsxsoft\W3CValidator\W3CValidator::filterfile()

Gets or specifies a filename. Each line of the file contains either a regular expression or starts with "#" to indicate the line is a comment. Any error message or warning message that matches a regular expression in the file is filtered out (dropped/suppressed).


#### filterpattern

     $this|string Zsxsoft\W3CValidator\W3CValidator::filterpattern()

Gets or specifies a regular-expression pattern. Any error message or warning message that matches the pattern is filtered out (dropped/suppressed).


#### format

     $this|string Zsxsoft\W3CValidator\W3CValidator::format()

Gets or specifies the output format for reporting the results.

* Default: json


#### skip_non_html

     $this|string Zsxsoft\W3CValidator\W3CValidator::skip_non_html()

Skip documents that don’t have *.html, *.htm, *.xhtml, or *.xht extensions.


#### html

     $this|string Zsxsoft\W3CValidator\W3CValidator::html()

Forces any *.xhtml or *.xht documents to be parsed using the HTML parser.


#### no_langdetect

     $this|string Zsxsoft\W3CValidator\W3CValidator::no_langdetect()

Disables language detection, so that documents are not checked for missing or mislabeled html[lang] attributes.



#### no_stream

     $this|string Zsxsoft\W3CValidator\W3CValidator::no_stream()

Forces all documents to be be parsed in buffered mode instead of streaming mode (causes some parse errors to be treated as non-fatal document errors instead of as fatal document errors).



#### verbose

     $this|string Zsxsoft\W3CValidator\W3CValidator::verbose()

Gets or specifies "verbose" output. (Currently this just means that the names of files being checked are written to stdout.)



#### version

     $this|string Zsxsoft\W3CValidator\W3CValidator::version()

Shows the vnu.jar version number.


## License

The MIT License