<?php

/**
 * Provide cli commando (clifs) to commit all solr queue's
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 */
class cli_solr_commit extends CLICommand
{

	/**
	 * Overrides CLICommand::description
	 * The description for help
	 * @var string
	 */
	protected $description = "Send a commit to all configured solr servers.";

	/**
	 * Execute the command.
	 *
	 * @return boolean return true if no errors occured, else false
	 */
	public function execute() {

		// Setup filter.
		$filter = DatabaseFilter::create(SolrServerObj::TABLE)
			->add_column('id')
			->add_column('server');

		// Loop through all servers
		foreach ($filter->select_all() AS $row) {
			CliHelper::console_log(t('Server: @server', array('@server' => $row['server'])), 'ok');

			// Create the server and if available, commit it.
			$service = SolrFactory::create_instance($row['id']);
			if ($service !== false)  {
				$service->commit();
				CliHelper::console_log(t('committed'), 'ok');
			}
			else {
				CliHelper::console_log(t('offline'), 'error');
			}
		}

		return true;
	}

	/**
	 * Overrides CLICommand::on_success
	 * callback for on_success
	 */
	public function on_success() {
	}

}


