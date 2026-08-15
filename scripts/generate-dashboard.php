<?php

declare(strict_types=1);

/**
 * Sinh file dashboard.html — báo cáo trực quan theo MODULE cho tester.
 *
 * Nguồn dữ liệu:
 *  1. TEST_MAPPING.md          -> danh sách Module / TC ID / tên hàm test / trạng thái thiết kế
 *  2. storage/phpunit-junit.xml -> kết quả chạy thật của PHPUnit (JUnit XML)
 *  3. test-results/results.json -> kết quả chạy thật của Playwright (JSON reporter)
 *
 * Cách chạy: php scripts/generate-dashboard.php
 * (composer script: `composer dashboard`)
 */

const ROOT = __DIR__ . '/..';

// ------------------------------------------------------------------
// 1. Đọc TEST_MAPPING.md -> danh sách module + hàng test
// ------------------------------------------------------------------
function parseTestMapping(string $path): array
{
    $modules = [];
    $currentModule = null;
    $lines = file($path, FILE_IGNORE_NEW_LINES);

    foreach ($lines as $line) {
        // Tiêu đề module: "## 5.2.4. Giỏ hàng (17 designed → 14 executable)"
        if (preg_match('/^##\s+[\d.]+\.?\s*(.+)$/', $line, $m)) {
            $title = trim(preg_replace('/\s*\(\d+.*\)$/', '', $m[1]));
            $currentModule = $title;
            $modules[$currentModule] = $modules[$currentModule] ?? [];
            continue;
        }

        // Hàng bảng: | TC ID | Test file/method | Status |
        if ($currentModule !== null && preg_match('/^\|\s*(TC-[A-Z0-9-]+)\s*\|(.*)\|([^|]*)\|\s*$/', $line, $m)) {
            $tcId = trim($m[1]);
            $rest = trim($m[2]);
            $status = trim($m[3]);

            if (str_contains($status, '✅')) {
                $designStatus = 'executable';
            } elseif (str_contains($status, '⚠')) {
                $designStatus = 'bug';
            } else {
                $designStatus = 'pending';
            }

            // Cố gắng tách tên method PHP/Playwright ra khỏi cột mô tả (nằm trong dấu `...`)
            preg_match_all('/`([a-zA-Z0-9_]+)`/', $rest, $mm);
            $methods = $mm[1] ?? [];

            $modules[$currentModule][] = [
                'tc_id' => $tcId,
                'desc' => $rest,
                'methods' => $methods,
                'design_status' => $designStatus,
            ];
        }
    }

    return $modules;
}

// ------------------------------------------------------------------
// 2. Đọc kết quả PHPUnit từ JUnit XML (nếu có)
// ------------------------------------------------------------------
function parsePhpUnitResults(string $path): array
{
    $results = []; // methodName => 'pass' | 'fail'
    if (!file_exists($path)) {
        return $results;
    }

    $xml = @simplexml_load_file($path);
    if ($xml === false) {
        return $results;
    }

    foreach ($xml->xpath('//testcase') as $tc) {
        $name = (string)$tc['name'];
        $failed = count($tc->xpath('failure')) > 0 || count($tc->xpath('error')) > 0;
        $results[$name] = $failed ? 'fail' : 'pass';
    }

    return $results;
}

