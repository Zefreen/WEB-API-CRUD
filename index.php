<?php
include "../taskAPI/config/db.php";

header("Content-Type: application/json");


$requestMethod = $_SERVER["REQUEST_METHOD"];

//To hanlde if request doesn't exist
$request = isset($_GET['request']) ? explode("/", trim($_GET['request'], "/")) : [];

//How to handle HTTP Verb
$requestMethod;

//GET the task ID
$id = isset($request[1]) ? intval($request[1]) : null;

    switch($requestMethod){
        case 'POST':
            createTask();
            break;
        case 'GET':
            if($id){
                getTask($id);
            }
            else{
                getTasks();
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
        } else {
            echo json_encode(["message" => "Task not found."]);
        }
    } else {
        echo json_encode(["message" => "Query failed: " . mysqli_error($conn)]);
    }
}
?>