<?php
// ============================================
// API ДЛЯ POCKET CODE → GITHUB
// ============================================

$token = "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";
$owner = "wezerr2313212323";
$repo = "brawl-stars-leaderboard";
$file = "players.json";

$action = $_GET['action'] ?? '';

// ---------- ПОЛУЧИТЬ ТОП ----------
if ($action == 'top') {
    $url = "https://raw.githubusercontent.com/$owner/$repo/main/$file";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($result, true);
    $players = $data['players'] ?? [];
    usort($players, function($a, $b) {
        return $b['trophies'] - $a['trophies'];
    });
    echo json_encode(array_slice($players, 0, 10), JSON_UNESCAPED_UNICODE);
}

// ---------- СОХРАНИТЬ ИГРОКА ----------
elseif ($action == 'save') {
    $id = $_GET['id'] ?? '';
    $name = $_GET['name'] ?? 'Игрок';
    $trophies = intval($_GET['trophies'] ?? 0);
    
    $url = "https://api.github.com/repos/$owner/$repo/contents/$file";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: token $token",
        "Accept: application/vnd.github.v3+json"
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($result, true);
    $sha = $data['sha'] ?? null;
    
    $current = ['players' => []];
    if (isset($data['content'])) {
        $content = base64_decode($data['content']);
        $current = json_decode($content, true) ?: ['players' => []];
    }
    
    $current['players'][$id] = ['name' => $name, 'trophies' => $trophies];
    $new_content = base64_encode(json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $body = json_encode([
        'message' => 'Обновление через Pocket Code',
        'content' => $new_content,
        'sha' => $sha
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: token $token",
        "Accept: application/vnd.github.v3+json",
        "Content-Type: application/json"
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    
    echo json_encode(['status' => 'success', 'message' => 'Сохранено']);
}

else {
    echo json_encode(['error' => 'Неизвестное действие']);
}
?>