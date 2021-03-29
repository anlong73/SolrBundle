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

namespace FS\SolrBundle\Doctrine\ORM\Listener;

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineEmptyCollectionFilter;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use FS\SolrBundle\Doctrine\AbstractIndexingListener;

class EntityIndexerSubscriber extends AbstractIndexingListener implements EventSubscriber
{
    /**
     * @var array
     */
    private $persistedEntities = [];

    /**
     * @var array
     */
    private $deletedRootEntities = [];

    /**
     * @var array
     */
    private $deletedNestedEntities = [];

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['postUpdate', 'postPersist', 'preRemove', 'postFlush'];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (false === $this->isAbleToIndex($entity)) {
            return;
        }

        $doctrineChangeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
        try {
            if (false === $this->hasChanged($doctrineChangeSet, $entity)) {
                return;
            }

            $this->solr->updateDocument($entity);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (false === $this->isAbleToIndex($entity)) {
            return;
        }

        $this->persistedEntities[] = $entity;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (false === $this->isAbleToIndex($entity)) {
            return;
        }

        if ($this->isNested($entity)) {
            $this->deletedNestedEntities[] = $this->emptyCollections($entity);
        } else {
            $this->deletedRootEntities[] = $this->emptyCollections($entity);
        }
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        foreach ($this->persistedEntities as $entity) {
            $this->solr->addDocument($entity);
        }
        $this->persistedEntities = [];

        foreach ($this->deletedRootEntities as $entity) {
            $this->solr->removeDocument($entity);
        }
        $this->deletedRootEntities = [];

        foreach ($this->deletedNestedEntities as $entity) {
            $this->solr->removeDocument($entity);
        }
        $this->deletedNestedEntities = [];
    }

    /**
     * @param object $object
     *
     * @return object
     */
    private function emptyCollections($object)
    {
        $deepcopy = new DeepCopy();
        $deepcopy->addFilter(new DoctrineEmptyCollectionFilter(), new PropertyTypeMatcher('Doctrine\Common\Collections\Collection'));

        return $deepcopy->copy($object);
    }
}
