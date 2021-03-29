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

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Hydration\DoctrineValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

class DoctrineValueHydratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function skipArrays()
    {
        $hydrator = new DoctrineValueHydrator();

        $this->assertFalse($hydrator->mapValue('createdAt', [], new MetaInformation()));
    }

    /**
     * @test
     */
    public function skipObjects()
    {
        $hydrator = new DoctrineValueHydrator();

        $field = new Field(['type' => 'datetime']);
        $field->name = 'createdAt';
        $field->getter = 'format(\'Y-m-d\TH:i:s.z\Z\')';

        $metaInformation = new MetaInformation();
        $metaInformation->setFields([$field]);

        $this->assertFalse($hydrator->mapValue('createdAt', new \DateTime(), $metaInformation));
    }

    /**
     * @test
     */
    public function mapCommonType()
    {
        $hydrator = new DoctrineValueHydrator();

        $field = new Field(['type' => 'string']);
        $field->name = 'title';

        $metaInformation = new MetaInformation();
        $metaInformation->setFields([$field]);

        $this->assertTrue($hydrator->mapValue('title_s', 'a title', $metaInformation));
    }
}
