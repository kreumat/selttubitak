<?php
// Start session to store image data
session_start();

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Get the action from the request
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    // Handle JSON data for saveAllImages
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $action = $data['action'] ?? '';
} else {
    $action = '';
}

// Initialize images in session if not set
if (!isset($_SESSION['cellImages'])) {
    $_SESSION['cellImages'] = [];
}

// Process based on action
switch ($action) {
    case 'setImage':
        if (isset($_POST['coord']) && isset($_POST['imageData'])) {
            $coord = $_POST['coord'];
            $imageData = $_POST['imageData'];
            
            // Store image in session
            $_SESSION['cellImages'][$coord] = $imageData;
            
            $response = [
                'success' => true,
                'message' => 'Image set successfully',
                'coord' => $coord,
                'totalImages' => count($_SESSION['cellImages'])
            ];
        } else {
            $response['message'] = 'Missing parameters';
        }
        break;
        
    case 'removeImage':
        if (isset($_POST['coord'])) {
            $coord = $_POST['coord'];
            
            // Remove image from session
            if (isset($_SESSION['cellImages'][$coord])) {
                unset($_SESSION['cellImages'][$coord]);
                
                $response = [
                    'success' => true,
                    'message' => 'Image removed successfully',
                    'coord' => $coord,
                    'totalImages' => count($_SESSION['cellImages'])
                ];
            } else {
                $response['message'] = 'Image not found';
            }
        } else {
            $response['message'] = 'Missing parameters';
        }
        break;
        
    case 'saveAllImages':
        if (isset($data['images']) && is_array($data['images'])) {
            // Save all images at once
            $_SESSION['cellImages'] = $data['images'];
            
            $response = [
                'success' => true,
                'message' => 'All images saved successfully',
                'totalImages' => count($_SESSION['cellImages'])
            ];
        } else {
            $response['message'] = 'Invalid image data';
        }
        break;
        
    case 'getImages':
        // Return all images
        $response = [
            'success' => true,
            'message' => 'Images retrieved successfully',
            'images' => $_SESSION['cellImages'],
            'totalImages' => count($_SESSION['cellImages'])
        ];
        break;
        
    default:
        $response['message'] = 'Unknown action';
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>