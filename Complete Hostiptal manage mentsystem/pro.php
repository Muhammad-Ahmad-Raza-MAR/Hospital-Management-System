<?php
/**
 * MediCore HMS — Backend API
 * Handles all AJAX form submissions and returns JSON responses.
 * 
 * Requires: PHP 7.4+, MySQL/MariaDB
 * Database: hosss (configure below)
 */

// ── CONFIGURATION ──────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hosss');

// ── HEADERS ────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── ERROR HANDLING ──────────────────────────────────────────────
error_reporting(0);  // Suppress PHP warnings from leaking into JSON
ini_set('display_errors', 0);

// ── HELPER FUNCTIONS ────────────────────────────────────────────
function json_success(string $message = 'Record saved successfully.', array $data = []): void {
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}

function json_error(string $message = 'An error occurred.', int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function clean(mysqli $conn, mixed $val): string {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim((string)$val))));
}

function require_fields(array $fields, array $post): void {
    $missing = [];
    foreach ($fields as $f) {
        if (!isset($post[$f]) || trim($post[$f]) === '') {
            $missing[] = $f;
        }
    }
    if (!empty($missing)) {
        json_error('Missing required fields: ' . implode(', ', $missing));
    }
}

// ── REQUEST VALIDATION ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

// ── DB CONNECTION ───────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    json_error('Database connection failed. Please contact system administrator.', 500);
}

// ── ROUTER ──────────────────────────────────────────────────────
$formType = $_POST['formType'] ?? '';

