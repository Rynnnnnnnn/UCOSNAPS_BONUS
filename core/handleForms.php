<?php
require_once 'dbConfig.php';
require_once 'models.php';

if (isset($_POST['insertNewUserBtn'])) {
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($username) && !empty($first_name) && !empty($last_name) && !empty($password) && !empty($confirm_password)) {
        if ($password == $confirm_password) {
            $insertQuery = insertNewUser($pdo, $username, $first_name, $last_name, password_hash($password, PASSWORD_DEFAULT));
            $_SESSION['message'] = $insertQuery['message'];

            if ($insertQuery['status'] == '200') {
                $_SESSION['status'] = $insertQuery['status'];
                header("Location: ../login.php");
            } else {
                $_SESSION['status'] = $insertQuery['status'];
                header("Location: ../register.php");
            }
        } else {
            $_SESSION['message'] = "Please make sure both passwords are equal";
            $_SESSION['status'] = '400';
            header("Location: ../register.php");
        }
    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = '400';
        header("Location: ../register.php");
    }
}

if (isset($_POST['loginUserBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $loginQuery = checkIfUserExists($pdo, $username);

        if (!empty($loginQuery['userInfoArray'])) {
            $userIDFromDB = $loginQuery['userInfoArray']['user_id'];
            $usernameFromDB = $loginQuery['userInfoArray']['username'];
            $passwordFromDB = $loginQuery['userInfoArray']['password'];

            if (password_verify($password, $passwordFromDB)) {
                $_SESSION['user_id'] = $userIDFromDB;
                $_SESSION['username'] = $usernameFromDB;
                header("Location: ../index.php");
            } else {
                $_SESSION['message'] = "Username/password invalid";
                $_SESSION['status'] = "400";
                header("Location: ../login.php");
            }
        } else {
            $_SESSION['message'] = "User not found.";
            $_SESSION['status'] = "400";
            header("Location: ../login.php");
        }
    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = '400';
        header("Location: ../register.php");
    }
}

if (isset($_GET['logoutUserBtn'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    header("Location: ../login.php");
}

if (isset($_POST['insertPhotoBtn'])) {
    $description = $_POST['photoDescription'];
    $album_id = $_POST['album_id']; // Album ID from the form
    $username = $_SESSION['username'];

    // Handle file upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../images/";
        $fileName = basename($_FILES['image']['name']);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            // Insert photo into the database
            $stmt = $pdo->prepare("INSERT INTO photos (photo_name, description, album_id, username, date_added) VALUES (:photo_name, :description, :album_id, :username, NOW())");
            $stmt->execute([
                'photo_name' => $fileName,
                'description' => $description,
                'album_id' => $album_id,
                'username' => $username,
            ]);
            header("Location: ../viewAlbum.php?album_id=$album_id");
        } else {
            die("Failed to upload photo.");
        }
    } else {
        die("Please select a photo to upload.");
    }
}


if (isset($_POST['deletePhotoBtn'])) {
    $photo_name = $_POST['photo_name'] ?? null;
    $photo_id = $_POST['photo_id'] ?? null;

    if ($photo_name && $photo_id) {
        $deletePhoto = deletePhoto($pdo, $photo_id);

        if ($deletePhoto) {
            unlink("../images/" . $photo_name);
            header("Location: ../index.php");
        }
    }
}

if (isset($_POST['createAlbumBtn'])) {
    $albumName = trim($_POST['albumName']);

    if (!empty($albumName)) {
        $createAlbum = createAlbum($pdo, $albumName, $_SESSION['username']);
        if ($createAlbum) {
            $_SESSION['message'] = "Album created successfully.";
        } else {
            $_SESSION['message'] = "Failed to create album.";
        }
    } else {
        $_SESSION['message'] = "Album name cannot be empty.";
    }

    header("Location: ../index.php");
    exit();
}

if (isset($_POST['updateAlbumBtn'])) {
    $albumId = intval($_POST['album_id']);
    $albumName = trim($_POST['albumName']);

    if (!empty($albumId) && !empty($albumName)) {
        $updateAlbum = updateAlbum($pdo, $albumId, $albumName, $_SESSION['username']);
        if ($updateAlbum) {
            $_SESSION['message'] = "Album updated successfully.";
        } else {
            $_SESSION['message'] = "Failed to update album.";
        }
    } else {
        $_SESSION['message'] = "Album name cannot be empty.";
    }

    header("Location: ../index.php");
    exit();
}

if (isset($_POST['deleteAlbumBtn'])) {
    $album_id = $_POST['album_id'];

    // Delete all photos in the album
    $stmt = $pdo->prepare("DELETE FROM photos WHERE album_id = :album_id");
    $stmt->execute(['album_id' => $album_id]);

    // Delete the album itself
    $stmt = $pdo->prepare("DELETE FROM albums WHERE album_id = :album_id");
    $stmt->execute(['album_id' => $album_id]);

    header("Location: ../index.php");
    exit();
}

if (isset($_POST['editAlbumBtn'])) {
    $album_id = $_POST['album_id'];
    $new_album_name = $_POST['new_album_name'];

    // Update the album name
    $stmt = $pdo->prepare("UPDATE albums SET album_name = :album_name WHERE album_id = :album_id");
    $stmt->execute([
        'album_name' => $new_album_name,
        'album_id' => $album_id,
    ]);

    header("Location: ../viewAlbum.php?album_id=$album_id");
    exit();
}
