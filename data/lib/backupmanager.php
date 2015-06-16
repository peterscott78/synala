<?php

class BackupManager {

///////////////////////////////////////////////
// Construct
///////////////////////////////////////////////

public function __construct() { 

	// Initialize
	global $config;

	// Set variables
	$this->tar = '';
	$this->gzip = '';
	$this->mysqldump = '';

	// Find needed server commands
	$dirs = array('/usr/bin', '/bin', '/usr/local/bin', '/sbin/', '/usr/sbin', '/usr/local/sbin', '/usr/mysql/bin', '/usr/local/mysql/bin');
	foreach ($dirs as $dir) {
		if (file_exists($dir . '/mysqldump') && $this->mysqldump == '') { $this->mysqldump = $dir . '/mysqldump'; }
		if (file_exists($dir . '/tar') && $this->tar == '') { $this->tar = $dir . '/tar'; }
		if (file_exists($dir . '/gzip') && $this->gzip == '') { $this->gzip = $dir . '/gzip'; }
	}
}

///////////////////////////////////////////////
// Perform backup
///////////////////////////////////////////////

public function perform_backup($full_backup = false) { 

	// Init
	global $config;

	// Get filename
	$timestamp = date('Y-m-d_His');
	//if ($full_backup === true) { $timestamp .= '_full'; }
	
	// Change directories
	if (!chdir(SITE_PATH)) { 
		trigger_error("Unable to backup database, as the software was unable to change to the directory " . SITE_PATH, E_USER_ERROR);
	}

	// Create dump command
	$dump_command = $this->mysqldump . ' -u' . DBUSER;
	if (DBPASS != '') { $dump_command .= " -p'" . DBPASS . "'"; }
	if (DBHOST != 'localhost') { $dump_command .= ' -h' . DBHOST; }
	if (DBPORT != '3306') { $dump_command .= ' -P' . DBPORT; }
	$dump_command .= ' ' . DBNAME . " > data/backups/" . $timestamp . ".sql";

	// Dump mySQL database
	exec($dump_command);
	
	// Create tar command
	$tarfile = SITE_PATH . '/data/backups/' . $timestamp . '.tar';
	if ($full_backup === true) { 
		$tar_command = $this->tar . ' -cf ' . $tarfile . ' ./ --exclude data/backups';
	} else { 
		$tar_command = $this->tar . ' -cf ' . $tarfile . ' data/backups/' . $timestamp . '.sql';
	}

	// Archive backup
	exec($tar_command);
	exec($this->gzip . ' ' . $tarfile);

	// Delete dump.sql
	if (file_exists(SITE_PATH . '/data/backups/' . $timestamp . '.sql')) {
		unlink(SITE_PATH . '/data/backups/' . $timestamp . '.sql');
	}
	
	// Rotate backups
	$this->rotate_backups();
	
	// Upload remote backup, if needed
	$this->upload_remote_backup($timestamp . '.tar.gz');
	
	// Return
	return $timestamp . '.tar.gz';
}

///////////////////////////////////////////////
// Upload remote backup
///////////////////////////////////////////////

public function upload_remote_backup($filename) { 

	// Init
	global $config;
	$file_path = SITE_PATH . '/data/backups/' . $filename;

	// Amazon S3
	if ($config['backup_type'] == 'amazon') { 
	
		// Set variables
		$bucket_name = 'synala';
	
		// Init client
		include_once(SITE_PATH . '/data/lib/S3.php');
		$s3_client = new S3($config['backup_amazon_access_key'], $config['backup_amazon_secret_key']);

		// Create subject, if needed
		$buckets = $s3_client->listBuckets();
		if (!in_array($bucket_name, $buckets)) { 
			$s3_client->putBucket($bucket_name, S3::ACL_PRIVATE); 
		}
		$s3_files_tmp = $s3_client->getBucket($bucket_name);
		$s3_files = array_keys($s3_files_tmp);

		// Upload backup file
		$s3_client->putObjectFile($file_path, $bucket_name, $filename);

	// Remote FTP
	} elseif ($config['backup_type'] == 'ftp') { 
		if ($config['backup_ftp_type'] == 'ftps') { 
			$ftp_client = ftp_ssl_connect($config['backup_ftp_host'], 22, 360);
		} else { 
			$ftp_client = ftp_connect($config['backup_ftp_host'], $config['backup_ftp_port']);
		}
		ftp_login($ftp_client, $config['backup_ftp_username'], $config['backup_ftp_password']);

		// Set transfer mode
		//$is_passive = $config['remote_backup_ftp_mode'] == 'passive' ? true : false;
		//ftp_pasv($ftp_client, $is_passive);
		
		// Upload file
		//if ($config['remote_backup_ftp_dir'] != '') { $filename = $config['remote_backup_ftp_dir'] . '/' . $filename; }
		@ftp_put($ftp_client, $filename, SITE_PATH . "/data/backups/$filename", FTP_BINARY);
		ftp_close($ftp_client);

	// Tarsnap
	} elseif ($config['backup_type'] == 'tarsnap') { 
		system($config['backup_tarsnap_location'] . " -cf $config[backup_tarsnap_archive] " . SITE_PATH);
	}

	// Delete local file, if needed
	//if ($config['remote_backup_retain_local'] != 1 && is_file($file_path)) { 
	//	@unlink($file_path);
	//}

}

///////////////////////////////////////////////
// Rotate backups
///////////////////////////////////////////////

protected function rotate_backups() {

	// Initialize
	global $config;
	$delete_date = DB::queryFirstField("SELECT date(date_sub(now(), interval $config[backup_expire_days] day))");

	// GO through backups
	if (!$handle = opendir(SITE_PATH . '/data/backups')) { return; }
	while (false !== ($file = readdir($handle))) {
		if (!preg_match("/^(\d+)-(\d+)-(\d+)_(.+?)\.tar\.gz$/", $file, $match)) { continue; }
		$file_date = $match[1] . '-' . $match[2] . '-' . $match[3];

		$ok = DB::queryFirstField("SELECT DATE('$delete_date') > DATE('$file_date')");
		if ($ok == 1) { @unlink(SITE_PATH . '/data/backups/' . $file); }
	}
	closedir($handle);

	// Return
	return true;

}

}

?>