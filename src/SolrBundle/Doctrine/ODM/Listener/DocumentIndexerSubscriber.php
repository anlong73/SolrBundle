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

namespace FS\SolrBundle\Doctrine\ODM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use FS\SolrBundle\Doctrine\AbstractIndexingListener;

class DocumentIndexerSubscriber extends AbstractIndexingListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['postUpdate', 'postPersist', 'preRemove'];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();

        try {
            $doctrineChangeSet = $args->getDocumentManager()->getUnitOfWork()->getDocumentChangeSet($document);

            if (false === $this->hasChanged($doctrineChangeSet, $document)) {
                return;
            }

            $this->solr->updateDocument($document);
        } catch (\RuntimeException $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getDocument();

        try {
            $this->solr->removeDocument($entity);
        } catch (\RuntimeException $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getDocument();

        try {
            $this->solr->addDocument($entity);
        } catch (\RuntimeException $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
