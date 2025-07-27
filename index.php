<?php
require_once __DIR__ . '/config.php';
$pdo = new PDO("mysql:host=" . $_ENV["MYSQL_HOST"] . ";dbname=" . $_ENV["MYSQL_DATABASE"] . ";charset=utf8mb4", $_ENV["MYSQL_USER"], $_ENV["MYSQL_PASSWORD"]);

// Sorting logic
$sort = $_GET['sort'] ?? 'newest';
switch ($sort) {
    case 'oldest': $order = "created_at ASC"; break;
    case 'az': $order = "title ASC"; break;
    case 'za': $order = "title DESC"; break;
    default: $order = "created_at DESC";
}

$projects = $pdo->query("SELECT id, title, url, tags, created_at, visible FROM projects ORDER BY $order")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Projects</title>
    <link rel="stylesheet" href="./css/index.css?v=1.3">
</head>
<body>
<h1>My Projects</h1>

<div class="sort-form">
    <form method="get">
        <label for="sort">Sort by:</label>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Date (Newest)</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Date (Oldest)</option>
            <option value="az" <?= $sort === 'az' ? 'selected' : '' ?>>Name (A–Z)</option>
            <option value="za" <?= $sort === 'za' ? 'selected' : '' ?>>Name (Z–A)</option>
        </select>
    </form>
    <form method="get" style="margin-top: 1rem;">
    </form>
    <br>
</div>

<div class="grid">
    <?php foreach ($projects as $project): ?>
        <div class="card">
            <div class="card-title"><?= htmlspecialchars($project['title']) ?></div>

            <?php if (!empty($project['tags'])): ?>
                <div class="card-tags">#<?= implode(' #', array_map('trim', explode(',', $project['tags']))) ?></div>
            <?php endif; ?>

            <div class="card-date">
                🕒 <?= date("j F Y", strtotime($project['created_at'])) ?>
            </div>

            <div class="card-link" style="margin-top: 0.75rem;">
                <a href="<?= htmlspecialchars($project['url']) ?>" target="_blank">View Project</a>
            </div>
            <!--
            <div class="card-visibility" style="margin-top: 0.75rem;">
                Visibility: <strong><?= $project['visible'] ? 'Visible' : 'Hidden' ?></strong>
            </div>
-->
        </div>
    <?php endforeach; ?>
</div>


<footer>
    <hr>
    <p>
        Built with Notion, PHP & MariaDB
    </p>
</footer>
</body>
</html>
