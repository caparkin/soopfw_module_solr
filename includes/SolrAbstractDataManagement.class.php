<?php
/**
 * This abstract class provides an easy interface to store the saved data also within the given solr server instance.
 * Notice: Use this only on own modules where solr is always be present, else it can not find this class.
 *
 * @copyright Christian Ackermann (c) 2010 - End of life
 * @author Christian Ackermann <prdatur@gmail.com>
 * @package modules.solr.includes
 */
abstract class SolrAbstractDataManagement extends AbstractDataManagment
{
	/**
	 * Save the given Data
	 *
	 * @param boolean $save_if_unchanged
	 *   Save this object even if no changes to it's values were made (optional, default = false)
	 * @param boolean $commit
	 *  if set to true it will auto commit the changes, you need then
	 *  do this manualy (optional, default = false)
	 *
	 * @return boolean true on success, else false
	 */
	public function save($save_if_unchanged = false, $commit = false) {
		$update_solr = ($save_if_unchanged || !empty($this->values_changed));
		if (parent::save($save_if_unchanged)) {
			if ($update_solr === true) {
				$this->update_solr($commit);
			}
			return true;
		}
		return false;
	}

	/**
	 * Insert the current data
	 *
	 * @param boolean $ignore
	 *   Don't throw an error if data is already there (optional, default=false)
	 * @param boolean $commit
	 *  if set to true it will auto commit the changes, you need then
	 *  do this manualy (optional, default = false)
	 *
	 * @return boolean true on success, else false
	 */
	public function insert($ignore = false, $commit = false) {
		if (parent::insert($ignore)) {
			if (!empty($this->values_changed)) {
				$this->update_solr($commit);
			}
			return true;
		}
		return false;
	}

	/**
	 * Delete the given data
	 *
	 * @param boolean $commit
	 *  if set to true it will auto commit the changes, you need then
	 *  do this manualy (optional, default = false)
	 *
	 * @return boolean true on success, else false
	 */
	public function delete($commit = false) {
		if (!parent::delete()) {
			return false;
		}
		$this->delete_solr($commit);
		return true;
	}

	/**
	 * Save if data is already there, else insert current data
	 *
	 * @param boolean $commit
	 *  if set to true it will auto commit the changes, you need then
	 *  do this manualy (optional, default = false)
	 *
	 * @return boolean true on success, else false
	 */
	public function save_or_insert($commit = false) {
		if ($this->load_success()) {
			return $this->save(false, $commit);
		}
		else {
			return $this->insert(false, $commit);
		}
	}

	/**
	 * Deletes the given id from solr index.
	 *
	 * @param boolean $commit
	 *  if set to true it will auto commit the changes, you need then
	 *  do this manualy (optional, default = false)
	 */
	public function delete_solr($commit = false) {

		// We can not store it within solr because we have no data.
		if ($this->load_success() === false) {
			return;
		}

		$solr = $this->get_solr_server();
		if ($solr !== false) {
			$solr->deleteById($this->get_solr_unique_id());
			if ($commit === true) {
				$solr->commit();
			}
		}
	}
	/**
	 * Updates the solr index for the doc object.
	 *
	 * @param boolean $commit
	 *  if set to true it will auto commit the changes, you need then
	 *  do this manualy (optional, default = false)
	 *
	 * @return boolean returns true if we indexed something, else false
	 */
	public function update_solr($commit = false) {

		// We can not store it within solr because we have no data.
		if ($this->load_success() === false) {
			return;
		}

		$solr = $this->get_solr_server();
		if ($solr !== false) {
			$doc = $this->get_solr_document();

			// Autofill id
			$id = $doc->getField('id');
			if ($id === false || empty($id)) {
				$doc->addField('id', $this->get_solr_unique_id());
			}

			// Autofill created
			$created = $doc->getField('created');
			if ($created === false || empty($created)) {
				$doc->addField('created', gmdate("Y-m-d\TH:i:s\Z", TIME_NOW));
			}

			$solr->addDocument($doc);
			if ($commit === true) {
				$solr->commit();
			}
			return true;
		}
		return false;
	}

	/**
	 * Returns unique solr id which will be used for the "id" field within solr doc.
	 *
	 * @return string the unique id.
	 */
	abstract public function get_solr_unique_id();

	/**
	 * Returns the apache solr server instance.
	 *
	 * @return Apache_Solr_Service The apache solr server instance.
	 */
	abstract protected function get_solr_server();

	/**
	 * Returns the solr document which will be used for the solr update process.
	 * The solr document does not include the unique id and/or created field, it will be auto generated within SolrAbstractDataManagement class if it does not exist.
	 *
	 * @return Apache_Solr_Document The apache solr document.
	 */
	abstract public function get_solr_document();
}

