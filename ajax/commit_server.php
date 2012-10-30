<?php
/**
 * Provides an ajax request to commit all pending changes to the given solr server entry.
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 * @category Module.Solr
 */
class AjaxSolrCommitServer extends AjaxModul {

	/**
	 * This function will be executed after ajax file initializing
	 */
	public function run() {

		//Initalize param struct
		$params = new ParamStruct();
		$params->add_required_param("id", PDT_INT);

		// Fill the params.
		$params->fill();

		//Parameters are missing
		if (!$params->is_valid()) {
			throw new SoopfwMissingParameterException();
		}

		//Right missing
		if (!$this->core->get_right_manager()->has_perm("admin.solr.manage")) {
			throw new SoopfwNoPermissionException();
		}

		//Load the solr server
		$factory = SolrFactory::create_instance($params->id);

		// If provided id is not valid.
		if (empty($factory)) {
			throw new SoopfwWrongParameterException(t('No such solr server or the server is not reachable.'));
		}

		//Try to commit.
		try {
			$factory->commit(false, false);
			AjaxModul::return_code(AjaxModul::SUCCESS);

		}
		catch(Exception $e) {

		}
		AjaxModul::return_code(AjaxModul::ERROR_DEFAULT);
	}
}
