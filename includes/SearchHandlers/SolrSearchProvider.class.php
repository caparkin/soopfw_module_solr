<?php

/**
 * Provide an interface for a solr search provider.
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 * @category Search
 */
interface SolrSearchProvider
{

	/**
	 * Returns all query parameters.
	 *
	 * @return array An array with all parameters which can be used to generate a get or post request.
	 */
	public function get_query_parameter();

	/**
	 * Executes the search.
	 *
	 * @param string $q
	 *   the search string provided by the user.
	 * @param int $offset
	 *   the offset. (optional, default = 0)
	 * @param int $limit
	 *   the limit. (optional, default = 0)
	 * @param SolrSearchServerConfiguration $config
	 *   the configuration to get the server which will be used.
	 *
	 * @return array An array with Apache_Solr_Document objects.
	 *
	 * @throws SoopfwSolrException Will be thrown if it can't connect the solr service server.
	 */
	public function search($s, $offset = 0, $limit = 0, SolrSearchServerConfiguration $config = null);
}

