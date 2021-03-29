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

namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Event\Event;

/**
 * Create a log-entry if the index was cleared.
 */
class ClearIndexLogListener extends AbstractLogListener
{
    /**
     * @param Event $event
     */
    public function onClearIndex(Event $event)
    {
        $this->logger->debug(sprintf('clear index'));
    }
}
