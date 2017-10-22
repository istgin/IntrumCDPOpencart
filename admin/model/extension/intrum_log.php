<?php
class ModelExtensionIntrumLog extends Model {

	public function getLog($id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "plugin_byjuno_transactions WHERE id = '" . (int)$id . "'");

		return $query->row;
	}

	public function getLogs($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "plugin_byjuno_transactions as c";

		$sort_data = array(
			'c.id'
		);
		$sql .= " ORDER BY c.id";
		$sql .= " DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalLogs() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "plugin_byjuno_transactions");

		return $query->row['total'];
	}
}