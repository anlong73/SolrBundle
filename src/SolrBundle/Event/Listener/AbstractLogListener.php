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

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractLogListener
{
    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param MetaInformationInterface $metaInformation
     *
     * @return string
     */
    protected function createDocumentNameWithId(MetaInformationInterface $metaInformation)
    {
        return $metaInformation->getDocumentName().':'.$metaInformation->getEntityId();
    }

    /**
     * @param MetaInformationInterface $metaInformation
     *
     * @return string
     */
    protected function createFieldList(MetaInformationInterface $metaInformation)
    {
        return implode(', ', $metaInformation->getFields());
    }
}
