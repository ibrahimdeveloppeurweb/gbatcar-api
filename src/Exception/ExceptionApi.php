<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionApi extends HttpException
{
    /**
     * Exception message
     *
     * @var string
     */
    protected $message = 'Unknown exception';

    /**
     * Exception code
     *
     * @var int
     */
    protected $code = 422;

    /**
     * Source filename of exception
     *
     * @var string
     */
    protected $filename;

    /**
     * Source ligne of exception
     *
     * @var string
     */
    protected $ligne;

    /**
     * @var array
     */
    protected $errors;


    public function __construct($message = null, $errors = [], $code = 422)
    {
        if ($code) {
            $this->code = $code;
        }
        if (!$message) {
            throw new $this('Unknown ' . get_class($this));
        }
        if (!$errors) {
            $errors = ['msg' => $message];
        }
        parent::__construct($this->code, $message);
        $this->errors = $errors;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->filename}({$this->ligne})\n"
            . "{$this->getTraceAsString()}";
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}