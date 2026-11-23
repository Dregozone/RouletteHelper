<?php

/*
 * Claude Code cloud sessions only (Life OS's cloud worker, docs/office/30 in life-os), called by
 * cloud-setup.sh when `composer install` can't download packages. Kept by Life OS
 * (docs/office/cloud-worker/cloud-dists.php); edit it there.
 *
 * Why: in a cloud session, GitHub is reached through a proxy that only serves the repositories
 * attached to the session, so Composer's zips of other people's packages
 * (api.github.com/.../zipball, codeload.github.com) are refused. Git fetches of them work. Cloning
 * each package in full (--prefer-source) is too slow for big ones (rector/rector is 384 MB), and
 * some (phpstan/phpstan) have no git source in composer.lock at all. So for every package whose
 * zip comes from GitHub, this fetches only the locked commit (depth 1, several at once) and builds
 * the same zip with `git archive`, then writes composer.cloud.json / composer.cloud.lock: copies
 * whose lock points at the local zips. The real composer.json and composer.lock are never changed.
 *
 * Prints how many zips it made (0: nothing to do, no copies written).
 */
$lockFile = 'composer.lock';
if (! is_file($lockFile) || ! is_file('composer.json')) {
    echo 0;
    exit(0);
}
$lock = json_decode(file_get_contents($lockFile), true);
if (! is_array($lock)) {
    echo 0;
    exit(0);
}

/** Remove a folder and everything in it (git's files are read-only on some systems). */
function removeTree(string $path): void
{
    if (! file_exists($path)) {
        return;
    }
    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($items as $item) {
        @chmod($item->getPathname(), 0777);
        $item->isDir() && ! $item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}

$dir = sys_get_temp_dir().'/cloud-dists';
@mkdir($dir, 0777, true);
$q = fn (string $s): string => escapeshellarg($s);

// Every package whose zip comes from GitHub: [group, index, name, zip, command to build it].
$jobs = [];
foreach (['packages', 'packages-dev'] as $group) {
    foreach ($lock[$group] ?? [] as $i => $package) {
        $url = $package['dist']['url'] ?? '';
        if (! preg_match('~^https://api\.github\.com/repos/([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+)/zipball/([0-9a-f]{40})$~', $url, $m)) {
            continue;
        }
        [, $owner, $repo, $sha] = $m;
        $zip = "{$dir}/{$owner}-{$repo}-{$sha}.zip";
        $src = "{$dir}/src-{$owner}-{$repo}-{$sha}";
        $cmd = is_file($zip) ? null : 'git init -q '.$q($src)
            .' && git -C '.$q($src).' fetch -q --depth 1 '.$q("https://github.com/{$owner}/{$repo}.git").' '.$q($sha)
            .' && git -C '.$q($src).' archive --format=zip -o '.$q($zip).' FETCH_HEAD';
        $jobs[] = compact('group', 'i', 'zip', 'src', 'cmd') + ['name' => $package['name'], 'sha' => $sha];
    }
}

// Build the missing zips, 8 at a time.
$pending = array_values(array_filter($jobs, fn (array $j) => $j['cmd'] !== null));
$running = [];
while ($pending !== [] || $running !== []) {
    while ($pending !== [] && count($running) < 8) {
        $job = array_shift($pending);
        removeTree($job['src']);
        $proc = proc_open($job['cmd'].' 2>&1', [1 => ['pipe', 'w']], $pipes);
        $running[] = ['job' => $job, 'proc' => $proc, 'out' => $pipes[1]];
    }
    usleep(100000);
    foreach ($running as $k => $r) {
        $status = proc_get_status($r['proc']);
        if ($status['running']) {
            continue;
        }
        $out = stream_get_contents($r['out']);
        fclose($r['out']);
        proc_close($r['proc']);
        removeTree($r['job']['src']);
        // A failed or empty build leaves no zip: that package keeps its own dist (and git source).
        if ($status['exitcode'] !== 0 || ! is_file($r['job']['zip']) || filesize($r['job']['zip']) === 0) {
            @unlink($r['job']['zip']);
            fwrite(STDERR, "Could not build {$r['job']['name']} from git: {$out}\n");
        }
        unset($running[$k]);
    }
}

$made = 0;
foreach ($jobs as $job) {
    if (is_file($job['zip'])) {
        $lock[$job['group']][$job['i']]['dist'] = ['type' => 'zip', 'url' => $job['zip'], 'reference' => $job['sha'], 'shasum' => ''];
        $made++;
    }
}

if ($made > 0) {
    copy('composer.json', 'composer.cloud.json');
    file_put_contents('composer.cloud.lock', json_encode($lock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
}
echo $made;
