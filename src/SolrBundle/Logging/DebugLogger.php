<?php

/*
 * Solr Bundle
 * This is a fork of the unmaintained solr bundle from Florian Semm.
 *
 * @author Daan Biesterbos     (fork maintainer)
 * @author Florian Semm (author original bundle)
 *
 * Issues can be submitted here:
 * https://github.com/daanbiesterbos/SolrBundle/issues
 */

namespace FS\SolrBundle\Logging;

/**
 * Logs the current request and information about this request.
 */
class DebugLogger implements SolrLoggerInterface
{
    /**
     * @var float
     */
    private $start;

    /**
     * @var array
     */
    private $queries = [];

    /**
     * @var int
     */
    private $currentQuery = 0;

    /**
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * {@inheritdoc}
     */
    public function startRequest(array $request)
    {
        $this->start = microtime(true);
        $this->queries[++$this->currentQuery] = [
            'request' => $request,
            'executionMS' => 0,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function stopRequest()
    {
        $this->queries[$this->currentQuery]['executionMS'] = microtime(true) - $this->start;
    }
}
