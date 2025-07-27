<?php
require_once __DIR__ . '/config.php';
$pdo = new PDO("mysql:host=" . $_ENV["MYSQL_HOST"] . ";dbname=" . $_ENV["MYSQL_DATABASE"] . ";charset=utf8mb4", $_ENV["MYSQL_USER"], $_ENV["MYSQL_PASSWORD"]);

// Handle visibility toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $id = intval($_POST['toggle_id']);
    $stmt = $pdo->prepare("UPDATE projects SET visible = NOT visible WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage.php");
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);

    // Fetch project title before deletion
    $stmt = $pdo->prepare("SELECT title FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();

    if ($project) {
        // Convert title to filename format
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $project['title']), '-'));
        $file = __DIR__ . '/pages/' . $slug . '.html';

        // Delete the HTML file
        if (file_exists($file)) {
            unlink($file);
        }

        // Now delete from DB
        $delete = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $delete->execute([$id]);
    }

    header("Location: manage.php");
    exit;
}


// Sorting logic
$sort = $_GET['sort'] ?? 'newest';
switch ($sort) {
    case 'oldest': $order = "created_at ASC"; break;
    case 'az': $order = "title ASC"; break;
    case 'za': $order = "title DESC"; break;
    default: $order = "created_at DESC";
}

// Visibility filter logic
$filter = $_GET['filter'] ?? 'all';
$visibilitySql = '';
if ($filter === 'visible') {
    $visibilitySql = 'WHERE visible = 1';
} elseif ($filter === 'hidden') {
    $visibilitySql = 'WHERE visible = 0';
}

$projects = $pdo->query("SELECT id, title, url, tags, created_at, visible FROM projects $visibilitySql ORDER BY $order")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Projects</title>
    <link rel="stylesheet" href="./css/manage.css?v=1.4">
</head>
<body>
<h1>Manage Projects</h1>

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
        <label for="filter">Filter:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="visible" <?= $filter === 'visible' ? 'selected' : '' ?>>Visible</option>
            <option value="hidden" <?= $filter === 'hidden' ? 'selected' : '' ?>>Hidden</option>
        </select>
        <!-- Keep sort param when filtering -->
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
    </form>
    <br>
    <a href="index.php"> <button>Public Page</button> </a>
    <a href="admin.php"> <button>Admin Page</button> </a>

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
            <!-- Toggle visibility form -->
            <form method="POST" class="visibility-form" style="margin-top: 0.5rem;">
                <input type="hidden" name="toggle_id" value="<?= $project['id'] ?>">
                <button type="submit"><?= $project['visible'] ? '🙈 Hide' : '👁️ Show' ?></button>
            </form>

            <!-- Delete form -->
            <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this project?');" style="margin-top: 0.5rem;">
                <input type="hidden" name="delete_id" value="<?= $project['id'] ?>">
                <button type="submit">Delete</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
