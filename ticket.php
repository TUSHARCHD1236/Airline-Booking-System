<?php

require('fpdf.php');

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$airline = $_POST['airline'] ?? '';
$from = $_POST['arr'] ?? '';
$to = $_POST['des'] ?? '';
$class = $_POST['tic'] ?? '';
$travelDate = $_POST['travel_date'] ?? '';
$travelTime = $_POST['travel_time'] ?? '';
$seatNo = $_POST['seat_no'] ?? '';
$passengerCount = $_POST['passenger_count'] ?? '1';
$price = $_POST['price'] ?? '';

$ticketNo = "SKY" . rand(10000, 99999);

function drawFakeQr($pdf, $x, $y, $size) {
    $cells = 9;
    $cell = $size / $cells;
    $pattern = [
        [1,1,1,1,1,0,1,1,1],
        [1,0,0,0,1,0,1,0,1],
        [1,0,1,0,1,0,1,1,1],
        [1,0,0,0,1,0,0,0,1],
        [1,1,1,1,1,0,1,0,1],
        [0,0,0,0,0,1,0,1,0],
        [1,1,1,0,1,1,1,0,1],
        [1,0,1,0,0,0,1,0,1],
        [1,1,1,1,0,1,1,1,1]
    ];

    $pdf->SetFillColor(30, 41, 59);
    $pdf->Rect($x, $y, $size, $size);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($x + 1, $y + 1, $size - 2, $size - 2, 'F');
    $pdf->SetFillColor(30, 41, 59);

    for ($row = 0; $row < $cells; $row++) {
        for ($col = 0; $col < $cells; $col++) {
            if ($pattern[$row][$col] === 1) {
                $pdf->Rect($x + ($col * $cell), $y + ($row * $cell), $cell, $cell, 'F');
            }
        }
    }
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

$pdf->SetFillColor(244, 247, 251);
$pdf->Rect(8, 8, 194, 281, 'F');

$pdf->SetFillColor(13, 110, 253);
$pdf->Rect(12, 12, 186, 28, 'F');

$pdf->Image('logo.jpg', 16, 15, 22);

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 22);
$pdf->SetXY(44, 18);
$pdf->Cell(100, 8, 'SkyHigh Boarding Pass', 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->SetXY(44, 28);
$pdf->Cell(100, 6, 'Demo airline e-ticket', 0, 1);

$pdf->SetDrawColor(220, 226, 232);
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(12, 48, 186, 92, 'DF');

$pdf->SetTextColor(31, 41, 55);
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetXY(18, 56);
$pdf->Cell(75, 8, strtoupper($from), 0, 0);
$pdf->Cell(18, 8, 'TO', 0, 0, 'C');
$pdf->Cell(75, 8, strtoupper($to), 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 116, 139);
$pdf->SetXY(18, 66);
$pdf->Cell(75, 6, 'Departure', 0, 0);
$pdf->Cell(18, 6, '', 0, 0, 'C');
$pdf->Cell(75, 6, 'Arrival', 0, 1, 'R');

$pdf->SetDrawColor(230, 235, 240);
$pdf->Line(18, 78, 192, 78);

$pdf->SetTextColor(31, 41, 55);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetXY(18, 84);
$pdf->Cell(42, 7, 'Passenger');
$pdf->Cell(42, 7, 'Ticket No');
$pdf->Cell(42, 7, 'Class');
$pdf->Cell(42, 7, 'Airline', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(18, 92);
$pdf->Cell(42, 7, $name);
$pdf->Cell(42, 7, $ticketNo);
$pdf->Cell(42, 7, $class);
$pdf->Cell(42, 7, $airline, 0, 1);

$pdf->SetXY(18, 106);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(42, 7, 'Travel Date');
$pdf->Cell(42, 7, 'Travel Time');
$pdf->Cell(42, 7, 'Seat');
$pdf->Cell(42, 7, 'Passengers', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(18, 114);
$pdf->Cell(42, 7, $travelDate);
$pdf->Cell(42, 7, $travelTime);
$pdf->Cell(42, 7, $seatNo);
$pdf->Cell(42, 7, $passengerCount, 0, 1);

$pdf->Rect(12, 148, 186, 72, 'DF');
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(31, 41, 55);
$pdf->SetXY(18, 156);
$pdf->Cell(90, 8, 'Fare Summary', 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->SetXY(18, 170);
$pdf->Cell(60, 7, 'Registered Email');
$pdf->Cell(110, 7, $email, 0, 1);

$pdf->SetXY(18, 180);
$pdf->Cell(60, 7, 'Total Price');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(110, 7, 'INR ' . $price, 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 116, 139);
$pdf->SetXY(18, 194);
$pdf->MultiCell(112, 6, 'This is a demo ticket generated for project presentation purposes. QR code below is decorative only.');

drawFakeQr($pdf, 150, 160, 34);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(31, 41, 55);
$pdf->SetXY(147, 198);
$pdf->Cell(40, 6, 'Scan at Gate', 0, 1, 'C');

$pdf->SetDrawColor(200, 208, 216);
$pdf->Line(12, 232, 198, 232);

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(107, 114, 128);
$pdf->SetXY(18, 240);
$pdf->Cell(170, 6, 'Please arrive 2 hours before departure. Carry a valid photo ID during check-in.', 0, 1, 'C');

$pdf->Output("D", "Flight_Ticket.pdf");

?>
