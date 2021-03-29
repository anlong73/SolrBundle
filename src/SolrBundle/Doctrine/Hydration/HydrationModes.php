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

namespace FS\SolrBundle\Doctrine\Hydration;

class HydrationModes
{
    /**
     * use only the values from the index. Ignore not indexed db values.
     */
    const HYDRATE_INDEX = 'index';

    /**
     * use values from the index and db. Resulting entity holds also not indexed values.
     */
    const HYDRATE_DOCTRINE = 'doctrine';
}
