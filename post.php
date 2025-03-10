<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = trim($_POST['caption']);
    $image_path = null; // Initialize image_path as null

    // Check if either a caption or an image is provided
    if (empty($caption) && empty($_FILES['image']['name'])) {
        echo "You must provide either a caption or an image.";
        exit;
    }

    // Check if an image was uploaded
    if (!empty($_FILES['image']['name'])) {
        $image_name = basename($_FILES['image']['name']);
        $image_path = 'uploads/' . $image_name;

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            echo "Only JPEG, PNG, and GIF images are allowed.";
            exit;
        }

        // Validate file size (e.g., 5MB limit)
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            echo "File size must be less than 5MB.";
            exit;
        }

        // Move uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            echo "Failed to upload image.";
            exit;
        }
    }

    // Insert post into the database
    $stmt = $conn->prepare("INSERT INTO posts (user_id, caption, image_path) VALUES (:user_id, :caption, :image_path)");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':caption', $caption);
    $stmt->bindParam(':image_path', $image_path);

    if ($stmt->execute()) {
        header('Location: index.php');
    } else {
        echo "Failed to create post.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Post</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #121212;
            color: #e0e0e0;
            font-family: Arial, sans-serif;
        }
        
        .tweet-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
            background: #1e1e1e;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
        
        .tweet-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .tweet-textarea {
            width: 100%;
            min-height: 120px;
            padding: 2px;
            border: 1px solid #333;
            background: #2c2c2c;
            color: #e0e0e0;
            resize: none;
            font-size: 18px;
            font-family: inherit;
            outline: none;
            border-radius: 10px;
        }
        
        .tweet-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #333;
            padding-top: 15px;
        }
        
        .image-upload {
            display: flex;
            align-items: center;
            color: #1DA1F2;
        }
        
        .tweet-button {
            background: #1DA1F2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .tweet-button:hover {
            background: #1a91da;
        }

        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
            border-radius: 10px;
        }
    </style>
    <script>
        function previewImage() {
            const file = document.getElementById('image').files[0];
            const preview = document.getElementById('image-preview');
            const reader = new FileReader();

            reader.onloadend = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
                preview.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    
    <div class="tweet-container">
        <form method="POST" enctype="multipart/form-data" class="tweet-form">
            <textarea name="caption" placeholder="What's happening?" class="tweet-textarea"></textarea>
            <img id="image-preview" class="image-preview" alt="Image Preview">
            <div class="tweet-footer">
                <div class="image-upload">
                    <label for="image" style="cursor: pointer;">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="#1DA1F2">
                            <path d="M19.75 2H4.25C3.01 2 2 3.01 2 4.25v15.5C2 20.99 3.01 22 4.25 22h15.5c1.24 0 2.25-1.01 2.25-2.25V4.25C22 3.01 20.99 2 19.75 2zM4.25 3.5h15.5c.413 0 .75.337.75.75v9.676l-3.858-3.858c-.14-.14-.33-.22-.53-.22h-.003c-.2 0-.393.08-.532.224l-4.317 4.384-1.813-1.806c-.14-.14-.33-.22-.53-.22-.193-.03-.395.08-.535.227L3.5 17.642V4.25c0-.413.337-.75.75-.75zm-.744 16.28l5.418-5.534 6.282 6.254H4.25c-.402 0-.727-.322-.744-.72zm16.244.72h-2.42l-5.007-4.987 3.792-3.85 4.385 4.384v3.703c0 .413-.337.75-.75.75z"/>
                        </svg>
                    </label>
                    <input type="file" name="image" id="image" style="display: none;" onchange="previewImage()">
                </div>
                <button type="submit" class="tweet-button">Tweet</button>
            </div>
        </form>
    </div>
</body>
</html>