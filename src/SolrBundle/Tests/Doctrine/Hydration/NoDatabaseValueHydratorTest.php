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

namespace FS\SolrBundle\Tests\Doctrine\Hydration;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Hydration\NoDatabaseValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;

class NoDatabaseValueHydratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function doNotCutIdFields()
    {
        $reader = new AnnotationReader(new DoctrineAnnotationReader());
        $hydrator = new NoDatabaseValueHydrator();

        $document = new SolrDocumentStub([
            'id' => '0003115-2231_S',
            'title_t' => 'fooo_bar',
        ]);

        $entity = new ValidTestEntity();

        $metainformations = new MetaInformationFactory($reader);
        $metainformations = $metainformations->loadInformation($entity);

        $entity = $hydrator->hydrate($document, $metainformations);

        $this->assertEquals('0003115-2231_S', $entity->getId());
        $this->assertEquals('fooo_bar', $entity->getTitle());
    }
}
