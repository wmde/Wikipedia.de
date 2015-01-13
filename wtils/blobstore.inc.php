<?php
function file_get_contents_atomic($file) {
	$oldabort = ignore_user_abort( true );
	$f = fopen($file, "r");
	if (!$f) return false;

	if (!flock($f, LOCK_SH)) return false;

	$s = "";
	while (true) {
		$buffer = fread($f, 16 * 1024);
		if ($buffer===false || $buffer===null || $buffer==="") break;

		$s .= $buffer;
	}

	flock($f, LOCK_UN);
	fclose($f);
	ignore_user_abort( $oldabort );

	return $s;
}

function file_put_contents_atomic($file, $content, $flags = 0) {
	if ( !is_string($content) ) {
		$e = false;
		
		if ( is_integer($content) || is_float($content) ) {
			if ( defined('BLOBSTORE_STRICT') ) $e = E_USER_WARNING;
		} else {
			if ( defined('BLOBSTORE_STRICT') ) $e = E_USER_ERROR;
			else $e = E_USER_WARNING;
		}
		
		if ( $e ) {
			$msg = "bad blob type: expected string, found " . gettype($content) . ": " . substr(var_export($content, true), 0, 100);
			trigger_error($msg, $e);
		}
		
		log_error($msg, 0);
	}
	
	if (is_bool($flags)) $append = $flags;
	else $append= ( ($flags & FILE_APPEND) == FILE_APPEND );

	$oldabort = ignore_user_abort( true );
	$f = fopen($file, $append?'a':'w');
	if (!$f) return false;

	if (!flock($f, LOCK_EX)) return false;

	while ( strlen($content) > 0 && ( $c = fwrite($f, $content) ) ) {
		$content = substr($content, $c);
		
		if ($content === false) break;
	}

	flock($f, LOCK_UN);
	$ok = fclose($f);
	ignore_user_abort( $oldabort );
	
	return $ok;
}

class BlobStore {
	public function load($key) {
		return false;
	}

	public function info($key) {
		return false;
	}

	public function delete($key) {
		return false;
	}

	public function store($key, $value) {
		return false;
	}
	
	public function createStore() {
		// noop
	}
	
	public function listBlobs() {
		// noop
	}
	
}

class FileBlobStore extends BlobStore {
	function __construct( $dir, $prefix, $suffix = '.data' ) {
		$this->dir = $dir;
		$this->prefix = $prefix;
		$this->suffix = $suffix;
	}
	
	protected function getFileName($key) {
		$k = $key;
		$k = str_replace(' ', '_', $k);
		$k = urlencode($k);
		$f = $this->dir . '/' . $this->prefix . $k . $this->suffix;
		return $f; 
	}
	
	public function load($key) {
		$f = $this->getFileName($key);
		$d = file_get_contents_atomic($f);
		return $d;
	}

	public function delete($key) {
		$f = $this->getFileName($key);
		return unlink($f);
	}

	public function info($key) {
		$f = $this->getFileName($key);
		$stat = stat($f);
		
		if (!$stat) return false;
		
		return array(
			'timestamp' => $stat[9],
			'size' => $stat[7],
			'file' => $f,
			'key' => $key,
		);
	}

	public function store($key, $value) {
		$f = $this->getFileName($key);
		return file_put_contents_atomic($f, $value);
	}
	
	public function createStore() {
		$ok = mkdir($this->dir);
		return $ok;
	}
	
	public function listBlobs() {
		$d = opendir($this->dir);
		if (!$d) return false;
		
		$pattern = '!^' . preg_quote($this->prefix, '!') . '(.*)' . preg_quote($this->suffix, '!') . '$!'; 
		
		$blobs = array();
		while (($f = readdir($d)) !== false) {
			if (preg_match($pattern, $f, $m)) {
				$k = urldecode($m[1]);
				
				$blobs[] = $this->info($key);
			} 
		}
		
		closedir($d);
		
		return $blobs;
	}
}

class DBBlobStore extends BlobStore {
	function __construct( $db, $table, $prefix ) {
		$this->db = $db;
		$this->table = $table;
		$this->prefix = $prefix;
	}
	
