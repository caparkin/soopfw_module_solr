<?php

/**
 * Provide a class for a default solr search
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 */
class SolrSearch implements SolrSearchProvider {

	/**
	 * Define field constances.
	 */
	const FACET_ORDER_COUNT = 'count';
	const FACET_ORDER_INDEX = 'index';

	const FACET_METHOD_ENUM = 'enum';
	const FACET_METHOD_FC = 'fc';

	const SORT_ASC = 'asc';
	const SORT_DESC = 'desc';

	/**
	 * Holds the last results from search.
	 *
	 * @var array
	 */
	private $last_result = array();

	/**
	 * The server configuration.
	 *
	 * @var SolrSearchServerConfiguration
	 */
	protected $server_config = null;

	/**
	 * Holds all query fields.
	 *
	 * @var array
	 */
	protected $query_fields = array();

	/**
	 * Holds all facet fields.
	 *
	 * @var array
	 */
	protected $facet_fields = array();

	/**
	 * Holds all facet queries.
	 *
	 * @var array
	 */
	protected $facet_queries = array();

	/**
	 * Holds all facet ranges.
	 *
	 * @var array
	 */
	protected $facet_range = array();

	/**
	 * This holds all parameter which we provide to the solr query.
	 *
	 * @var array
	 */
	protected $query_parameter = array();

	/**
	 * This holds all entries on which we filter.
	 *
	 * @var SolrFilterGroup
	 */
	protected $query_filter = array();

	/**
	 * This holds all fields which should be highlighted
	 *
	 * @var array
	 */
	protected $highlight_fields = array();

	/**
	 * This holds all fields and direction on which we want sort.
	 *
	 * @var array
	 */
	protected $sort = array();

	/**
	 * Construct.
	 *
	 * @param SolrSearchServerConfiguration $server_config
	 *   The server configuration. (optional, default = null)
	 */
	public function __construct(SolrSearchServerConfiguration $server_config = null) {
		$this->server_config = $server_config;
	}

	/**
	 * Set / Add the given field and direction for sorting.
	 *
	 * Mutliple calls on same field is NOT ALLOWED and will produce errors.
	 *
	 * @param string $field
	 *   the field.
	 * @param string $direction
	 *   the direction, use one of SolrSearch::SORT_*
	 *   (optional, default = SolrSearch::SORT_ASC)
	 */
	public function sort($field, $direction = self::SORT_ASC) {
		$this->sort[] = $field . ' ' .$direction;
	}

	/**
	 * Set the solr server config.
	 *
	 * @param SolrSearchServerConfiguration $server_config
	 *   The server configuration.
	 */
	public function set_server_config(SolrSearchServerConfiguration $server_config) {
		$this->server_config = $server_config;
	}

	/**
	 * Adds a field or a SolrFilterGroup object to this group.
	 *
	 * @param string|SolrFilterGroup $key
	 *   the solr field as a string
	 * @param string $value
	 *   the value
	 *
	 * @return SolrSearch Self returning.
	 */
	public function query_filter($field, $value) {
		$this->query_filter[] ="+" . $field . ':' . $value;
		return $this;
	}

	/**
	 * Adds a field to be highlighted.
	 *
	 * Once this method is called the hl param is set to true to automaticly
	 * enable highlighting.
	 *
	 * @param string $field
	 *   the solr field as a string
	 *
	 * @return SolrSearch Self returning.
	 */
	public function highlight_field($field) {
		$this->query_parameter['hl'] = "true";
		$this->highlight_fields[] = $field;
		return $this;
	}

	/**
	 * Set the maximum highlighted snippeds within a field.
	 *
	 * @param int $num
	 *   the number
	 *
	 * @return SolrSearch Self returning.
	 */
	public function highlight_snippets($num) {
		$this->query_parameter['hl.snippets'] = (int)$num;
		return $this;
	}

	/**
	 * Set the max returning length of a highlighted snippet.
	 *
	 * @param int $length
	 *   the char length
	 *
	 * @return SolrSearch Self returning.
	 */
	public function highlight_frag_size($length) {
		$this->query_parameter['hl.fragsize'] = (int)$length;
		return $this;
	}

