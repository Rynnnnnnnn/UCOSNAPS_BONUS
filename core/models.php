<?php

require_once 'dbConfig.php';

function checkIfUserExists($pdo, $username)
{
    $response = array();
    $sql = "SELECT * FROM user_accounts WHERE username = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$username])) {

        $userInfoArray = $stmt->fetch();

        if ($stmt->rowCount() > 0) {
            $response = array(
                "result" => true,
                "status" => "200",
                "userInfoArray" => $userInfoArray
            );
        } else {
            $response = array(
                "result" => false,
                "status" => "400",
                "message" => "User doesn't exist from the database"
            );
        }
    }

    return $response;
}

function insertNewUser($pdo, $username, $first_name, $last_name, $password)
{
    $response = array();
    $checkIfUserExists = checkIfUserExists($pdo, $username);

    if (!$checkIfUserExists['result']) {

        $sql = "INSERT INTO user_accounts (username, first_name, last_name, password) 
		VALUES (?,?,?,?)";

        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$username, $first_name, $last_name, $password])) {
            $response = array(
                "status" => "200",
                "message" => "User successfully inserted!"
            );
        } else {
            $response = array(
                "status" => "400",
                "message" => "An error occured with the query!"
            );
        }
    } else {
        $response = array(
            "status" => "400",
            "message" => "User already exists!"
        );
    }

    return $response;
}

function getAllUsers($pdo)
{
    $sql = "SELECT * FROM user_accounts";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute();

    if ($executeQuery) {
        return $stmt->fetchAll();
    }
}

function getUserByID($pdo, $username)
{
    $sql = "SELECT * FROM user_accounts WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$username]);

    if ($executeQuery) {
        return $stmt->fetch();
    }
}

function insertPhoto($pdo, $imageName, $username, $description, $photo_id = "", $album_id = null)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO photos (photo_name, username, description, album_id) VALUES (:imageName, :username, :description, :album_id)");
        $stmt->execute([
            'imageName' => $imageName,
            'username' => $username,
            'description' => $description,
            'album_id' => $album_id
        ]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}


function getAllPhotos($pdo, $username = null)
{
    if (empty($username)) {
        $sql = "SELECT * FROM photos ORDER BY date_added DESC";
        $stmt = $pdo->prepare($sql);
        $executeQuery = $stmt->execute();

        if ($executeQuery) {
            return $stmt->fetchAll();
        }
    } else {
        $sql = "SELECT * FROM photos WHERE username = ? ORDER BY date_added DESC";
        $stmt = $pdo->prepare($sql);
        $executeQuery = $stmt->execute([$username]);

        if ($executeQuery) {
            return $stmt->fetchAll();
        }
    }
}


function getPhotoByID($pdo, $photo_id)
{
    $sql = "SELECT * FROM photos WHERE photo_id = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$photo_id]);

    if ($executeQuery) {
        return $stmt->fetch();
    }
}


function deletePhoto($pdo, $photo_id)
{
    $sql = "DELETE FROM photos WHERE photo_id  = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$photo_id]);

    if ($executeQuery) {
        return true;
    }
}

function insertComment($pdo, $photo_id, $username, $description)
{
    $sql = "INSERT INTO photos (photo_id, username, description) VALUES(?,?,?)";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$photo_id, $username, $description]);

    if ($executeQuery) {
        return true;
    }
}

function getCommentByID($pdo, $comment_id)
{
    $sql = "SELECT * FROM comments WHERE comment_id = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$comment_id]);

    if ($executeQuery) {
        return $stmt->fetch();
    }
}


function updateComment($pdo, $description, $comment_id)
{
    $sql = "UPDATE comments SET description = ?, WHERE comment_id = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$description, $comment_id,]);

    if ($executeQuery) {
        return true;
    }
}

function deleteComment($pdo, $comment_id)
{
    $sql = "DELETE FROM comments WHERE comment_id = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$comment_id]);

    if ($executeQuery) {
        return true;
    }
}

function getAllPhotosJson($pdo)
{
    if (empty($username)) {
        $sql = "SELECT * FROM photos";
        $stmt = $pdo->prepare($sql);
        $executeQuery = $stmt->execute();

        if ($executeQuery) {
            return $stmt->fetchAll();
        }
    }
}
function createAlbum($pdo, $albumName, $createdBy)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO albums (album_name, created_by) VALUES (:albumName, :createdBy)");
        $stmt->execute(['albumName' => $albumName, 'createdBy' => $createdBy]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function updateAlbum($pdo, $albumId, $albumName, $updatedBy)
{
    try {
        $stmt = $pdo->prepare("UPDATE albums SET album_name = :albumName WHERE album_id = :albumId AND created_by = :updatedBy");
        $stmt->execute(['albumName' => $albumName, 'albumId' => $albumId, 'updatedBy' => $updatedBy]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function getAlbumsByUser($pdo, $username)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM albums WHERE created_by = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getAlbumById($pdo, $album_id)
{
    $stmt = $pdo->prepare("SELECT * FROM albums WHERE album_id = :album_id");
    $stmt->execute(['album_id' => $album_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPhotosByAlbum($pdo, $album_id)
{
    $stmt = $pdo->prepare("SELECT * FROM photos WHERE album_id = :album_id");
    $stmt->execute(['album_id' => $album_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteAlbum($pdo, $album_id)
{
    $stmt = $pdo->prepare("DELETE FROM albums WHERE album_id = :album_id");
    $stmt->execute(['album_id' => $album_id]);
}

function updateAlbumName($pdo, $album_id, $new_album_name)
{
    $stmt = $pdo->prepare("UPDATE albums SET album_name = :album_name WHERE album_id = :album_id");
    $stmt->execute(['album_name' => $new_album_name, 'album_id' => $album_id]);
}
