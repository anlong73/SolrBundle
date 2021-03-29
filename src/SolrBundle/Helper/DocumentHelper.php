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

namespace FS\SolrBundle\Helper;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Solr;
use Solarium\Client as SolariumClient;
use Solarium\QueryType\Select\Query\Query;

class DocumentHelper
{
    /**
     * @var SolariumClient
     */
    private $solariumClient;

    /**
     * @var MetaInformationFactory
     */
    private $metaInformationFactory;

    /**
     * @param Solr $solr
     */
    public function __construct(Solr $solr)
    {
        $this->solariumClient = $solr->getClient();
        $this->metaInformationFactory = $solr->getMetaFactory();
    }

    /**
     * @param mixed $entity
     *
     * @return int
     */
    public function getLastInsertDocumentId($entity)
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        /** @var Query $select */
        $select = $this->solariumClient->createQuery(SolariumClient::QUERY_SELECT);
        $select->setQuery(sprintf('id:%s*', $metaInformation->getDocumentKey()));
        $select->setRows($this->getNumberOfDocuments($metaInformation->getDocumentName()));
        $select->addFields(['id']);

        $result = $this->solariumClient->select($select);

        if (0 === $result->count()) {
            return 0;
        }

        $ids = array_map(function ($document) {
            return mb_substr($document->id, mb_stripos($document->id, '_') + 1);
        }, $result->getIterator()->getArrayCopy());

        return (int) (max($ids));
    }

    /**
     * @param string $documentKey
     *
     * @return int
     */
    private function getNumberOfDocuments($documentKey)
    {
        $select = $this->solariumClient->createQuery(SolariumClient::QUERY_SELECT);
        $select->setQuery(sprintf('id:%s_*', $documentKey));

        $result = $this->solariumClient->select($select);

        return $result->getNumFound();
    }
}
