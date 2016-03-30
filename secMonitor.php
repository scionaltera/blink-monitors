<?php
$file = "/var/log/secure";
$len = filesize($file);
$lastpos = $len;

//printf("Starting up...\n");

while (true) {
	sleep(1);
	//printf("Top of loop.\n");
	clearstatcache(false, $file);
	//printf("Cleared stat cache.\n");
	$len = filesize($file);

	//printf("len=%d lastpos=%d\n", $len, $lastpos);

	if ($len < $lastpos) {
		// the file was deleted or reset
		//printf("File was deleted or reset.\n");
		$lastpos = $len;
	} else if ($len > $lastpos) {
		$handle = fopen($file, "rb");

		if ($handle === false) {
			die("File lost.");
		}

		fseek($handle, $lastpos);

		while (!feof($handle)) {
			$line = fgets($handle);

			//printf("line=%s\n", $line);

			if (preg_match("/^.*Failed password for \w+ from \d{0,3}\.\d{0,3}\.\d{0,3}\.\d{0,3} port \d+ ssh2$/", $line, $matches)) {
				//printf("Line matched regex:%s\nBlink!\n", rtrim($line));
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
