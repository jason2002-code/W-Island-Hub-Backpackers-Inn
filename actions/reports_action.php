<?php
/**
 * HOTEL REPORTS & FEEDBACK API ACTION
 * Provides comprehensive hotel operational, financial, and manifest reporting
 * alongside guest feedback/inquiry management.
 */
require_once __DIR__ . '/../config/db.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'generate_report') {
        generateHotelReport();
    } elseif ($action === 'stats') {
        getDashboardStats();
    } else {
        getReports();
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? $_GET['action'] ?? '';
    
    if ($action === 'generate_report') {
        generateHotelReport($input);
    } else {
        addReport($input);
    }
} elseif ($method === 'PUT') {
    updateReportStatus();
} elseif ($method === 'DELETE') {
    deleteReport();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

/**
 * Generate Comprehensive Hotel Reports
 */
function generateHotelReport($postData = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $params = $postData ?? $_GET;
    $reportType = $params['report_type'] ?? 'financial'; // financial, bookings, daily_manifest, room_performance, feedback
    $dateRange = $params['date_range'] ?? 'this_month';
    $customFrom = $params['from_date'] ?? '';
    $customTo = $params['to_date'] ?? '';
    $statusFilter = $params['status'] ?? 'all';
    $roomTypeFilter = $params['room_type'] ?? 'all';
    $paymentMethodFilter = $params['payment_method'] ?? 'all';
    $dateField = $params['date_field'] ?? 'created_at'; // created_at or check_in

    // 1. Calculate Date Range Bounds
    $today = date('Y-m-d');
    $startDate = $today;
    $endDate = $today;
    $rangeLabel = 'Today';

    switch ($dateRange) {
        case 'today':
            $startDate = $today;
            $endDate = $today;
            $rangeLabel = 'Today (' . date('M d, Y') . ')';
            break;
        case 'yesterday':
            $startDate = date('Y-m-d', strtotime('-1 day'));
            $endDate = $startDate;
            $rangeLabel = 'Yesterday (' . date('M d, Y', strtotime($startDate)) . ')';
            break;
        case 'this_week':
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = $today;
            $rangeLabel = 'This Week (' . date('M d', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)) . ')';
            break;
        case 'last_7_days':
            $startDate = date('Y-m-d', strtotime('-6 days'));
            $endDate = $today;
            $rangeLabel = 'Last 7 Days (' . date('M d', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)) . ')';
            break;
        case 'this_month':
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            $rangeLabel = 'This Month (' . date('F Y') . ')';
            break;
        case 'last_month':
            $startDate = date('Y-m-01', strtotime('first day of last month'));
            $endDate = date('Y-m-t', strtotime('last day of last month'));
            $rangeLabel = 'Last Month (' . date('F Y', strtotime($startDate)) . ')';
            break;
        case 'this_year':
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
            $rangeLabel = 'This Year (' . date('Y') . ')';
            break;
        case 'all_time':
            $startDate = '2020-01-01';
            $endDate = '2030-12-31';
            $rangeLabel = 'All Time History';
            break;
        case 'custom':
            if (!empty($customFrom) && !empty($customTo)) {
                $startDate = $customFrom;
                $endDate = $customTo;
                $rangeLabel = date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate));
            } else {
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                $rangeLabel = 'Custom Range (' . $startDate . ' to ' . $endDate . ')';
            }
            break;
        default:
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            $rangeLabel = 'Current Month (' . date('F Y') . ')';
    }

    try {
        if ($reportType === 'daily_manifest') {
            generateDailyManifest($pdo, $startDate, $rangeLabel);
            return;
        } elseif ($reportType === 'room_performance') {
            generateRoomPerformanceReport($pdo, $startDate, $endDate, $rangeLabel);
            return;
        } elseif ($reportType === 'feedback') {
            generateFeedbackReport($pdo, $startDate, $endDate, $rangeLabel);
            return;
        }

        // Default: Financial and Bookings Register Reports
        $where = [];
        $paramsSql = [];

        // Date Filter condition
        if ($dateRange !== 'all_time') {
            if ($dateField === 'check_in') {
                $where[] = "b.check_in BETWEEN :start_date AND :end_date";
            } else {
                $where[] = "DATE(b.created_at) BETWEEN :start_date AND :end_date";
            }
            $paramsSql[':start_date'] = $startDate;
            $paramsSql[':end_date'] = $endDate;
        }

        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $where[] = "b.status = :status";
            $paramsSql[':status'] = $statusFilter;
        }

        if ($roomTypeFilter !== 'all' && !empty($roomTypeFilter)) {
            $where[] = "r.type = :room_type";
            $paramsSql[':room_type'] = $roomTypeFilter;
        }

        if ($paymentMethodFilter !== 'all' && !empty($paymentMethodFilter)) {
            $where[] = "b.payment_method = :payment_method";
            $paramsSql[':payment_method'] = $paymentMethodFilter;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Fetch Row-level Bookings
        $sql = "
            SELECT 
                b.*, 
                r.name AS room_name, 
                r.type AS room_type, 
                r.price AS room_base_price, 
                COALESCE(b.guest_name, u.name, 'Guest') AS final_guest_name, 
                COALESCE(u.email, 'N/A') AS guest_email,
                u.created_at AS user_registered_at
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN users u ON b.user_id = u.id
            $whereClause
            ORDER BY b.created_at DESC, b.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($paramsSql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compute Key Metrics
        $totalBookings = count($rows);
        $confirmedCount = 0;
        $pendingCount = 0;
        $cancelledCount = 0;

        $confirmedRevenue = 0.00;
        $pendingRevenue = 0.00;
        $cancelledRevenue = 0.00;
        $totalNightsSold = 0;

        $roomTypeSummary = [];
        $paymentSummary = [];
        $dailySummary = [];

        foreach ($rows as &$b) {
            $status = $b['status'];
            $price = floatval($b['total_price']);
            $nights = max(1, intval($b['nights']));
            $rType = $b['room_type'] ?? 'Standard';
            $payMethod = !empty($b['payment_method']) ? $b['payment_method'] : 'Cash / Front Desk';
            $bDate = !empty($b['created_at']) ? date('Y-m-d', strtotime($b['created_at'])) : $today;

            if ($status === 'Confirmed') {
                $confirmedCount++;
                $confirmedRevenue += $price;
                $totalNightsSold += $nights;

                // Room Type Breakdown
                if (!isset($roomTypeSummary[$rType])) {
                    $roomTypeSummary[$rType] = ['count' => 0, 'revenue' => 0.00, 'nights' => 0];
                }
                $roomTypeSummary[$rType]['count']++;
                $roomTypeSummary[$rType]['revenue'] += $price;
                $roomTypeSummary[$rType]['nights'] += $nights;

                // Payment Breakdown
                if (!isset($paymentSummary[$payMethod])) {
                    $paymentSummary[$payMethod] = ['count' => 0, 'revenue' => 0.00];
                }
                $paymentSummary[$payMethod]['count']++;
                $paymentSummary[$payMethod]['revenue'] += $price;

                // Daily trend
                if (!isset($dailySummary[$bDate])) {
                    $dailySummary[$bDate] = ['date' => $bDate, 'revenue' => 0.00, 'count' => 0];
                }
                $dailySummary[$bDate]['revenue'] += $price;
                $dailySummary[$bDate]['count']++;

            } elseif ($status === 'Pending') {
                $pendingCount++;
                $pendingRevenue += $price;
            } elseif ($status === 'Cancelled') {
                $cancelledCount++;
                $cancelledRevenue += $price;
            }
        }

        $grossRevenue = $confirmedRevenue + $pendingRevenue;
        $averageDailyRate = $totalNightsSold > 0 ? ($confirmedRevenue / $totalNightsSold) : 0.00;
        $averageBookingValue = $confirmedCount > 0 ? ($confirmedRevenue / $confirmedCount) : 0.00;
        
        // Estimated Taxes & Fees portion (Assuming ~₱15 standard fee per booking)
        $taxAndFeesPortion = $confirmedCount * 15.00;
        $netRoomRevenue = max(0, $confirmedRevenue - $taxAndFeesPortion);

        // Sort Daily Trend
        ksort($dailySummary);

        echo json_encode([
            'success' => true,
            'report_type' => $reportType,
            'report_ref' => 'RPT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_at_formatted' => date('F d, Y - h:i A'),
            'date_range_label' => $rangeLabel,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary' => [
                'total_bookings' => $totalBookings,
                'confirmed_bookings' => $confirmedCount,
                'pending_bookings' => $pendingCount,
                'cancelled_bookings' => $cancelledCount,
                'confirmed_revenue' => $confirmedRevenue,
                'pending_revenue' => $pendingRevenue,
                'cancelled_revenue' => $cancelledRevenue,
                'gross_revenue' => $grossRevenue,
                'net_room_revenue' => $netRoomRevenue,
                'tax_and_fees' => $taxAndFeesPortion,
                'total_nights_sold' => $totalNightsSold,
                'average_daily_rate' => $averageDailyRate,
                'average_booking_value' => $averageBookingValue,
            ],
            'room_type_summary' => $roomTypeSummary,
            'payment_summary' => $paymentSummary,
            'daily_summary' => array_values($dailySummary),
            'rows' => $rows
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Report generation query failed: ' . $e->getMessage()]);
    }
}

/**
 * Generate Daily Front Desk Operations Manifest (Arrivals, Departures, In-house)
 */
function generateDailyManifest($pdo, $targetDate, $rangeLabel) {
    try {
        // 1. Expected Arrivals (Check-in = target date)
        $stmtArrivals = $pdo->prepare("
            SELECT b.*, r.name AS room_name, r.type AS room_type, r.capacity, COALESCE(b.guest_name, u.name, 'Guest') AS final_guest_name, COALESCE(u.email, 'N/A') AS guest_email
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN users u ON b.user_id = u.id
            WHERE b.check_in = :target_date AND b.status != 'Cancelled'
            ORDER BY b.status ASC, b.id ASC
        ");
        $stmtArrivals->execute([':target_date' => $targetDate]);
        $arrivals = $stmtArrivals->fetchAll(PDO::FETCH_ASSOC);

        // 2. Expected Departures (Check-out = target date)
        $stmtDepartures = $pdo->prepare("
            SELECT b.*, r.name AS room_name, r.type AS room_type, r.capacity, COALESCE(b.guest_name, u.name, 'Guest') AS final_guest_name, COALESCE(u.email, 'N/A') AS guest_email
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN users u ON b.user_id = u.id
            WHERE b.check_out = :target_date AND b.status != 'Cancelled'
            ORDER BY b.id ASC
        ");
        $stmtDepartures->execute([':target_date' => $targetDate]);
        $departures = $stmtDepartures->fetchAll(PDO::FETCH_ASSOC);

        // 3. Current In-House Guests (Checked in before target date and checking out after target date)
        $stmtInHouse = $pdo->prepare("
            SELECT b.*, r.name AS room_name, r.type AS room_type, r.capacity, COALESCE(b.guest_name, u.name, 'Guest') AS final_guest_name, COALESCE(u.email, 'N/A') AS guest_email
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN users u ON b.user_id = u.id
            WHERE b.check_in < :target_date AND b.check_out > :target_date AND b.status = 'Confirmed'
            ORDER BY r.name ASC, b.id ASC
        ");
        $stmtInHouse->execute([':target_date' => $targetDate]);
        $inHouse = $stmtInHouse->fetchAll(PDO::FETCH_ASSOC);

        // Total active rooms count
        $stmtTotalRooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status != 'Maintenance'");
        $activeRoomsCount = $stmtTotalRooms ? intval($stmtTotalRooms->fetchColumn()) : 1;

        $totalOccupiedToday = count($inHouse) + count($arrivals);
        $estimatedOccupancyRate = $activeRoomsCount > 0 ? min(100, round(($totalOccupiedToday / $activeRoomsCount) * 100, 1)) : 0;

        echo json_encode([
            'success' => true,
            'report_type' => 'daily_manifest',
            'report_ref' => 'MNF-' . date('Ymd', strtotime($targetDate)) . '-' . strtoupper(substr(uniqid(), -4)),
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_at_formatted' => date('F d, Y - h:i A'),
            'target_date' => $targetDate,
            'target_date_formatted' => date('l, F d, Y', strtotime($targetDate)),
            'date_range_label' => 'Front Desk Daily Manifest for ' . date('M d, Y', strtotime($targetDate)),
            'summary' => [
                'expected_arrivals' => count($arrivals),
                'expected_departures' => count($departures),
                'in_house_guests' => count($inHouse),
                'total_active_rooms' => $activeRoomsCount,
                'estimated_occupancy_rate' => $estimatedOccupancyRate,
            ],
            'arrivals' => $arrivals,
            'departures' => $departures,
            'in_house' => $inHouse
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Manifest query failed: ' . $e->getMessage()]);
    }
}

/**
 * Generate Room Occupancy & Utilization Performance Report
 */
function generateRoomPerformanceReport($pdo, $startDate, $endDate, $rangeLabel) {
    try {
        // Fetch all rooms
        $stmtRooms = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
        $rooms = $stmtRooms->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all confirmed bookings within period
        $stmtBookings = $pdo->prepare("
            SELECT b.*, r.id AS r_id
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.status = 'Confirmed'
              AND (
                  (b.check_in BETWEEN :start_date AND :end_date) OR
                  (b.check_out BETWEEN :start_date AND :end_date) OR
                  (b.check_in <= :start_date AND b.check_out >= :end_date)
              )
        ");
        $stmtBookings->execute([':start_date' => $startDate, ':end_date' => $endDate]);
        $allBookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

        // Days in date range
        $diff = max(1, (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1);

        $performanceData = [];
        $totalHotelRevenue = 0.00;
        $totalHotelNights = 0;
        $totalBookingsCount = 0;

        foreach ($rooms as $room) {
            $roomId = $room['id'];
            $roomBookings = array_filter($allBookings, function($b) use ($roomId) {
                return $b['room_id'] == $roomId;
            });

            $bCount = count($roomBookings);
            $revenue = 0.00;
            $nightsSold = 0;

            foreach ($roomBookings as $rb) {
                $revenue += floatval($rb['total_price']);
                $nightsSold += max(1, intval($rb['nights']));
            }

            $occupancyRate = min(100, round(($nightsSold / $diff) * 100, 1));

            $totalHotelRevenue += $revenue;
            $totalHotelNights += $nightsSold;
            $totalBookingsCount += $bCount;

            $performanceData[] = [
                'id' => $room['id'],
                'name' => $room['name'],
                'type' => $room['type'],
                'price' => floatval($room['price']),
                'capacity' => intval($room['capacity']),
                'status' => $room['status'],
                'total_bookings' => $bCount,
                'nights_sold' => $nightsSold,
                'revenue_generated' => $revenue,
                'occupancy_rate' => $occupancyRate,
                'rating' => floatval($room['rating'] ?? 5.0)
            ];
        }

        // Sort by revenue generated descending
        usort($performanceData, function($a, $b) {
            return $b['revenue_generated'] <=> $a['revenue_generated'];
        });

        echo json_encode([
            'success' => true,
            'report_type' => 'room_performance',
            'report_ref' => 'RPR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_at_formatted' => date('F d, Y - h:i A'),
            'date_range_label' => $rangeLabel,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_in_period' => intval($diff),
            'summary' => [
                'total_rooms' => count($rooms),
                'total_confirmed_bookings' => $totalBookingsCount,
                'total_nights_sold' => $totalHotelNights,
                'total_revenue' => $totalHotelRevenue,
                'average_revenue_per_room' => count($rooms) > 0 ? ($totalHotelRevenue / count($rooms)) : 0,
            ],
            'rooms_performance' => $performanceData
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Room performance query failed: ' . $e->getMessage()]);
    }
}

/**
 * Generate Guest Reviews & Feedback Satisfaction Report
 */
function generateFeedbackReport($pdo, $startDate, $endDate, $rangeLabel) {
    try {
        // Reviews
        $stmtReviews = $pdo->query("
            SELECT rv.*, u.name AS user_name, u.email AS user_email, rm.name AS room_name 
            FROM reviews rv
            LEFT JOIN users u ON rv.user_id = u.id
            LEFT JOIN rooms rm ON rv.room_id = rm.id
            ORDER BY rv.created_at DESC
        ");
        $reviews = $stmtReviews ? $stmtReviews->fetchAll(PDO::FETCH_ASSOC) : [];

        // Feedback / Inquiries
        $stmtReports = $pdo->query("
            SELECT r.*, rm.name AS room_name, u.name AS user_name, u.email AS user_email 
            FROM reports r 
            LEFT JOIN bookings b ON r.booking_id = b.id 
            LEFT JOIN rooms rm ON b.room_id = rm.id 
            LEFT JOIN users u ON r.user_id = u.id 
            ORDER BY r.created_at DESC
        ");
        $feedbackList = $stmtReports ? $stmtReports->fetchAll(PDO::FETCH_ASSOC) : [];

        // Ratings breakdown
        $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $totalRatingScore = 0;
        $totalReviews = count($reviews);

        foreach ($reviews as $rev) {
            $star = max(1, min(5, intval(round(floatval($rev['rating'])))));
            $ratingCounts[$star]++;
            $totalRatingScore += floatval($rev['rating']);
        }

        $averageRating = $totalReviews > 0 ? round($totalRatingScore / $totalReviews, 2) : 5.00;
        $positiveReviews = ($ratingCounts[5] + $ratingCounts[4]);
        $satisfactionRate = $totalReviews > 0 ? round(($positiveReviews / $totalReviews) * 100, 1) : 100;

        // Feedback status breakdown
        $pendingFeedback = 0;
        $resolvedFeedback = 0;
        foreach ($feedbackList as $fb) {
            if ($fb['status'] === 'Resolved') $resolvedFeedback++;
            else $pendingFeedback++;
        }

        echo json_encode([
            'success' => true,
            'report_type' => 'feedback',
            'report_ref' => 'QST-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_at_formatted' => date('F d, Y - h:i A'),
            'date_range_label' => $rangeLabel,
            'summary' => [
                'total_reviews' => $totalReviews,
                'average_rating' => $averageRating,
                'satisfaction_rate' => $satisfactionRate,
                'rating_counts' => $ratingCounts,
                'total_feedback' => count($feedbackList),
                'pending_feedback' => $pendingFeedback,
                'resolved_feedback' => $resolvedFeedback
            ],
            'reviews' => $reviews,
            'feedback' => $feedbackList
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Feedback report query failed: ' . $e->getMessage()]);
    }
}

/**
 * Standard Feedback Ticket Methods (Preserved for backwards compatibility)
 */
function getReports() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, b.room_id, rm.name as room_name, u.name as user_name, u.email as user_email
            FROM reports r 
            LEFT JOIN bookings b ON r.booking_id = b.id 
            LEFT JOIN rooms rm ON b.room_id = rm.id 
            LEFT JOIN users u ON r.user_id = u.id 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'reports' => $reports]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function addReport($data = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    if (!$data) {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }
    $userId = $data['user_id'] ?? null;
    $bookingId = $data['booking_id'] ?? null;
    $subject = $data['subject'] ?? null;
    $message = $data['message'] ?? null;
    $type = $data['type'] ?? 'feedback';
    if (!$userId || !$message) {
        echo json_encode(['success' => false, 'message' => 'User ID and message are required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO reports (user_id, booking_id, subject, message, type, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->execute([$userId, $bookingId, $subject, $message, $type]);
        echo json_encode(['success' => true, 'message' => 'Report submitted successfully', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteReport() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    $reportId = $_GET['id'] ?? null;
    if (!$reportId) {
        echo json_encode(['success' => false, 'message' => 'Report ID is required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
        $stmt->execute([$reportId]);
        echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateReportStatus() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $reportId = $data['id'] ?? null;
    $status = $data['status'] ?? null;
    if (!$reportId || !$status) {
        echo json_encode(['success' => false, 'message' => 'Report ID and status are required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("UPDATE reports SET status = ? WHERE id = ?");
        $stmt->execute([$status, $reportId]);
        echo json_encode(['success' => true, 'message' => 'Report status updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getDashboardStats() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    try {
        $stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
        $totalUsers = $stmtUsers ? $stmtUsers->fetchColumn() : 0;
        $stmtBookings = $pdo->query("SELECT COUNT(*) FROM bookings");
        $totalBookings = $stmtBookings ? $stmtBookings->fetchColumn() : 0;
        $stmtRooms = $pdo->query("SELECT COUNT(*) FROM rooms");
        $totalRooms = $stmtRooms ? $stmtRooms->fetchColumn() : 0;
        $stmtRev = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status = 'Confirmed'");
        $totalRevenue = $stmtRev ? $stmtRev->fetchColumn() : 0;
        $stmtPend = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'");
        $pendingBookings = $stmtPend ? $stmtPend->fetchColumn() : 0;
        $stmtConf = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'");
        $confirmedBookings = $stmtConf ? $stmtConf->fetchColumn() : 0;
        $stmtCanc = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Cancelled'");
        $cancelledBookings = $stmtCanc ? $stmtCanc->fetchColumn() : 0;
        $stmtRecent = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
        $recentBookings = $stmtRecent ? $stmtRecent->fetchAll(PDO::FETCH_ASSOC) : [];
        echo json_encode([
            'success' => true,
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalBookings' => $totalBookings,
                'totalRooms' => $totalRooms,
                'totalRevenue' => $totalRevenue,
                'pendingBookings' => $pendingBookings,
                'confirmedBookings' => $confirmedBookings,
                'cancelledBookings' => $cancelledBookings,
                'recentBookings' => $recentBookings
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
