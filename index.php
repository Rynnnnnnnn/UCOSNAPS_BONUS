<?php require_once 'core/dbConfig.php'; ?>
<?php require_once 'core/models.php'; ?>
<?php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles/styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="insertPhotoForm">
        <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
            <p>
                <label for="#">Description</label>
                <input type="text" name="photoDescription" placeholder="Type Here...">
            </p>
            <p>
                <label for="album">Album</label>
                <select name="album_id">
                    <option value="">None</option>
                    <?php
                    $albums = getAlbumsByUser($pdo, $_SESSION['username']);
                    foreach ($albums as $album) { ?>
                        <option value="<?php echo $album['album_id']; ?>">
                            <?php echo htmlspecialchars($album['album_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </p>
            <p>
                <label for="#">Photo Upload: </label>
                <input type="file" name="image"><br><br>
                <input type="submit" name="insertPhotoBtn" style="display: flex; justify-content: center;">
            </p>
        </form>
    </div>

    <div class="albumForm">
        <form action="core/handleForms.php" method="POST">
            <p>
                <label for="albumName">Create Album</label>
                <input type="text" name="albumName" placeholder="Type Here...">
                <input type="submit" name="createAlbumBtn" value="Create Album" style="margin-top: 10px;">
            </p>
        </form>
    </div>

    <div class="main-container" style="display: flex; justify-content: center;">
        <div class="sidebar">
            <h3>Your Albums</h3>
            <?php if (!empty($albums)) { ?>
                <ul>
                    <?php foreach ($albums as $album) { ?>
                        <li>
                            <a href="viewAlbum.php?album_id=<?php echo $album['album_id']; ?>">
                                <strong><?php echo htmlspecialchars($album['album_name']); ?></strong>
                            </a>
                            <form action="core/handleForms.php" method="POST" style="display: inline;">
                                <input type="hidden" name="album_id" value="<?php echo $album['album_id']; ?>">
                            </form>
                        </li>
                    <?php } ?>
                </ul>
        </div>
    <?php } else { ?>
        <p>No albums found. Create one above!</p>
    <?php } ?>
    </div>

    <?php $getAllPhotos = getAllPhotos($pdo); ?>
    <?php foreach ($getAllPhotos as $row) { ?>
        <div class="container">
            <div class="photoContainer">
                <img src="images/<?php echo $row['photo_name']; ?>" alt="" style="width: 100%;">

                <div class="photoDescription" style="padding:25px;">
                    <a href="profile.php?username=<?php echo $row['username']; ?>">
                        <h2><?php echo $row['username']; ?></h2>
                    </a>
                    <p><i><?php echo $row['date_added']; ?></i></p>
                    <h4><?php echo $row['description']; ?></h4>

                    <?php if ($_SESSION['username'] == $row['username']) { ?>
                        <a href="editphoto.php?photo_id=<?php echo $row['photo_id']; ?>" style="float: right;"> Edit </a>
                        <br>
                        <br>
                        <a href="deletephoto.php?photo_id=<?php echo $row['photo_id']; ?>" style="float: right;"> Delete</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
</body>

</html>