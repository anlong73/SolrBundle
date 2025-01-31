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

namespace FS\SolrBundle\Tests\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use FS\SolrBundle\Doctrine\Annotation as Solr;
use FS\SolrBundle\Tests\Doctrine\Mapper\date;
use FS\SolrBundle\Tests\Doctrine\Mapper\text;
use FS\SolrBundle\Tests\Doctrine\Mapper\the;

/**
 * @Solr\Document(boost="1")
 */
class ValidTestEntityWithCollection
{
    /**
     * @Solr\Id
     */
    private $id;

    /**
     * @Solr\Field(type="text")
     *
     * @var string
     */
    private $text;

    /**
     * @Solr\Field()
     *
     * @var string
     */
    private $title;

    /**
     * @Solr\Field(type="date")
     *
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var ArrayCollection
     *
     * @Solr\Field(type="strings", getter="getTitle")
     */
    private $collection;

    /**
     * @var ArrayCollection
     *
     * @Solr\Field(type="strings")
     */
    private $collectionNoGetter;

    /**
     * @Solr\Field(type="my_costom_fieldtype")
     *
     * @var string
     */
    private $costomField;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $costomField
     */
    public function setCostomField($costomField)
    {
        $this->costomField = $costomField;
    }

    /**
     * @return string
     */
    public function getCostomField()
    {
        return $this->costomField;
    }

    /**
     * @return ArrayCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param ArrayCollection $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return ArrayCollection
     */
    public function getCollectionNoGetter()
    {
        return $this->collectionNoGetter;
    }

    public function setCollectionNoGetter(ArrayCollection $collectionNoGetter)
    {
        $this->collectionNoGetter = $collectionNoGetter;
    }
}
