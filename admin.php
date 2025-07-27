<?php
require_once __DIR__ . '/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pdo = new PDO("mysql:host=" . $_ENV["MYSQL_HOST"] . ";dbname=" . $_ENV["MYSQL_DATABASE"] . ";charset=utf8mb4", $_ENV["MYSQL_USER"], $_ENV["MYSQL_PASSWORD"]);

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'project';
}

$finalUrl = '';
$projectCreated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['notion_link'];
    $title = $_POST['title'];
    $tags = $_POST['tags'];

    // Extract Notion ID
    preg_match('/([a-f0-9]{32})/', $url, $matches);
    if (!isset($matches[0])) {
        die("❌ Invalid Notion share link format.");
    }
    $embed_url = "https://" . $_ENV["EMBED_DOMAIN"] . "/ebd/{$matches[0]}";

    // Generate slug & path
    $slug = slugify($title);
    $public_filename = "{$slug}.html";
    $filepath = rtrim($_ENV["FILE_PATH"], "/") . "/{$public_filename}";
    $public_url = rtrim($_ENV["PUBLIC_URL"], "/") . "/{$public_filename}";

    // Create HTML file
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="../css/pages.css" >
        <title id="title">{$title}</title>
    </head>
    <body>
        <iframe src="$embed_url" allowfullscreen></iframe>
    </body>
</html>
HTML;

    if (file_put_contents($filepath, $html) === false) {
        die("❌ Failed to write HTML file. Check permissions.");
    }

    // Insert into DB (URL = final public URL)
    $stmt = $pdo->prepare("INSERT INTO projects (title, url, tags, embed_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $public_url, $tags, $embed_url]);

    $finalUrl = $public_url;
    $projectCreated = true;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Add Project</title>
        <link rel="stylesheet" href="./css/admin.css?v=1.23">
    </head>
    <body>
    <h1>Add a Project to the Void</h1>
    <p style="text-align: center; font-size: 0.8rem; color: #555;">
        🔐 Admin Panel – Secured with Cloudflare Zero Trust
    </p>

    <form action="admin.php" method="POST" class="form-box">
        <label for="notion_link">Notion Share Link:</label>
        <input type="text" id="notion_link" name="notion_link" required>

        <label for="title">Project Title:</label>
        <input type="text" id="title" name="title" required>

        <label for="tags">Tags (optional):</label>
        <input type="text" id="tags" name="tags">

        <button type="submit" class="sendbtn" style="width: 100%; margin-top: 2rem;">Add Project</button>
    </form>


    <div class="sendbtn-row">
        <a href="index.php" class="sendbtn">Public Page</a>
        <a href="manage.php" class="sendbtn">Manage Page</a>
    </div>


    <?php if ($projectCreated): ?>
    <div class="message">
        ✅ Project created:<br>
        <a href="<?= $finalUrl ?>" target="_blank"><?= $finalUrl ?></a><br><br>
        <button id="copyBtn">📋 Copy Link</button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const copyBtn = document.getElementById("copyBtn");
            if (copyBtn) {
                copyBtn.addEventListener("click", function () {
                    navigator.clipboard.writeText("<?= $finalUrl ?>").then(() => {
                        copyBtn.innerText = "✅ Copied!";
                        setTimeout(() => copyBtn.innerText = "📋 Copy Link", 2000);
                    });
                });
            }
        });
    </script>
<?php endif; ?>
</body>
</html>
