<?php
$file = "/var/log/secure";
$len = filesize($file);
$lastpos = $len;

while (true) {
	sleep(1);
	clearstatcache(false, $file);
	$len = filesize($file);

	if ($len < $lastpos) {
		// the file was deleted or reset
		$lastpos = $len;
	} else if ($len > $lastpos) {
		$handle = fopen($file, "rb");

		if ($handle === false) {
			die("File lost.");
		}

		fseek($handle, $lastpos);

		while (!feof($handle)) {
			$line = fgets($handle);

			if (preg_match("/^.*Invalid user \w+ from \d{0,3}\.\d{0,3}\.\d{0,3}\.\d{0,3}.*$/", $line, $matches)) {
				// I did this the hard way because the --blink argument to the tool kept leaving it set to red all the time.
				// This way the "blink" seems a lot more reliable.
				$blink = popen("./blink1-tool -q --red", "r");
				fpassthru($blink);
				fclose($blink);

				sleep(1);

				$blink = popen("./blink1-tool -q --off", "r");
				fpassthru($blink);
				fclose($blink);
			}
		}

		$lastpos = ftell($handle);
		fclose($handle);
	}
}
