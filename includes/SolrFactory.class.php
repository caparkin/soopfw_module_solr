<?php

/**
 * Provides a solr factory
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 * @category Module
 */
class SolrFactory extends Object
{

	/**
	 * Create a Apache_Solr_Service instance and return it or return false
	 *
	 * @param mixed $server
	 *   the servername or server id which must be configured over the solr manager.
	 *
	 *   It can also be a database config scope (module name)
	 *
	 *   Note if $server is a scope:
	 *
	 *   Normaly the module configuration will use Core::dbconfig() to store the server id, which should be used.
	 *   The module will save the server id usually with the modul name as the "scope" and the config key will hold the server id.
	 *
	 *   For example the content modul will store the serverid in
	 *   "content" as the scope and "solr_server" as key
	 *   therefore $server would be "content" and $config_key = "solr_server"
	 *
	 *   The scope behavour will be tried within the last check if we can not get a
	 *   valid SolrServerObj.
	 *   So if you provide $config_key but an integer for $server which is found
	 *   within the server id's it will directly load the server id and do not
	 *   search within the scope behaviour
	 * @param string $config_key
	 *   the config key where the server id is stored (optional, default = NS)
	 *
	 * @return Apache_Solr_Service
	 *   The solr client or false on error.
	 */
	public static function create_instance($server, $config_key = NS) {
		static $cache = array();

		//Get the server object and try to load it.

		// Check if we provided a server id.
		if(preg_match("/^[0-9]+$/", $server)) {
			$object = new SolrServerObj($server);
		}
		else {
			// Check if we provided a valid servername.
			$object = new SolrServerObj();
			$object->db_filter->add_where("server", $server);
			$object->load();
		}

		//Check if server exist, if not try scope behaviour.
		if (!$object->load_success() && $config_key !== NS) {
			$server_id = $object->get_core()->dbconfig($server, $config_key);
			$object = new SolrServerObj((int)$server_id);
		}

		// if server does not exist here we tried all formats and must give up.
		if (!$object->load_success()) {
			return false;
		}

		if(!isset($cache[$object->id])) {

			// Get all server configurations.
			$options = $object->get_values(true);

			// Init the solr client.
			$client = new Apache_Solr_Service($options['host'], $options['port'], $options['path'], new Apache_Solr_HttpTransport_Curl());

			// Check if solr service is connectable.
			if (!$client->ping()) {
				$client = false;
			}
			$cache[$object->id] = $client;
		}

		return $cache[$object->id];
	}

	/**
	 * Returns an array with all configured servers.
	 *
	 * @param boolean $include_empty
	 *   If set to true it will have an empty entry with 'none' as key and 'None' as value at the
	 *   first array index.
	 *   Usefull for configurations (optional, default = false)
	 *
	 * @return array The data.
	 */
	public function get_all_instances($include_empty = false) {
		$filter = DatabaseFilter::create(SolrServerObj::TABLE)
			->add_column('id')
			->add_column('server');

		if ($include_empty === true) {
			return array('none' => t('None')) + $filter->select_all('id', true);
		}
		return $filter->select_all('id', true);
	}

}