// ------------------------------------------------------------------
// 3. Đọc kết quả Playwright từ JSON reporter (nếu có)
// ------------------------------------------------------------------
function parsePlaywrightResults(string $path): array
{
    $results = []; // "TC-E2E-XXX-NN" (lấy từ title) => 'pass' | 'fail'
    if (!file_exists($path)) {
        return $results;
    }

    $data = json_decode((string)file_get_contents($path), true);
    if (!is_array($data)) {
        return $results;
    }

    $walk = function ($suites) use (&$walk, &$results) {
        foreach ($suites as $suite) {
            foreach ($suite['specs'] ?? [] as $spec) {
                $title = $spec['title'] ?? '';
                if (preg_match('/^(TC-[A-Z0-9-]+)/', $title, $m)) {
                    $allPassed = true;
                    foreach ($spec['tests'] ?? [] as $test) {
                        foreach ($test['results'] ?? [] as $result) {
                            if (($result['status'] ?? '') !== 'passed') {
                                $allPassed = false;
                            }
                        }
                    }
                    $results[$m[1]] = $allPassed ? 'pass' : 'fail';
                }
            }
            if (!empty($suite['suites'])) {
                $walk($suite['suites']);
            }
        }
    };

    $walk($data['suites'] ?? []);
    return $results;
}

// ------------------------------------------------------------------
// 4. Gộp và render HTML
// ------------------------------------------------------------------
$modules = parseTestMapping(ROOT . '/TEST_MAPPING.md');
$phpunitResults = parsePhpUnitResults(ROOT . '/storage/phpunit-junit.xml');
$playwrightResults = parsePlaywrightResults(ROOT . '/test-results/results.json');

function statusBadge(string $key, array $phpunitResults, array $playwrightResults, string $designStatus): array
{
    // TC-E2E-* -> tra trong kết quả Playwright, còn lại tra theo tên method PHPUnit
    if (isset($playwrightResults[$key])) {
        return $playwrightResults[$key] === 'pass'
            ? ['✅ Pass', 'pass']
            : ['❌ Fail', 'fail'];
    }

    if ($designStatus === 'pending') {
        return ['⏳ Chưa có test tự động', 'pending'];
    }
    if ($designStatus === 'bug') {
        return ['⚠️ Bug đã biết (chờ fix)', 'bug'];
    }

    return ['❓ Chưa chạy / không tìm thấy kết quả', 'unknown'];
}

function methodStatus(array $methods, array $phpunitResults): ?array
{
    foreach ($methods as $m) {
        if (isset($phpunitResults[$m])) {
            return $phpunitResults[$m] === 'pass' ? ['✅ Pass', 'pass'] : ['❌ Fail', 'fail'];
        }
    }
    return null;
}

$totalPass = 0;
$totalFail = 0;
$totalPending = 0;
$totalBug = 0;

$moduleHtml = '';
foreach ($modules as $moduleName => $rows) {
    if (empty($rows)) {
        continue;
    }

    $rowsHtml = '';
    $modPass = 0; $modFail = 0; $modPending = 0; $modBug = 0;

    foreach ($rows as $row) {
        $byMethod = methodStatus($row['methods'], $phpunitResults);
        if ($byMethod !== null) {
            [$label, $cls] = $byMethod;
        } else {
            [$label, $cls] = statusBadge($row['tc_id'], $phpunitResults, $playwrightResults, $row['design_status']);
        }

        switch ($cls) {
            case 'pass': $modPass++; $totalPass++; break;
            case 'fail': $modFail++; $totalFail++; break;
            case 'bug': $modBug++; $totalBug++; break;
            default: $modPending++; $totalPending++; break;
        }

        $desc = htmlspecialchars($row['desc']);
        $desc = preg_replace('/`([a-zA-Z0-9_]+)`/', '<code>$1</code>', $desc);
        $tcId = htmlspecialchars($row['tc_id']);
        $rowsHtml .= "<tr class=\"row-$cls\"><td class=\"tc-id\">$tcId</td><td>$desc</td><td class=\"badge badge-$cls\">$label</td></tr>\n";
    }

    $total = count($rows);
    $pct = $total > 0 ? round((($modPass) / $total) * 100) : 0;

    $moduleHtml .= <<<HTML
    <section class="module">
      <div class="module-header">
        <h2>{$moduleName}</h2>
        <div class="module-stats">
          <span class="stat pass">✅ {$modPass}</span>
          <span class="stat fail">❌ {$modFail}</span>
          <span class="stat bug">⚠️ {$modBug}</span>
          <span class="stat pending">⏳ {$modPending}</span>
          <span class="stat pct">{$pct}% pass</span>
        </div>
      </div>
      <table>
        <thead><tr><th>TC ID</th><th>Mô tả / Hàm test</th><th>Kết quả</th></tr></thead>
        <tbody>
          {$rowsHtml}
        </tbody>
      </table>
    </section>
    HTML;
}