	/**
	 * Set if all snippets will be merged into one big snippet per field.
	 *
	 * @param boolean $bool
	 *   set to true to merge
	 *
	 * @return SolrSearch Self returning.
	 */
	public function highlight_merge_contiguous($bool) {
		$this->query_parameter['hl.mergeContiguous'] = ($bool === true) ? 'true' : 'false';
		return $this;
	}

	/**
	 * Set if we only get the highlighted field back if the search query could match this field.
	 *
	 * @param boolean $bool
	 *   set to true to merge
	 *
	 * @return SolrSearch Self returning.
	 */
	public function highlight_require_field_match($bool) {
		$this->query_parameter['hl.requireFieldMatch'] = ($bool === true) ? 'true' : 'false';
		return $this;
	}

	/**
	 * Set the max char length which will be analyzed if a snippet can be found.
	 *
	 * @param int $length
	 *   the char length
	 *
	 * @return SolrSearch Self returning.
	 */
	public function highlight_max_analyzed_chars($length) {
		$this->query_parameter['hl.maxAnalyzedChars'] = (int)$length;
		return $this;
	}

	/**
	 * Set the alternate field.
	 *
	 * This field will be returned if no highlight match can be found
	 *
	 * @param string $field
	 *   the field
	 *
	 * @return SolrSearch Self returning.
	 */
	public function highlight_alternate_field($field) {
		$this->query_parameter['hl.alternateField'] = $field;
		return $this;
	}

	/**
	 * Set the max char length which will be returned for the alternate field.
	 *
	 * @param int $length
	 *   the char length
	 *
	 * @return SolrSearch Self returning.
	 */
	public function highlight_max_alternate_field_length($length) {
		$this->query_parameter['hl.maxAlternateFieldLength'] = (int)$length;
		return $this;
	}

	/**
	 * Set the pre and suffix for the highlighted surrounding.
	 *
	 * Default is "<em>" for prefix and "</em>" for suffix
	 *
	 * @param string $prefix
	 *   the prefix
	 * @param string $suffix
	 *   the suffix
	 *
	 * @return SolrSearch Self returning.
	 */
	public function highlight_prefix($prefix, $suffix) {
		$this->query_parameter['hl.simple.pre'] = $prefix;
		$this->query_parameter['hl.simple.post'] = $suffix;
		return $this;
	}

