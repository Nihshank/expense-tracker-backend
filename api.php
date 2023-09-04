<?php
    include 'db.php';

    // Allow requests from the specific frontend domain
    header("Access-Control-Allow-Origin: http://localhost:8080");

    // Allow specific HTTP methods
    header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE");

    // Allow specific headers
    header("Access-Control-Allow-Headers: Content-Type");

    // Allow credentials (if needed)
    header("Access-Control-Allow-Credentials: true");

    // Set the content type for the response
    header("Content-Type: application/json");


    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the 'id' parameter is present in the URL
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']); // Convert the 'id' parameter to an integer

            // Construct a query to fetch a specific transaction by its ID
            $query = "SELECT * FROM transactions WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$id]);

            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($transaction) {
                // If a transaction with the specified ID was found, return it as JSON
                echo json_encode($transaction);
            } else {
                // If no transaction with the specified ID was found, return an error or an empty response
                http_response_code(404); // Not Found status code
                echo json_encode(array("message" => "Transaction not found."));
            }
        } else {
            // If 'id' parameter is not present, fetch all transactions
            $query = "SELECT * FROM transactions";
            $stmt = $conn->prepare($query);
            $stmt->execute();

            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($transactions);
        }
    }

    if($_SERVER['REQUEST_METHOD'] === 'DELETE'){

        $id = intval($_GET['id']);

        $query = "DELETE FROM transactions WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
    }

    if($_SERVER['REQUEST_METHOD'] === 'PATCH'){

        $id = intval($_GET['id']);

        $input_data = file_get_contents('php://input');
    
        // Decode the JSON data
        $data = json_decode($input_data);

        $amount = doubleval($data->amount);

        $query = "UPDATE transactions SET amount = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$amount, $id]);

        if ($stmt->execute([$amount, $id])) {
            // Successful update
            http_response_code(200);
            echo json_encode(array("message" => "Transaction amount updated successfully"));
        } else {
            // No records were updated
            http_response_code(404);
            echo json_encode(array("message" => "Transaction not found"));
        }
        
        

    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {


        $input_data = file_get_contents('php://input');
    
        // Decode the JSON data
        $data = json_decode($input_data);

        // Get and sanitize the description
        $description = filter_var($data->transaction_description, FILTER_SANITIZE_SPECIAL_CHARS);
        $amount = $data->amount;


        // Validate the description
        $description_err = empty($description) ? 'Please enter a description' : '';

        // Validate the amount
        $amount_err =  empty($amount) ? 'Please enter the amount of money' :
                      (!is_numeric($amount) ? 'Amount must be a valid number' : '');

        // Check if transaction is an expense
        $is_expense = $data->is_expense ? 1 : 0;

        // If no errors, insert into the database
        if (empty($description_err) && empty($amount_err)) {

            $query = "INSERT INTO transactions (transaction_description, amount, is_expense) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);

            if ($stmt->execute([$description, $amount, $is_expense])) {
                $response = ['status' => 'success', 'message' => 'Transaction submitted successfully'];
            } 
            else {
                // Error in insertion
                $errorInfo = $stmt->errorInfo();
                // Log or output the error information
                error_log("Database Error: " . $errorInfo[2]);
                $response = ['status' => 'error', 'message' => 'Error submitting transaction'];
            }

            echo json_encode($response);
        } 
        else {
            // Validation errors
            $response = ['status' => 'error', 'message' => 'Validation errors', 'description_err' => $description_err, 'amount_err' => $amount_err];
            echo json_encode($response);
            

        }
    }
?>