	public function getWhere($key) {
		return ' ( id = ' . $this->db->quote($key) . ' ) '; 
	}
	
	protected function query($sql, $key = null) {
		if ($key) $sql = $sql . " WHERE " . $this->getWhere($key);
		
		debug(__METHOD__, 'sql', $sql);
		$rs = $this->db->query($sql);
		
		// Always check that result is not an error
		if (PEAR::isError($rs)) {
		    debug(__METHOD__, 'DB error', $rs->getMessage());
		    debug(__METHOD__, 'DB error SQL', $sql);
		    trigger_error('DB error: '.$rs->getMessage(), E_USER_WARNING);
		    return false;
		}
		
		return $rs;
	}
	
	public function load($key) {
		if (!$key) return false;
		
		$rs = $this->query("SELECT size, data FROM " . $this->table, $key );
		if (!$rs) return false;
		
		if ($rs->fetchInto($row, DB_FETCHMODE_ORDERED)) {
		    $data = $row[1];
			if (strlen($data) != $row[0]) {
				trigger_error("bad blob length: expected " . $row[0] . " bytes, got " . strlen($data), E_USER_WARNING);
				return false;
			} 
			
			return $data;
		}
		
		return false;
	}

	
	public function info($key) {
		if (!$key) return false;
		
		$rs = $this->query("SELECT size, mtime, octet_length(data) FROM " . $this->table, $key );
		if (!$rs) return false;
		
		if ($rs->fetchInto($row, DB_FETCHMODE_ORDERED)) {
			if ( $row[0] != $row[2] ) $warning = "bad blob length: expected " . $row[0] . " bytes, got " . $row[2];
			else $warning = false;
			
			return array(
				'key' => $key,
				'size' => $row[0],
				'mtime' => $row[1],
				'warning' => $warning,
			);
		}
		
		return false;
	}

	public function store($key, $value) {
		if (!$key) return false;
		
		$sql = "INSERT INTO " . $this->table . ' ( id, size, mtime, data ) ';
		
		$sql.= " VALUES (";
		$sql.=  $this->db->quote($key);
		$sql.= ", ";
		$sql.=  $this->db->quote(strlen($value));
		$sql.= ", ";
		$sql.=  $this->db->quote(date("Y-m-d H:i:s"));
		$sql.= ", ";
		$sql.=  $this->db->quote($value);
		$sql.= " )";
		$sql.= " ON DUPLICATE KEY UPDATE ";
		$sql.= " size = VALUES(size), ";
		$sql.= " mtime = VALUES(mtime), ";
		$sql.= " data = VALUES(data) ";
		
		$rs = $this->query( $sql );
		if ( !$rs ) return false;
		
		return $this->db->affectedRows();
	}
	
	public function delete($key) {
		if (!$key) return false;
		
		$rs = $this->query("DELETE FROM " . $this->table, $key );
		if ( !$rs ) return false;
		
		return $this->db->affectedRows();
	}

	public function createStore() {
		$sql = "CREATE TABLE IF NOT EXISTS " . $this->table . ' ( ';
		
		$sql.= " id VARCHAR(255) NOT NULL,";
		$sql.= " size INT NOT NULL,";
		$sql.= " mtime DATETIME NOT NULL,";
		$sql.= " data LONGBLOB DEFAULT NULL,";
		$sql.= " PRIMARY KEY (id)";
		$sql.= " )";
		
		$rs = $this->query( $sql );
		if ( !$rs ) return false;
		
		return $this->db->affectedRows();
	}
	
	public function listBlobs() {
		$rs = $this->query("SELECT id, size, mtime, octet_length(data) FROM " . $this->table );
		if (!$rs) return false;
		
		$blobs = array();
		while ($rs->fetchInto($row, DB_FETCHMODE_ORDERED)) {
			if ( $row[1] != $row[3] ) $warning = "bad blob length: expected " . $row[1] . " bytes, got " . $row[3];
			else $warning = false;
			
			$blobs[] = array(
				'key' => $row[0],
				'size' => $row[1],
				'mtime' => $row[2],
				'warning' => $warning,
			);
		}
		
		return $blobs;
	}
}

