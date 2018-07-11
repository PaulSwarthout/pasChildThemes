<?PHP
function isWin() {
	return (strtoupper(substr(PHP_OS, 0, 3)) == "WIN" ? true : false);
}
function files_are_identical($a, $b, $blocksize = 1024)
{
  // Check if filesize is different
  if(filesize($a) !== filesize($b))
      return false;

  // Check if content is different
  $ah = fopen($a, 'rb');
  $bh = fopen($b, 'rb');

  $result = true;
  while(!feof($ah))
  {
    if(fread($ah, $blocksize) != fread($bh, $blocksize))
    {
      $result = false;
      break;
    }
  }

  fclose($ah);
  fclose($bh);

  return $result;
}
function file_count($dir) {
	$files = scandir($dir);

	unset($files[array_search('.', $files, true)]);
	unset($files[array_search('..', $files, true)]);

	return count($files);
}

function is_folder_empty($dir) {
	return (file_count($dir) == 0 ? true : false);
}
function killChildFile($args) {
	$childFile = $args['file'];
	$directory = $args['directory'];
	$delimiter = $args['delimiter'];
	$themeRoot = $args['themeRoot'];

	unlink($childFile);

	// Walk the folder tree backwards, from endpoint node to root
	// If each folder successive is empty, remove the folder, otherwise break out, we're done.
	$folderSegments = explode($delimiter, $directory);
	for ($ndx = count($folderSegments) - 1; $ndx >= 0; $ndx--) {
		$dir = $themeRoot . $delimiter . implode($delimiter, $folderSegments);
		if (is_folder_empty($dir)) {
			// Folder is empty, remove it.
			rmdir($dir);
		} else {
			// Folder is not empty. Break out, we're done.
			break;
		}
		unset($folderSegments[count($folderSegments)-1]);
	}
}
