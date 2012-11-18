<?php

/**
 * Provides a configuration for solr search.
 *
 * This will be provided to the SolrSearch class
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 * @category Configurations
 */
class SolrSearchServerConfiguration extends Configuration
{
	/**
	 * Is used to get the server by module database configuration.
	 * This is the database configuration key.
	 *
	 * Notice:
	 * If configuration key SOLR_INSTANCE is configurated, this config is not used.
	 *
	 * @param string
	 */
	const DB_CONFIG_KEY = 0;

	/**
	 * Is used to get the server by module database configuration.
	 * This is the database configuration module.
	 *
	 * Notice:
	 * If configuration key SOLR_INSTANCE is configurated, this config is not used.
	 *
	 * @param string
	 */
	const DB_CONFIG_MODULE = 1;

	/**
	 * Is used to get the server directly as a Apache_Solr_Service object.
	 * This is the Apache_Solr_Service object.
	 *
	 * Notice:
	 * If all configurations are set this will be used because it needs less
	 * performance.
	 *
	 * @param Apache_Solr_Service
	 */
	const SOLR_INSTANCE = 2;

	/**
	 * The http method to be used.
	 * Can be one of Apache_Solr_Service::METHOD_* (METHOD_GET or METHOD_POST)
	 *
	 * @param string
	 */
	const SEARCH_METHOD = 3;

}