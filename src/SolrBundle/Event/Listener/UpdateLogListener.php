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
 * Create a log-entry if a document was updated.
 */
class UpdateLogListener extends AbstractLogListener
{
    /**
     * @param Event $event
     */
    public function onSolrUpdate(Event $event)
    {
        $metaInformation = $event->getMetaInformation();

        $nameWithId = $this->createDocumentNameWithId($metaInformation);
        $fieldList = $this->createFieldList($metaInformation);

        $this->logger->debug(
            sprintf('document %s with fields %s was updated', $nameWithId, $fieldList)
        );
    }
}
