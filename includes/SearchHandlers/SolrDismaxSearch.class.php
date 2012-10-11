<?php

/**
 * Provide a class for a dismax solr search provider
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 * @package solr.includes
 */
class SolrDismaxSearch extends SolrSearch implements SolrSearchProvider {

	/**
	 * Holds all phrase fields.
	 *
	 * @var array
	 */
	protected $phrase_fields = array();

	/**
	 * Holds all phrase fields with bi-gram texts.
	 *
	 * @var array
	 */
	protected $phrase_fields2 = array();

	/**
	 * Holds all phrase fields with tri-gram texts.
	 *
	 * @var array
	 */
	protected $phrase_fields3 = array();

	/**
	 * Holds a list of conditions for mm param.
	 *
	 * @see http://lucene.apache.org/solr/api-4_0_0-BETA/org/apache/solr/util/doc-files/min-should-match.html
	 * @var array
	 */
	protected $minimum_should_match = array();

	/**
	 * Returns the defType search method.
	 *
	 * @return string
	 *   The solr search method.
	 */
	public function get_type() {
		return "edismax";
	}

	/**
	 * Adds a query field.
	 *
	 * @param string $name
	 *   the field name
	 * @param float $boost
	 *   the boost parameter (optional, default = 0)
	 *
	 * @return SolrDismaxSearch Self returning.
	 */
	public function &query_field($name, $boost = 0) {
		$val = $name;

		// Boost the field.
		if ($boost > 0) {
			$val .= "^" . $boost;
		}
		$this->query_fields[$name] = $val;
		return $this;
	}

	/**
	 * Configures one clause option for the mm param.
	 *
	 * For detailed information please look at:
	 * http://lucene.apache.org/solr/api-4_0_0-BETA/org/apache/solr/util/doc-files/min-should-match.html
	 *
	 * $value is only optional if you do not add a mm value again.
	 *
	 * @param int $clauses
	 *   the condition num of optional clauses
	 * @param mixed $value
	 *   can be an positive or negative integer or a string with %-char.
	 *   %-Char can also be positive or negative. (optional, default = "")
	 *
	 * @see http://lucene.apache.org/solr/api-4_0_0-BETA/org/apache/solr/util/doc-files/min-should-match.html
	 *
	 * @return SolrDismaxSearch Self returning.
	 */
	public function minimum_should_match($clauses, $value = "") {
		$this->minimum_should_match[(int)$clauses] = (empty($value)) ? $clauses : $clauses . '<' . $value;
		return $this;
	}

	/**
	 * Sets the slop count for explizit query fields.
	 *
	 * The slop count represents the max word count where the provided explicit query
	 * will find "near" neighbours.
	 * For example you have a string like:
	 * "The see would be very nice tonight."
	 * and you search for "see nice"
	 * You will get a higher score if slop count is set to 4 instead if you have
	 * slop count 2 because with slop count 4 the search will find "nice" is near "see"
	 * because the word count between them are within the slop count value.
	 *
	 * @param int $slop
	 *   the slop count for explicit (string which are surrounded by "") search texts.
	 *
	 * @return SolrDismaxSearch Self returning.
	 */
	public function query_slops($slop) {
		$this->query_parameter['qs'] = (int)$slop;
		return $this;
	}

	/**
	 * Set the phrase fields.
	 *
	 * Here we can boost the fields which are matched within the query slops
	 * The different is that the value set by query_slop does not effect this
	 * phrase check. To set the slop count for this you have to use "phrase_slop"
	 *
	 * @param string $name
	 *   the field name
	 * @param float $boost
	 *   the boost parameter
	 *
	 * @return SolrDismaxSearch Self returning.
	 */
	public function phrase_fields($name, $boost) {
		$val = $name;

		// Boost the field.
		if ($boost > 0) {
			$val .= "^" . $boost;
		}
		$this->phrase_fields[$name] = $val;

		return $this;
	}

