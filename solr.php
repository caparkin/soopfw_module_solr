<?php

/**
 * Solr action module
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 */
class Solr extends ActionModul
{
	/**
	 * The default method
	 * @var string
	 */
	protected $default_methode = self::NO_DEFAULT_METHOD;

	/**
	 * Define config constances.
	 */
	const CONFIG_COMMIT_INTERVAL = 'commit_interval';

	/**
	 * Implementation of get_admin_menu()
	 *
	 * @return array the menu
	 */
	public function get_admin_menu() {
		return array(
			55 => array(//Order id, same order ids will be unsorted placed behind each
				'#id' => 'soopfw_solr', //A unique id which will be needed to generate the submenu
				'#title' => t("Solr"), //The main title
				'#perm' => 'admin.solr', //Perm needed
				'#childs' => array(
					array(
						'#title' => t("Manage"), //The main title
						'#link' => "/admin/solr/manage", // The main link
						'#perm' => "admin.solr.manage", // perms needed
					),
					array(
						'#title' => t("Config"), //The main title
						'#link' => "/admin/solr/config", // The main link
						'#perm' => "admin.solr.manage", // perms needed
					),
				)
			)
		);
	}

	/**
	 * Implements hook: cron
	 *
	 * Allow other modules to run cron's
	 */
	public function hook_cron(Cron &$cron) {
		// Get the intervall when the solr index actions will be committed
		$runtime = (int)$this->core->get_dbconfig("solr", self::CONFIG_COMMIT_INTERVAL, 1);
		if (!empty($runtime)) {
			// Commit all actions.
			if (((strtotime(date('Y-m-d H:i:00', TIME_NOW))/60) % $runtime) === 0) {
				$cli = new cli_solr_commit();
				$cli->execute();
			}
		}
	}

	/**
	 * Action: config
	 *
	 * Configurate the solr main settings.
	 */
	public function config() {
		//Check perms
		if (!$this->right_manager->has_perm('admin.solr.manage', true)) {
			throw new SoopfwNoPermissionException();
		}

		// Setting up title and description.
		$this->title(t("Solr config"), t("Here we can configure the solr settings"));

		// Configurate the settings form.
		$form = new SystemConfigForm($this, "solr_config");

		$form->add(new Fieldset('main_config', t('Main')));
		$form->add(new Textfield(self::CONFIG_COMMIT_INTERVAL, (int)$this->core->get_dbconfig("solr", self::CONFIG_COMMIT_INTERVAL, '5'), t("Commit interval (minutes)"), t('Define the minutes how often a solr commit should be automatically executed.')));

		// Execute the settings form.
		$form->execute();
	}

	/**
	 * Action: manage
	 *
	 * Display and/or search all servers
	 */
	public function manage() {
		//Check perms
		if (!$this->right_manager->has_perm("admin.solr.manage", true)) {
			throw new SoopfwNoPermissionException();
		}

		// Setting up title and description.
		$this->title(t("Manage Solr server"), t("Here we can manage and configurate the Solr servers"));

		//Setup search form
		$form = new SessionForm("search_solr_overview", t("Search server:"));
		$form->add(new Textfield("server", '', t('Servername')));
		$form->add(new Submitbutton("search", t("Search")));
		$form->assign_smarty();

		//Check form and add errors if form is not valid
		$form->check_form();

		$filter = new DatabaseFilter(SolrServerObj::TABLE);

		// Fill the database filter.
		foreach ($form->get_values() AS $field => $val) {
			if (empty($val)) {
				continue;
			}
			$filter->add_where($field, $this->db->get_sql_string_search($val, "*.*", false), 'LIKE');
		}

		//Init pager
		$pager = new Pager(50, $filter->select_count());
		$pager->assign_smarty("pager");

		// Setup paging limit and offset
		$filter->limit($pager->max_entries_per_page());
		$filter->offset($pager->get_offset());

		//Assign found results
		$this->smarty->assign_by_ref("servers", $filter->select_all());
	}

	/**
	 * Action: save_server
	 *
	 * Save or create a solr server, if $id is provided update the current one
	 * if left empty it will create a new server
	 *
	 * @param int $id
	 *   the server id (optional, default = "")
	 */
	public function save_server($id = "") {
		if (!$this->right_manager->has_perm("admin.solr.manage", true)) {
			throw new SoopfwNoPermissionException();
		}

		$this->static_tpl = 'form.tpl';

		// Setup object form.
		$form = new ObjForm(new SolrServerObj($id), "");
		$form->set_ajax(true);
		$form->add_js_success_callback("save_server_success");
		$form->add(new Submitbutton("insert", t("Save")));

		// Check if form was submitted.
		if ($form->check_form()) {

			// Start transaction.
			$form->get_object()->transaction_auto_begin();

			// If save operation succeed.
			if ($form->save_or_insert()) {

				$obj = $form->get_object();
				$factory = new SolrFactory();

				if ($factory->create_instance($obj->server)) {

					// Setup success message.
					$form->get_object()->transaction_auto_commit();
					$this->core->message("Server saved ", Core::MESSAGE_TYPE_SUCCESS, true, $form->get_values(true));

				}
				else {

					// Server infos not correct.
					$form->get_object()->transaction_auto_rollback();
					$this->core->message("Could not connect to Solr service", Core::MESSAGE_TYPE_ERROR, true);

				}

			}
			else {

				// Else setup error message.
				$form->get_object()->transaction_auto_rollback();
				$this->core->message("Error while saving server", Core::MESSAGE_TYPE_ERROR, true);

			}
		}
	}
}