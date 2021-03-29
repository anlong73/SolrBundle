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

namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

/**
 * @group mapper
 */
class MetaInformationTest extends \PHPUnit\Framework\TestCase
{
    public function testHasCallback_CallbackSet()
    {
        $information = new MetaInformation();
        $information->setSynchronizationCallback('function');

        $this->assertTrue($information->hasSynchronizationFilter(), 'has callback');
    }

    public function testHasCallback_NoCallbackSet()
    {
        $information = new MetaInformation();

        $this->assertFalse($information->hasSynchronizationFilter(), 'has no callback');
    }

    private function createFieldObject($name, $value)
    {
        $value = new \stdClass();
        $value->name = $name;
        $value->value = $value;

        return $value;
    }
}