switch ($formType) {

    // ── PATIENT ───────────────────────────────────────────────
    case 'patientForm':
        require_fields(['firstName','lastName','dob','gender','email','phone1','address'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO patients (firstName, lastName, dob, gender, address, phone1, phone2, email)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $firstName = clean($conn, $_POST['firstName']);
        $lastName  = clean($conn, $_POST['lastName']);
        $dob       = clean($conn, $_POST['dob']);
        $gender    = clean($conn, $_POST['gender']);
        $address   = clean($conn, $_POST['address']);
        $phone1    = clean($conn, $_POST['phone1']);
        $phone2    = clean($conn, $_POST['phone2'] ?? '');
        $email     = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        $stmt->bind_param('ssssssss', $firstName, $lastName, $dob, $gender, $address, $phone1, $phone2, $email);
        $stmt->execute() ? json_success('Patient registered successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to register patient: ' . $stmt->error);
        $stmt->close();
        break;

    // ── DOCTOR ────────────────────────────────────────────────
    case 'doctorForm':
        require_fields(['doctorFirstName','doctorLastName','specialization','doctorEmail','doctorPhone'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO doctors (firstName, lastName, specialty, phone1, email, experienceYears)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $firstName  = clean($conn, $_POST['doctorFirstName']);
        $lastName   = clean($conn, $_POST['doctorLastName']);
        $specialty  = clean($conn, $_POST['specialization']);
        $phone      = clean($conn, $_POST['doctorPhone']);
        $email      = filter_var($_POST['doctorEmail'], FILTER_SANITIZE_EMAIL);
        $expYears   = intval($_POST['experienceYears'] ?? 0);

        $stmt->bind_param('sssssi', $firstName, $lastName, $specialty, $phone, $email, $expYears);
        $stmt->execute() ? json_success('Doctor registered successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to register doctor: ' . $stmt->error);
        $stmt->close();
        break;

    // ── NURSE ─────────────────────────────────────────────────
    case 'nurseForm':
        require_fields(['nurseFirstName','nurseLastName','nurseDepartment','nurseEmail','nursePhone1'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO nurses (firstName, lastName, department, phone1, email)
             VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $firstName  = clean($conn, $_POST['nurseFirstName']);
        $lastName   = clean($conn, $_POST['nurseLastName']);
        $dept       = clean($conn, $_POST['nurseDepartment']);
        $phone      = clean($conn, $_POST['nursePhone1']);
        $email      = filter_var($_POST['nurseEmail'], FILTER_SANITIZE_EMAIL);

        $stmt->bind_param('sssss', $firstName, $lastName, $dept, $phone, $email);
        $stmt->execute() ? json_success('Nurse registered successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to register nurse: ' . $stmt->error);
        $stmt->close();
        break;

    // ── STAFF ─────────────────────────────────────────────────
    case 'staffForm':
        require_fields(['staffFirstName','staffLastName','staffDepartment','staffEmail','staffPhone1'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO staff (firstName, lastName, department, phone1, email)
             VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $firstName = clean($conn, $_POST['staffFirstName']);
        $lastName  = clean($conn, $_POST['staffLastName']);
        $dept      = clean($conn, $_POST['staffDepartment']);
        $phone     = clean($conn, $_POST['staffPhone1']);
        $email     = filter_var($_POST['staffEmail'], FILTER_SANITIZE_EMAIL);

        $stmt->bind_param('sssss', $firstName, $lastName, $dept, $phone, $email);
        $stmt->execute() ? json_success('Staff member added successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to add staff: ' . $stmt->error);
        $stmt->close();
        break;

    // ── DEPARTMENT ────────────────────────────────────────────
    case 'departmentForm':
        require_fields(['departmentName','location'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO departments (departmentName, location) VALUES (?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $deptName = clean($conn, $_POST['departmentName']);
        $location = clean($conn, $_POST['location']);

        $stmt->bind_param('ss', $deptName, $location);
        $stmt->execute() ? json_success('Department created successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to create department: ' . $stmt->error);
        $stmt->close();
        break;

    // ── ROOM ──────────────────────────────────────────────────
    case 'roomForm':
        require_fields(['roomNumber','roomType','capacity','availabilityStatus'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO rooms (roomNumber, roomType, capacity, availabilityStatus)
             VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $roomNumber = clean($conn, $_POST['roomNumber']);
        $roomType   = clean($conn, $_POST['roomType']);
        $capacity   = intval($_POST['capacity']);
        $status     = clean($conn, $_POST['availabilityStatus']);

        $stmt->bind_param('ssis', $roomNumber, $roomType, $capacity, $status);
        $stmt->execute() ? json_success('Room registered successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to register room: ' . $stmt->error);
        $stmt->close();
        break;

    // ── APPOINTMENT ───────────────────────────────────────────
    case 'appointmentForm':
        require_fields(['appointmentDate','reason','status','patientID','doctorID'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO appointments (appointmentDate, reason, status, patientID, doctorID)
             VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $date      = clean($conn, $_POST['appointmentDate']);
        $reason    = clean($conn, $_POST['reason']);
        $status    = clean($conn, $_POST['status']);
        $patientID = intval($_POST['patientID']);
        $doctorID  = intval($_POST['doctorID']);

        $stmt->bind_param('sssii', $date, $reason, $status, $patientID, $doctorID);
        $stmt->execute() ? json_success('Appointment booked successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to book appointment: ' . $stmt->error);
        $stmt->close();
        break;

    // ── TREATMENT ─────────────────────────────────────────────
    case 'treatmentForm':
        require_fields(['treatmentDate','description','cost','treatmentPatientID'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO treatments (treatmentDate, description, cost, patientID)
             VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $date      = clean($conn, $_POST['treatmentDate']);
        $desc      = clean($conn, $_POST['description']);
        $cost      = floatval($_POST['cost']);
        $patientID = intval($_POST['treatmentPatientID']);

        $stmt->bind_param('ssdi', $date, $desc, $cost, $patientID);
        $stmt->execute() ? json_success('Treatment recorded successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to record treatment: ' . $stmt->error);
        $stmt->close();
        break;

    // ── PRESCRIPTION ──────────────────────────────────────────
    case 'prescriptionForm':
        require_fields(['prescriptionDate','medication','dosage','patientID','doctorID'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO prescriptions (prescriptionDate, medication, dosage, patientID, doctorID)
             VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $date      = clean($conn, $_POST['prescriptionDate']);
        $medication= clean($conn, $_POST['medication']);
        $dosage    = clean($conn, $_POST['dosage']);
        $patientID = intval($_POST['patientID']);
        $doctorID  = intval($_POST['doctorID']);

        $stmt->bind_param('sssii', $date, $medication, $dosage, $patientID, $doctorID);
        $stmt->execute() ? json_success('Prescription written successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to write prescription: ' . $stmt->error);
        $stmt->close();
        break;

    // ── BILLING ───────────────────────────────────────────────
    case 'billingForm':
        require_fields(['billingDate','amount','billingPatientID','paymentStatus'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO billing (billingDate, amount, patientID, paymentStatus)
             VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $date      = clean($conn, $_POST['billingDate']);
        $amount    = floatval($_POST['amount']);
        $patientID = intval($_POST['billingPatientID']);
        $status    = clean($conn, $_POST['paymentStatus']);

        $stmt->bind_param('sdis', $date, $amount, $patientID, $status);
        $stmt->execute() ? json_success('Bill generated successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to generate bill: ' . $stmt->error);
        $stmt->close();
        break;

    // ── INSURANCE ─────────────────────────────────────────────
    case 'insuranceForm':
        require_fields(['insuranceProvider','policyNumber','insurancePatientID','coverageDetails'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO insurance (providerName, policyNumber, patientID, coverageDetails)
             VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $provider  = clean($conn, $_POST['insuranceProvider']);
        $policy    = clean($conn, $_POST['policyNumber']);
        $patientID = intval($_POST['insurancePatientID']);
        $coverage  = clean($conn, $_POST['coverageDetails']);

        $stmt->bind_param('ssis', $provider, $policy, $patientID, $coverage);
        $stmt->execute() ? json_success('Insurance record added successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to add insurance record: ' . $stmt->error);
        $stmt->close();
        break;

    // ── LAB TEST ──────────────────────────────────────────────
    case 'labTestForm':
    case 'labTestForm2':
        require_fields(['testDate','testResult','labTestPatientID'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO labtests (testDate, testResult, patientID) VALUES (?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $date      = clean($conn, $_POST['testDate']);
        $result    = clean($conn, $_POST['testResult']);
        $patientID = intval($_POST['labTestPatientID']);

        $stmt->bind_param('ssi', $date, $result, $patientID);
        $stmt->execute() ? json_success('Lab test recorded successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to record lab test: ' . $stmt->error);
        $stmt->close();
        break;

    // ── MEDICATION ────────────────────────────────────────────
    case 'medicationForm':
        require_fields(['medicationName','dosage','medicationPatientID'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO medications (medicationName, dosage, patientID) VALUES (?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $medName   = clean($conn, $_POST['medicationName']);
        $dosage    = clean($conn, $_POST['dosage']);
        $patientID = intval($_POST['medicationPatientID']);

        $stmt->bind_param('ssi', $medName, $dosage, $patientID);
        $stmt->execute() ? json_success('Medication assigned successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to assign medication: ' . $stmt->error);
        $stmt->close();
        break;

    // ── SURGERY ───────────────────────────────────────────────
    case 'surgeryForm':
    case 'surgeryForm2':
        require_fields(['surgeryDate','description','surgeryPatientID'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO surgeries (surgeryDate, description, patientID) VALUES (?, ?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $date      = clean($conn, $_POST['surgeryDate']);
        $desc      = clean($conn, $_POST['description']);
        $patientID = intval($_POST['surgeryPatientID']);

        $stmt->bind_param('ssi', $date, $desc, $patientID);
        $stmt->execute() ? json_success('Surgery record saved successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to save surgery record: ' . $stmt->error);
        $stmt->close();
        break;

    // ── FEEDBACK ──────────────────────────────────────────────
    case 'feedbackForm':
    case 'feedbackForm2':
        require_fields(['feedbackText','feedbackPatientID'], $_POST);

        $stmt = $conn->prepare(
            "INSERT INTO feedback (feedbackText, patientID) VALUES (?, ?)"
        );
        if (!$stmt) { json_error('Database prepare error.', 500); }

        $text      = clean($conn, $_POST['feedbackText']);
        $patientID = intval($_POST['feedbackPatientID']);

        $stmt->bind_param('si', $text, $patientID);
        $stmt->execute() ? json_success('Feedback submitted successfully.', ['id' => $conn->insert_id])
                         : json_error('Failed to submit feedback: ' . $stmt->error);
        $stmt->close();
        break;

    // ── UNKNOWN FORM ──────────────────────────────────────────
    default:
        json_error('Unknown form type: ' . htmlspecialchars($formType), 400);
}

$conn->close();
