<?php
require_once("blobstore.inc.php");

function call_user_func_args($fetch_function) {
	if ( is_array( $fetch_function ) ) { // see if there's an argument list included with the callable
		if ( count( $fetch_function ) == 2 && is_array( $fetch_function[1] ) ) {
		  $args = $fetch_function[1];
		  $fetch_function = $fetch_function[0];
		} else if ( count( $fetch_function ) == 3 && is_array( $fetch_function[2] ) ) {
		  $args = $fetch_function[2];
		  $fetch_function = array_slice($fetch_function, 0, 2);
		} else {
		  $args = array();
		}
	} else {
		$args = array();
	}
	
	$content = call_user_func_array($fetch_function, $args); //call callback
	return $content; 
}

class Cache {
	public function get($key) {
		return null;
	}

	public function set($key, $value, $expiry = null) {
		// noop
	}
	
	public function aquire($key, $fetch_function = null, $expiry = null, $purge = false) {
		debug(get_class($this) . "::aquire", "key", $key);
		
		if ( !$purge ) $content = $this->get($key);
		else $content = false; // explicite purge
		
		if ($content === null || $content === false) {
			if ($fetch_function) {
				debug(get_class($this) . "::aquire", "miss", $key);
				$content = call_user_func_args($fetch_function); //call callback
				if ( $expiry ) $this->set($key, $content, $expiry);
			} else {
				debug(get_class($this) . "::aquire", "fail", $key);
			}
		} else {
			if ($fetch_function) debug(get_class($this) . "::aquire", "hit", $key);
		}
		
		return $content;
	}
}

class FileCache extends Cache {
	function __construct( $dir, $prefix, $lock_expiry = 60 ) {
		$this->dir = $dir;
		$this->prefix = $prefix;
		$this->lock_expiry = $lock_expiry;
		$this->serialize = true;
		$this->suffix = ".cache";
		$this->fmode = false;
	}
	
	public function get_cache_dir() {
		return $this->dir;
	}
	
	public function get_cache_file($key) {
		$f = $this->dir . "/" . $this->prefix . urlencode($key) . $this->suffix ;
		return $f;
	}

	public function get($key) {
		$f = $this->get_cache_file($key);
		$v = file_get_contents_atomic( $f );
		
		if ( $this->serialize && $v ) $v = unserialize($v);
		return $v;
	}

	public function set($key, $value, $expiry = null) {
		$f = $this->get_cache_file($key);
		if ( $this->serialize && $value ) $value = serialize($value);
		file_put_contents_atomic($f, $value);
		if ($this->fmode) chmod($f, $this->fmode);
	}
	
	function aquire($key, $fetch_function = null, $cache_duration = 60, $purge = false) {
		#debug(get_class($this) . "::aquire", "key", $key);
		
		$cache_file = $this->get_cache_file($key);
		$lock_file  = "$cache_file.lock";
	
		$cache_age = time();
		$lock_age = time();
	
		if (!$purge && file_exists($cache_file)) {
			$cache_age -= filemtime($cache_file);
		}
	
		if (file_exists($lock_file)) {
			$lock_age -= filemtime($lock_file);
			if ($lock_age < $this->lock_expiry) $cache_age = 0; //lock is valid, use old data
			else @unlink($lock_file); //lock expired
		}
	
		$content = NULL;
	
		if (!$cache_duration || $purge || $cache_age > $cache_duration) {
			if ( !$fetch_function ) {
				debug(get_class($this) . "::aquire", "fail", $key);
				return false;
			}
	
			file_put_contents_atomic( $lock_file, posix_getpid() ); //grab lock
	
			$content = call_user_func_args($fetch_function); //call callback
	
			if ($content===false) {
				debug(get_class($this) . "::aquire", "fetch-fail", $content);
				return false;
			}
	
			debug(get_class($this) . "::aquire", "miss", $key);
			
			if ( $content && $this->serialize ) {
				$data = serialize( $content );
			} else {
				$data = $content;
			}
	
			if ($cache_duration && $cache_file) {
				debug(get_class($this) . "::aquire", "caching to", $cache_file);
				$ok = file_put_contents_atomic($cache_file, $data);
				if (!$ok) debug(get_class($this) . "::aquire", "failed to write to cache file!", $cache_file);
			}
	
			@unlink( $lock_file ); //release lock
		} else {
			debug(get_class($this) . "::aquire", "hit", $key);
		}
	
		if ( is_null($content) && file_exists($cache_file) ) {
			$content = file_get_contents_atomic($cache_file);
	
			if ( $content && $this->serialize ) {
				$c = unserialize( $content );
				if ( $c !== false ) $content = $c; //NOTE: if unserialize fails, keep data as-is. may be legacy content 
			}
		}
		
		return $content;
	}
}

class BufferedFileCache extends FileCache {
	function __construct( $buffer, $dir, $prefix, $lock_expiry = 60 ) {
		$this->buffer = $buffer;
		$this->buffer_expiry = false;
		
		parent::__construct($dir, $prefix, $lock_expiry );
	}
	
	public function get($key) {
		$v = $this->buffer->get($key);
		if ( $v !== null && $v !== false ) {
			debug(__METHOD__, "using buffered value", $key);
			return $v;
		}
		
		$v = parent::get($key);
		return $v;
	}

	public function set($key, $value, $expiry = null) {
		parent::set($key, $value, $expiry);
		
		debug(__METHOD__, "buffering value", $key);
		$this->buffer->set($key, $value, $this->buffer_expiry ? $this->buffer_expiry : $expiry);
	}
	
