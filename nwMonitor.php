<?php
$maxRx = 1310;
$maxTx = 1310;
$lastGreen = 0;
$lastBlue = 0;

$handle = popen("/bin/sar -n DEV 1", "r");

while (!feof($handle)) {
	$line = fgets($handle);

	if (preg_match("/^(\d\d:\d\d:\d\d\s+\w\w)\s+(enp3s0)\s+(\d+.\d\d)\s+(\d+.\d\d)\s+(\d+.\d\d)\s+(\d+.\d\d)\s+(\d+.\d\d)\s+(\d+.\d\d)\s+(\d+.\d\d)$/", $line, $matches)) {
		$rxPct = $matches[5]; // rx kB/s
		$txPct = $matches[6]; // tx kB/s

		$green = round(($rxPct / $maxRx) * 255); // recv'd intensity
		$blue = round(($txPct / $maxTx) * 255); // sent intensity

		// upload
		if ($blue > 255) {
			$blue = 255;
		}

		// download
		if ($green > 255) {
			$green = 255;
		}

		//printf("%s: %01.2f rx kB/s %01.2f tx kB/s RGB(0,%d,%d)\n", $matches[2], $matches[3], $matches[4], $green, $blue);

		if ($green != $lastGreen || $blue != $lastBlue) {
			$blink1 = popen("./blink1-tool -q -m 500 --rgb 0,$green,$blue", "r");
			fpassthru($blink1);
			fclose($blink1);

			$lastGreen = $green;
			$lastBlue = $blue;
		}
	}
}

fclose($handle);
?>
