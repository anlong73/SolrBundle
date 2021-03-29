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

namespace FS\SolrBundle\Event;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use Solarium\Client;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

class Event extends BaseEvent
{
    /**
     * @var Client
     */
    private $client = null;

    /**
     * @var MetaInformationInterface
     */
    private $metainformation = null;

    /**
     * something like 'update-solr-document'.
     *
     * @var string
     */
    private $solrAction = '';

    /**
     * @var Event
     */
    private $sourceEvent;

    /**
     * @param Client                   $client
     * @param MetaInformationInterface $metainformation
     * @param string                   $solrAction
     * @param Event                    $sourceEvent
     */
    public function __construct(
        Client $client = null,
        MetaInformationInterface $metainformation = null,
        $solrAction = '',
        Event $sourceEvent = null
    ) {
        $this->client = $client;
        $this->metainformation = $metainformation;
        $this->solrAction = $solrAction;
        $this->sourceEvent = $sourceEvent;
    }

    /**
     * @return MetaInformationInterface
     */
    public function getMetaInformation()
    {
        return $this->metainformation;
    }

    /**
     * @return string
     */
    public function getSolrAction()
    {
        return $this->solrAction;
    }

    /**
     * @return Event
     */
    public function getSourceEvent()
    {
        return $this->sourceEvent;
    }

    /**
     * @return bool
     */
    public function hasSourceEvent()
    {
        return null !== $this->sourceEvent;
    }
}