	/**
	 * Adds a field to build a facet.
	 *
	 * Once this method is called the facet param is set to true to automaticly
	 * enable facets.
	 *
	 * @param string $field
	 *   the solr field as a string
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_field($field) {
		$this->query_parameter['facet'] = "true";
		$this->facet_fields[] = $field;
		return $this;
	}

	/**
	 * Adds a query to build a facet.
	 *
	 * Once this method is called the facet param is set to true to automaticly
	 * enable facets.
	 *
	 * @param string $query
	 *   the solr default query string which build a facet
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_query($query) {
		$this->query_parameter['facet'] = "true";
		$this->facet_queries[] = $query;
		return $this;
	}

	/**
	 * Set the facet prefix
	 *
	 * @param string $prefix
	 *   the prefix
	 * @param string $field
	 *   if specified this option affects only the given field (optional, default = '')
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_prefix($prefix, $field = '') {
		$f = 'facet';
		if (!empty($field)) {
			$f = 'f.facet.' . $field;
		}
		$this->query_parameter[$f . '.prefix'] = $prefix;
		return $this;
	}

	/**
	 * Set how the facet are sorted
	 *
	 * @param string $order
	 *   the order use one of SolrSearch::FACET_ORDER_*;
	 * @param string $field
	 *   if specified this option affects only the given field (optional, default = '')
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_sort($order, $field = '') {
		$f = 'facet';
		if (!empty($field)) {
			$f = 'f.facet.' . $field;
		}
		$this->query_parameter[$f . '.sort'] = $order;
		return $this;
	}

	/**
	 * Set the facet limit
	 *
	 * Returns max only the given $limit of facet entries.
	 *
	 * @param int $limit
	 *   the limit
	 * @param string $field
	 *   if specified this option affects only the given field (optional, default = '')
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_limit($limit, $field = '') {
		$f = 'facet';
		if (!empty($field)) {
			$f = 'f.facet.' . $field;
		}
		$this->query_parameter[$f . '.limit'] = (int)$limit;
		return $this;
	}

	/**
	 * Set the facet offset
	 *
	 * @param int $offset
	 *   the offset
	 * @param string $field
	 *   if specified this option affects only the given field (optional, default = '')
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_offset($offset, $field = '') {
		$f = 'facet';
		if (!empty($field)) {
			$f = 'f.facet.' . $field;
		}
		$this->query_parameter[$f . '.offset'] = (int)$offset;
		return $this;
	}

	/**
	 * Set the facet mincount.
	 * The facet will only be returned if the minimum count of results
	 * are greater or equals this value.
	 *
	 * Default: 0 (all)
	 *
	 * @param int $mincount
	 *   the mincount
	 * @param string $field
	 *   if specified this option affects only the given field (optional, default = '')
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_mincount($mincount, $field = '') {
		$f = 'facet';
		if (!empty($field)) {
			$f = 'f.facet.' . $field;
		}
		$this->query_parameter[$f . '.mincount'] = (int)$mincount;
		return $this;
	}

	/**
	 * Set the facet missing.
	 *
	 * Set to "true" this param indicates that in addition to the Term based constraints of a facet field,
	 * a count of all matching results which have no value for the field should be computed
	 *
	 * Default: false.
	 *
	 * @param boolean $bool
	 *   the value
	 * @param string $field
	 *   if specified this option affects only the given field (optional, default = '')
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_missing($bool, $field = '') {
		$f = 'facet';
		if (!empty($field)) {
			$f = 'f.facet.' . $field;
		}
		$this->query_parameter[$f . '.missing'] = ($bool === true) ? 'true' : 'false';
		return $this;
	}

	/**
	 * Set the facet method.
	 *
	 * This parameter indicates what type of algorithm/method to use when faceting a field.
	 *
	 * enum:
	 * Enumerates all terms in a field, calculating the set intersection of documents that match the term with documents that match the query.
	 * This was the default (and only) method for faceting multi-valued fields prior to Solr 1.4.
	 *
	 * fc:
	 * (stands for field cache) The facet counts are calculated by iterating over documents that match the query and summing the terms that appear in each document.
	 * This was the default method for single valued fields prior to Solr 1.4.
	 *
	 * Default: FACET_METHOD_FC
	 *
	 * @param string $method
	 *   the method use one of SolrSearch::FACET_METHOD_*
	 * @param string $field
	 *   if specified this option affects only the given field (optional, default = '')
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_method($method, $field = '') {
		$f = 'facet';
		if (!empty($field)) {
			$f = 'f.facet.' . $field;
		}
		$this->query_parameter[$f . '.method'] = $method;
		return $this;
	}

	/**
	 * Adds a field to build a facet rage.
	 *
	 * Once this method is called the facet param is set to true to automaticly
	 * enable facets.
	 *
	 * @param string $field
	 *   the solr field as a string
	 * @param mixed $start
	 *   where the range starts
	 * @param mixed $end
	 *   where the range ends
	 * @param mixed $gab
	 *   the field gap (optional, default = '')
	 *
	 * @return SolrSearch Self returning.
	 */
	public function facet_range($field, $start, $end, $gab = '') {
		$this->query_parameter['facet'] = "true";
		$this->facet_range[] = $field;
		$this->query_parameter['f.' . $field . '.facet.range.start'] = $start;
		$this->query_parameter['f.' . $field . '.facet.range.end'] = $end;
		$this->query_parameter['f.' . $field . '.facet.range.gap'] = $gab;
		return $this;
	}

	/**
	 * Returns the defType search method.
	 *
	 * @return string The solr search method.
	 */
	public function get_type() {
		return "func";
	}

	/**
	 * Returns all query parameters.
	 *
	 * @return array An array with all parameters which can be used to generate a get or post request.
	 */
	public function get_query_parameter() {
		$params = $this->query_parameter;
		$params['defType'] = $this->get_type();

		if (!empty($this->query_filter)) {
			ksort($this->query_filter);
			$params['fq'] = implode(" ", $this->query_filter);
		}

		if (!empty($this->highlight_fields)) {
			$params['hl.fl'] = implode(",", $this->highlight_fields);
		}

		if (!empty($this->sort)) {
			$params['sort'] = implode(",", $this->sort);
		}

		if (!empty($this->facet_fields)) {
			$params['facet.field'] = $this->facet_fields;
		}

		if (!empty($this->facet_queries)) {
			$params['facet.query'] = $this->facet_queries;
		}

		if (!empty($this->facet_range)) {
			$params['facet.range'] = $this->facet_range;
		}

		return $params;
	}

