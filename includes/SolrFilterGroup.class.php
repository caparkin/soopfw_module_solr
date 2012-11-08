<?php

/**
 * Provides a solr filter group.
 * Every group has an own operator type (AND/OR) for the elements within the container.
 * You can also provide a SolrFilterGroup object to the "add" function to build up complex filter statements
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 */
class SolrFilterGroup
{
	/**
	 * Operators
	 */
	const TYPE_AND = 'AND';
	const TYPE_OR = 'OR';

	/**
	 * The link type
	 *
	 * @var string
	 */
	private $type = "AND";

	/**
	 * The grouped conditions.
	 *
	 * @var array
	 */
	private $conditions = array();

	/**
	 * Construct.
	 *
	 * @param string $type
	 *   the link type use one of SolrFilterGroup::TYPE_* (optional, default = SolrFilterGroup::TYPE_AND)
	 */
 	public function __construct($type = SolrFilterGroup::TYPE_AND) {
		$this->type = $type;
	}

	/**
	 * Adds a field or a complete SolrFilterGroup object to this group.
	 *
	 * @param string|SolrFilterGroup $key
	 *   the solr field as a string or an SolrFilterGroup object
	 * @param string $value
	 *   The value.
	 *   This is only optional if the key is a SolrFilterGroup object (optional, default = NS)
	 *
	 * @return SolrFilterGroup
	 *   The solr filter group
	 */
	public function add($key, $value = NS) {
		//If we just provided a solr filter group object, add this
		if ($value === NS && $key instanceof SolrFilterGroup) {
			$this->conditions[] = $key;
		}
		//Else we need to have setup the condition.
		else if ($value !== NS) {
			//Add the condition
			$this->conditions[] = array(
				'key' => $key,
				'value' => $value,
			);
		}
		return $this;
	}


	/**
	 * Return the solr filter statement.
	 *
	 * @return string
	 *   The solr filter statement string.
	 */
	public function get_filter() {
		$tmp_array = array();

		// Loop through all available conditions.
		foreach ($this->conditions AS $v) {
			// If we have a solr filter group, get the statement from this object.
			if ($v instanceof SolrFilterGroup) {
				$tmp_array[] = $v->get_sql(false);
				continue;
			}

			// Escape the value.
			$val = urlencode($v['value']);


			// Add the condition string.
			$tmp_array[] = $v['key'] . ":" . $val;
		}

		// Transform the conditions into a string and link them with the configured link type.
		$where_str = implode(" " . $this->type . " ", $tmp_array);
		$where = "";
		if (!empty($where_str)) {
			$where = " (" . $where_str . ") ";
		}

		return $where;
	}

}

