<?php

/**
 * The solr server object which holds all our configured solr servers
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 * @package modules.solr.objects
 */
class SolrServerObj extends AbstractDataManagment
{
	/**
	 * Define constances
	 */
	const TABLE = 'solr_servers';

	/**
	 * Constructor
	 *
	 * @param int $id
	 *   the server id (optional, default = 0)
	 * @param boolean $force_db
	 *   if we want to force to load the data from the database (optional, default = false)
	 */
	public function __construct($id = 0, $force_db = false) {
		parent::__construct();
		$this->db_struct = new DbStruct(self::TABLE);
		$this->db_struct->set_cache(true);

		$this->db_struct->add_reference_key("id");
		$this->db_struct->set_auto_increment("id");
		
		$this->db_struct->add_hidden_field("id", t("Solr ServerID"), PDT_INT);
		$this->db_struct->add_required_field("server", t("Solr Servername"), PDT_STRING, 'default');
		$this->db_struct->add_required_field("host", t("Solr host"), PDT_STRING, $this->core->core_config('core', 'domain'));
		$this->db_struct->add_required_field("port", t("Solr port"), PDT_INT, 8080, 'UNSIGNED');
		$this->db_struct->add_required_field("path", t("Solr path"), PDT_STRING, '/solr');
		$this->db_struct->add_field("login", t("Solr username"), PDT_STRING);
		$this->db_struct->add_field("password", t("Solr password"), PDT_PASSWORD);

		$this->db_struct->add_index(MysqlTable::INDEX_TYPE_UNIQUE, 'server');

		$this->set_default_fields();

		if (!empty($id)) {
			if (!$this->load($id, $force_db)) {
				return false;
			}
		}
	}

}

