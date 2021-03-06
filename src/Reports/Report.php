<?php

namespace Wyox\GitlabReport\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class Report
{
    protected $exception;
    protected $request;

    /**
     * Report constructor.
     *
     * @param \Throwable $exception
     * @param Request    $request
     */
    public function __construct(\Throwable $exception, Request $request)
    {
        $this->exception = $exception;
        $this->request = $request;
    }

    /**
     * This returns a unique signature based on the exception,
     * the query and input parameters.
     *
     * @return string
     */
    public function signature()
    {
        // Signature should be unique to the error (ignore session for now)
        // Signature should be unique to the error (ignore session for now)
        $key = $this->message().$this->exception->getFile().$this->exception->getTraceAsString().$this->exception->getCode();
        // This might fail if it has complex objects
        $key .= (new Collection($this->request->request->all()))->toJson();
        $key .= (new Collection($this->request->query->all()))->toJson();

        return hash('md5', $key);
    }

    /**
     * Returns description "rendered".
     *
     * @return string
     */
    public function render()
    {
        // Limit string to 1048575 characters per gitlab api limit
        return substr($this->description(), 0, 1048575);
    }

    /**
     * Generates a GitLab issue title.
     *
     * @return string
     */
    public function title()
    {
        return substr("BUG: {$this->message()}", 0, 254);
    }

    /**
     * Returns the title of the issue.
     *
     * @return string
     */
    abstract public function message();

    /**
     * Returns the issue in markdown.
     *
     * @return string
     */
    abstract public function description();

    /**
     * Should return the unique identifier for this issue.
     *
     * @return string
     */
    abstract public function identifier();
}
