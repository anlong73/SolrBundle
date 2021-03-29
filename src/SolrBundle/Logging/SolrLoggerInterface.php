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

interface SolrLoggerInterface
{
    /**
     * Called when the request is started.
     *
     * @param array $request
     */
    public function startRequest(array $request);

    /**
     * Called when the request has ended.
     */
    public function stopRequest();
}
