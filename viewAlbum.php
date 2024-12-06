<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

$album_id = $_GET['album_id'] ?? null;

if (!$album_id) {
    die("Album ID is required.");
}

$album = getAlbumById($pdo, $album_id);
if (!$album) {
    die("Album not found.");
}

$photos = getPhotosByAlbum($pdo, $album_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($album['album_name']); ?></title>
    <link rel="stylesheet" href="styles/styles.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <h1><?php echo htmlspecialchars($album['album_name']); ?></h1>

    <div class="albumActions" style="margin-bottom: 20px;">
        <form action="core/handleForms.php" method="POST" style="display:flow-root;">
            <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
            <input type="submit" name="deleteAlbumBtn" value="Delete Album" onclick="return confirm('Are you sure you want to delete this album? This action cannot be undone.')"><br><br>
        </form>

        <form action="core/handleForms.php" method="POST" style="display:flow-root;">
            <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
            <input type="text" name="new_album_name" placeholder="New Album Name" required><br><br>
            <input type="submit" name="editAlbumBtn" value="Edit Album"><br><br>
        </form>
    </div>

    <div class="insertPhotoForm" style="margin-bottom: 20px;">
        <h3>Add a Photo to This Album</h3>
        <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
            <p>
                <label for="description">Description:</label>
                <input type="text" name="photoDescription" required>
            </p>
            <p>
                <label for="image">Photo:</label>
                <input type="file" name="image" required>
            </p>
            <p>
                <input type="submit" name="insertPhotoBtn" value="Upload Photo">
            </p>
        </form>
    </div>

    <?php if (!empty($photos)) { ?>
        <div class="albumPhotos" style="display: flex; flex-wrap: wrap; gap: 20px;">
            <?php foreach ($photos as $photo) { ?>
                <div class="photoContainer" style="width: 30%; border: 1px solid gray; padding: 10px;">
                    <img src="images/<?php echo $photo['photo_name']; ?>" alt="" style="width: 100%;">
                    <h4><?php echo htmlspecialchars($photo['description']); ?></h4>
                    <p><i><?php echo $photo['date_added']; ?></i></p>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <p>No photos found in this album.</p>
    <?php } ?>
</body>

</html>