$generatedAt = date('d/m/Y H:i:s');
$totalAll = $totalPass + $totalFail + $totalBug + $totalPending;
$overallPct = $totalAll > 0 ? round(($totalPass / $totalAll) * 100) : 0;

$html = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Laptop Vui — Test Dashboard theo Module</title>
<style>
  body { font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: #0f1117; color: #e6e6e6; margin: 0; padding: 24px; }
  h1 { font-size: 22px; }
  .meta { color: #9aa0a6; font-size: 13px; margin-bottom: 24px; }
  .summary { display: flex; gap: 16px; margin-bottom: 32px; flex-wrap: wrap; }
  .summary .box { background: #1a1d27; border-radius: 8px; padding: 14px 20px; min-width: 120px; }
  .summary .box .num { font-size: 26px; font-weight: 700; }
  .summary .box .label { font-size: 12px; color: #9aa0a6; }
  .module { background: #161822; border-radius: 10px; margin-bottom: 20px; overflow: hidden; border: 1px solid #262a38; }
  .module-header { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; background: #1d2130; flex-wrap: wrap; gap: 8px; }
  .module-header h2 { margin: 0; font-size: 16px; }
  .module-stats { display: flex; gap: 10px; font-size: 13px; }
  .stat.pass { color: #3ddc84; }
  .stat.fail { color: #ff5c5c; }
  .stat.bug { color: #ffb347; }
  .stat.pending { color: #8a8f98; }
  .stat.pct { color: #66b3ff; font-weight: 700; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  th, td { text-align: left; padding: 8px 14px; border-bottom: 1px solid #262a38; }
  th { color: #9aa0a6; font-weight: 600; font-size: 12px; text-transform: uppercase; }
  .tc-id { font-family: Menlo, Consolas, monospace; color: #66b3ff; white-space: nowrap; }
  code { background: #262a38; padding: 1px 6px; border-radius: 4px; font-size: 12px; }
  .badge { white-space: nowrap; font-weight: 600; }
  .row-fail { background: rgba(255,92,92,0.06); }
  .row-bug { background: rgba(255,179,71,0.05); }
</style>
</head>
<body>
  <h1>🧪 Laptop Vui — Dashboard test theo Module</h1>
  <div class="meta">Sinh lúc {$generatedAt} · Nguồn: TEST_MAPPING.md + kết quả chạy PHPUnit/Playwright thật</div>

  <div class="summary">
    <div class="box"><div class="num" style="color:#3ddc84">{$totalPass}</div><div class="label">✅ Pass</div></div>
    <div class="box"><div class="num" style="color:#ff5c5c">{$totalFail}</div><div class="label">❌ Fail</div></div>
    <div class="box"><div class="num" style="color:#ffb347">{$totalBug}</div><div class="label">⚠️ Bug đã biết</div></div>
    <div class="box"><div class="num" style="color:#8a8f98">{$totalPending}</div><div class="label">⏳ Chưa có test</div></div>
    <div class="box"><div class="num" style="color:#66b3ff">{$overallPct}%</div><div class="label">Tỉ lệ pass / tổng TC</div></div>
  </div>

  {$moduleHtml}
</body>
</html>
HTML;

file_put_contents(ROOT . '/dashboard.html', $html);
echo "Dashboard sinh xong: dashboard.html\n";
echo "Tổng: {$totalAll} test case | Pass: {$totalPass} | Fail: {$totalFail} | Bug: {$totalBug} | Chưa có test: {$totalPending}\n";
