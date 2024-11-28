<?php
include "../WEB-API-CRUD/config/db.php";

header("Content-Type: application/json");


$requestMethod = $_SERVER["REQUEST_METHOD"];

//How to handle HTTP Verb
$requestMethod;

//GET the task ID
$taskid = isset($_GET['id']) ? intval($_GET['id']) : null;


    switch($requestMethod){
        case 'POST':
            createTask();
            break;

        case 'GET':
            if($taskid){
                getTask($taskid);
            }
            else{
                getTasks();
            }
            
            break;

        case 'DELETE':
            if($taskid){
                DelTask($taskid);
            }
            else{
               DelTasks();
            }
            break;

        case 'PATCH':
            if ($taskid) {
                Patchstatus($taskid); 
            } 
            else {
                http_response_code(400); 
                echo json_encode(["message" => "Task ID is required."]);
            }
            break;
    default:
    http_response_code(405);
    echo json_encode(["message" => "Method not exisiting."]);
    }

mysqli_close($conn);
?>


<?php
function createTask(){
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);

    $title = $data['title'];
    $description = $data['description'];

    if (!empty($title)){
        $sql = "INSERT INTO task (title, description) VALUES ('$title', '$description')";

        if(mysqli_query($conn, $sql)){
            http_response_code(201);
            echo json_encode(["message" => "Task created!"]);
        }
        else{
            http_response_code(500);
            echo json_encode(["message" => "Error!!!"]);
        }
    }

    else{
        http_response_code(400);
        echo json_encode(["message" => "Title is required."]);
    }
}


function Patchstatus($id){
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['status'])) {
        $status = intval($data['status']);
        if ($status !== 0 && $status !== 1) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid, must be 0 (unfinished) or 1 (finished)."]);
            return;
        }
    $sql = "UPDATE task SET status = $status WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $Rows = mysqli_affected_rows($conn);
        if ($Rows > 0) {
        echo json_encode(["message" => "$Rows task status updated."]);
        } 
        else {
        http_response_code(404);
        echo json_encode(["message" => "Task not found."]);
        }
    }
    else {
        echo json_encode(["message" => "Query failed: " . mysqli_error($conn)]);
    }
    }
    else {
        echo json_encode(["message" => "Status is required."]);
    }
}

function getTasks(){
    global $conn;

    $sql = "SELECT * FROM task";
    $result = mysqli_query($conn, $sql);

    $task = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($task);
}

function getTask($id){
    global $conn;

    $sql = "SELECT * FROM  task WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode($row);
        } 
        else {
            http_response_code(404);
            echo json_encode(["message" => "Task not found."]);
        }
    } else {
        echo json_encode(["message" => "Query failed: " . mysqli_error($conn)]);
    }
}

function DelTasks(){
    global $conn;

    $sql = "DELETE FROM task";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $deletedRows = mysqli_affected_rows($conn);
        echo json_encode(["message" => "$deletedRows tasks deleted."]);
    } 
    else {
        echo json_encode(["message" => "Query failed: " . mysqli_error($conn)]);
    }
}

function DelTask($delid){
    global $conn;

    $sql = "DELETE FROM task WHERE id = $delid";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode(["message" => "Task deleted."]);
        } 
        else {
            http_response_code(404);
            echo json_encode(["message" => "Task not found."]);
        }
    } 
    else {
        echo json_encode(["message" => "Query failed: " . mysqli_error($conn)]);
    }
}
?>