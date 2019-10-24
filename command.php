<?php declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    die('Command must be executed via CLI');
}

run();
die('FINISHED');

// ---------------------------------------------------------------------------------------------------------------------
function run(): void
{
    if (empty($argv[1])) {
        die('Missing first argument with path to directory');
    }

    $path = realpath($argv[1]);
    if (! file_exists($path)) {
        die('Path `' . $argv[1] . '` does not exist');
    }

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        modifyFile($file->getRealPath());
    }
}

/**
 * @param string $path
 */
function modifyFile(string $path): void
{
	$original = file_get_contents($path);
	$lines = explode(PHP_EOL, $original);

	// modify
	foreach ($lines as $i => &$line) {
		if ($i === 0) {
			$line = '<?php declare(strict_types=1);';
			continue;
		}

        $line = str_replace('declare(strict_types=1);', '', $line);
        $line = rtrim($line);
	}
    unset($line);

    // clean double empty lines
	$cleaning = false;
	$lines = array_filter($lines, static function ($line) use (&$cleaning) {
		if ($line !== '') {
			$cleaning = false;
			return true;
		}

		if ($cleaning) {
			return false;
		}

		$cleaning = true;

		return true;
	});

	$modified = implode(PHP_EOL, $lines);

	file_put_contents($path, $modified);
}