	/**
	 * Sets the slop count for phrase searches within the fields set by phrase_fields, phrase_fields2 and phrase_fields3.
	 *
	 * The slop count represents the max word count where the provided explicit query
	 * will find "near" neighbours.
	 * For example you have a string like:
	 * The see would be very nice tonight.
	 * and you search for "see nice"
	 * You will get a higher score if slop count is set to 4 instead if you have
	 * slop count 2 because with slop count 4 the search will find "nice" is near "see"
	 * because the word count between them are within the slop count value.
	 *
	 * @param int $slop
	 *   the slop count for explicit (string which are surrounded by "") search texts.
	 *
	 * @return SolrDismaxSearch Self returning.
	 */
	public function phrase_slops($slop) {
		$this->query_parameter['ps'] = (int)$slop;
		return $this;
	}

	/**
	 * Set the phrase fields same as phrase_fields but with bi-gram text.
	 *
	 * Here we can boost the fields which are matched within the query slops
	 * The different is that the value set by query_slop does not effect this
	 * phrase check. To set the slop count for this you have to use "phrase_slop"
	 *
	 * @param string $name
	 *   the field name
	 * @param float $boost
	 *   the boost parameter
	 *
	 * @return SolrDismaxSearch Self returning.
	 */
	public function phrase_fields2($name, $boost) {
		$val = $name;

		// Boost the field.
		if ($boost > 0) {
			$val .= "^" . $boost;
		}
		$this->phrase_fields2[$name] = $val;

		return $this;
	}

	/**
	 * Set the phrase fields.
	 *
	 * Here we can boost the fields which are matched within the query slops
	 * The different is that the value set by query_slop does not effect this
	 * phrase check. To set the slop count for this you have to use "phrase_slop"
	 *
	 * @param string $name
	 *   the field name
	 * @param float $boost
	 *   the boost parameter
	 *
	 * @return SolrDismaxSearch Self returning.
	 */
	public function phrase_fields3($name, $boost) {
		$val = $name;

		// Boost the field.
		if ($boost > 0) {
			$val .= "^" . $boost;
		}
		$this->phrase_fields3[$name] = $val;

		return $this;
	}

	/**
	 * Set the tie breaker value
	 *
	 * Float value to use as tiebreaker in DisjunctionMaxQueries (should be something much less than 1)
	 * When a term from the users input is tested against multiple fields,
	 * more than one field may match and each field will generate a different score based
	 * on how common that word is in that field (for each document relative to all other documents).
	 *
	 * By default the score from the field with the maximum score is used.
	 * If two documents both have a matching score, the tie parameter has the effect of breaking the tie.
	 * When a tie parameter is specified the scores from other matching fields are added to the score of the maximum scoring field:
	 * (score of matching clause with the highest score) + ( (tie paramenter) * (scores of any other matching clauses) )
	 * The "tie" param let's you configure how much the final score of the query
	 * will be influenced by the scores of the lower scoring fields compared to the highest scoring field.
	 *
	 * A value of "0.0" makes the query a pure "disjunction max query"
	 * -- only the maximum scoring sub query contributes to the final score.
	 *
	 * A value of "1.0" makes the query a pure "disjunction sum query" where it doesn't matter what the maximum scoring sub query is,
	 * the final score is the sum of the sub scores.
	 *
	 * Typically a low value (ie: 0.1) is useful.
	 *
	 * @param float $tie
	 *   the tie breaker value
	 *
	 * @return SolrDismaxSearch Self returning.
	 */
	public function tie_breaker($tie) {
		$this->query_parameter['tie'] = (float)$tie;
		return $this;
	}

	/**
	 * Returns all query parameters
	 *
	 * @return array An array with all parameters which can be used to generate a get or post request
	 */
	public function get_query_parameter() {

		$params = parent::get_query_parameter();

		$params['qf'] = implode(" ", $this->query_fields);

		if (!empty($this->phrase_fields)) {
			$params['pf'] = implode(" ", $this->phrase_fields);
		}

		if (!empty($this->phrase_fields2)) {
			$params['pf2'] = implode(" ", $this->phrase_fields2);
		}

		if (!empty($this->phrase_fields3)) {
			$params['pf3'] = implode(" ", $this->phrase_fields3);
		}

		if (!empty($this->minimum_should_match)) {
			ksort($this->minimum_should_match);
			$params['mm'] = implode(" ", $this->minimum_should_match);
		}

		return $params;
	}
}