	/**
	 * Executes the search.
	 *
	 * @param string $q
	 *   the search string provided by the user.
	 * @param int $limit
	 *   the limit (optional, default = 10)
	 * @param int $offset
	 *   the offset (optional, default = 0)
	 * @param SolrSearchServerConfiguration $config
	 *   the configuration to get the server which will be used.
	 *
	 * @return array|boolean Returns false if the server could not be found, else
	 *   an array with the response.
	 *
	 *   The response array includes normaly:
	 *   array(
	 *      'numFound' => The number of found docs,
	 *      'start' => Where we start (offset),
	 *      'docs' => All current docs within the limit,
	 *   )
	 *
	 *   If highlighting is enabled, every row has a field "highlighted" which is the
	 *   returning highlighting result for this row.
	 *
	 *   Notice:
	 *   If facets are enabled the facets will be appended to the returning array like:
	 *   array(
	 *      'numFound' => The number of found docs,
	 *      'start' => Where we start (offset),
	 *      'docs' => All current docs within the limit,
	 *      'facets' => the found facets
	 *   )
	 *
	 * @throws SoopfwSolrException Will be thrown if it can't connect the solr service server.
	 */
	public function search($s, $limit = 10, $offset = 0, SolrSearchServerConfiguration $config = null) {

		$server_config = $this->server_config;

		if (!empty($config)) {
			$server_config = $config;
		}

		if (empty($server_config)) {
			return false;
		}

		$params = $this->get_query_parameter();
		if ($server_config->is_set(SolrSearchServerConfiguration::SOLR_INSTANCE)) {
			$server = $server_config->get(SolrSearchServerConfiguration::SOLR_INSTANCE);
		}
		else {
			$server = SolrFactory::create_instance(SolrSearchServerConfiguration::DB_CONFIG_MODULE, SolrSearchServerConfiguration::DB_CONFIG_KEY);
		}

		if (empty($server)) {
			throw new SoopfwSolrException(t('Could not connect to the solr server instance'));
		}

		$response = $server->search($s, $offset, $limit, $params, $server_config->get(SolrSearchServerConfiguration::SEARCH_METHOD, Apache_Solr_Service::METHOD_GET));

		$return = json_decode($response->getRawResponse(), true);
		$results = $return['response'];
		if (!empty($return['highlighting'])) {
			foreach ($results['docs'] AS &$row) {
				if (isset($return['highlighting'][$row['id']])) {
					$row['highlighted'] = $return['highlighting'][$row['id']];
				}
			}
 		}

		if (!empty($return['facet_counts'])) {
			$results['facets'] = $return['facet_counts'];
 		}

		$this->last_result = $results;
		return $results;
	}

	/**
	 * Returns the results from the search.
	 *
	 * @return array The results
	 */
	public function get_results() {
		if (empty($this->last_result)) {
			return array();
		}

		return $this->last_result['docs'];
	}

	/**
	 * Returns the complete result count (ignores the limit count)
	 *
	 * @return int The complete result count
	 */
	public function get_result_count() {
		if (empty($this->last_result['numFound'])) {
			return 0;
		}

		return $this->last_result['numFound'];
	}

	/**
	 * Returns all found facets, or if $field is provided the facets for that field.
	 *
	 * @param string $field
	 *   if provided we will only get back the facet for the given field. (optional, default = '')
	 * @return array The facet results or false on error.
	 */
	public function get_result_facets($field = '') {
		if (!isset($this->last_result['facets'])) {
			return false;
		}

		if (!empty($field)) {
			if (!isset($this->last_result['facets']['facet_fields']) || !isset($this->last_result['facets']['facet_fields'][$field])) {
				return false;
			}
			return $this->last_result['facets']['facet_fields'][$field];
		}

		$this->last_result['facets']['facet_fields'];
	}
}

