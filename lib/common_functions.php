<?PHP
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