	function aquire($key, $fetch_function = null, $cache_duration = 60, $purge = false) {
		if ( !$purge ) {
			$v = $this->buffer->get($key);

			if ( $v !== null && $v !== false ) {
				debug(__METHOD__, "using buffered value", $key);
				return $v;
			} else {
				debug(__METHOD__, "no buffered value", $key);
			}
		}

		$content = parent::aquire($key, $fetch_function, $cache_duration, $purge);

		debug(__METHOD__, "buffering value", $key);
		$this->buffer->set($key, $content, $this->buffer_expiry ? $this->buffer_expiry : $cache_duration);

		return $content;
	}	
}

class MemcachedCache extends Cache {
	function __construct( $server, $prefix ) {
		$this->prefix = $prefix;
		
		if (is_string($server)) $server = preg_split('/[:]/', $server, 2);
		
		if ( count($server) < 2 ) $server[1] = 11211; //default port
		
		if (class_exists('Memcached')) {
			$this->impl = "Memcached";
			$this->memcached = new Memcached();
			$this->memcached->addServer($server[0], $server[1]);
		} else {
			$this->impl = "Memcache";
			$this->memcached = new Memcache();
			$this->memcached->pconnect($server[0], $server[1]);
		}
	}
	
	public function get_cache_key($key) {
		$k = $this->prefix . urlencode($key);
		return $k;
	}
	
	public function get($key) {
		$k = $this->get_cache_key( $key );
		
		$v = $this->memcached->get($k);
		
		if ($v) $v = unserialize($v);
		return $v;
	}

	public function set($key, $value, $expiry = 0) {
		$k = $this->get_cache_key( $key );
		if ($value) $value = serialize($value);
		
		if ( $this->impl == "Memcache" ) {
			$ok = $this->memcached->set($k, $value, 0, $expiry);
			
			if (!$ok) {
				debug(__METHOD__, "Memcached returned an error");
				trigger_error( "memcached returned an error", E_USER_WARNING );
			}
		} else {
			$ok = $this->memcached->set($k, $value, $expiry);
			
			if (!$ok) {
				
				$error = $this->memcached->getResultCode();
				$msg = $this->memcached->getResultMessage();
				
				debug(__METHOD__, "Memcached error ", $msg);
				trigger_error( "memcached-error (#$error): $msg", E_USER_WARNING );
			}
		}
		
		return $ok;
	}
}

class LocalCache extends Cache {
	function __construct( $prefix, $mode = null ) {
		$this->prefix = $prefix;
		
		if ( !$mode || $mode === 'auto' ) {
				if ( function_exists('apc_store') ) $mode = 'apc';
				else if ( function_exists('eaccelerator_put') ) $mode = 'eaccelerator';
				else if ( function_exists('xcache_put') ) $mode = 'xcache';
				else $mode = 'none';
		}
		
		$this->mode = $mode;
	}
	
	public function get_cache_key($key) {
		$k = $this->prefix . urlencode($key);
		return $k;
	}
	
	public function get($key) {
		$k = $this->get_cache_key( $key );
		
		if ( $this->mode == 'apc' ) $v = apc_fetch( $k );
		else if ( $this->mode == 'eaccelerator' ) $v = eaccelerator_get( $k );
		else if ( $this->mode == 'xcache' ) $v = xcache_get( $k );
		else $v = null;
		
		if ( $v ) $v = unserialize($v);
		return $v;
	}

	public function set($key, $value, $expiry = null) {
		$k = $this->get_cache_key( $key );
		
		if ( $value ) $value = serialize($value);
		
		if ( $this->mode == 'apc' ) $ok = apc_store($k, $value, $expiry);
		else if ( $this->mode == 'eaccelerator' ) $ok = eaccelerator_put($k, $value, $expiry);
		else if ( $this->mode == 'xcache' ) $ok = xcache_put($k, $value, $expiry);
		else $ok = false;
		
		return $ok;
	}
}

class FreshCacheList {
	function __construct( $file ) {
		$this->file = $file;
		$this->items = null;
	}	
	
	function items() {
		if ( $this->items === false ) return;
		if ( $this->items === null ) $this->load();
		
		return $this->items;
	}
	
	function load() {
		$this->items = array();

		debug(__METHOD__, "file", $this->file);
		$data = @file_get_contents_atomic( $this->file );

		if ( !$data ) {
			debug(__METHOD__, "failed!", $data);
			return false;
		}
		
		$lines = preg_split('/[\r\n]+/s', $data);

		foreach ( $lines as $s ) {
			$w = preg_split('/\s+/', trim($s));
			
			$k = $this->key( $w );
			$this->items[$k] = $w;
		}
		
		debug(__METHOD__, "list", $this->items);
		return count($this->items);
	}
	
	function save() {
		if ( !$this->items ) return false;

		$data = "";

		foreach ( $this->items as $w ) {
			$data .= implode("\t", $w) . "\n";
		}
		
		debug(__METHOD__, "file", $this->file);
		return file_put_contents_atomic( $this->file, $data );
	}
	
	function key( $fields ) {
		return implode(':', $fields);
	}
	
	function add( $fields ) {
		$this->items();
		
		$k = $this->key( $fields );
		if ( isset($this->items[$k]) ) return 0; //nothing to do.

		$this->items[$k] = $fields;
		
		return $this->save();
	}

}
