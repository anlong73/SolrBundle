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

/**
 * List of event which can be fired.
 */
final class Events
{
    const PRE_INSERT = 'solr.pre_insert';
    const POST_INSERT = 'solr.post_insert';

    const PRE_UPDATE = 'solr.pre_update';
    const POST_UPDATE = 'solr.post_update';

    const PRE_DELETE = 'solr.pre_delete';
    const POST_DELETE = 'solr.post_delete';

    const PRE_CLEAR_INDEX = 'solr.pre_clear_index';
    const POST_CLEAR_INDEX = 'solr.post_clear_index';

    const ERROR = 'solr.error';
}
