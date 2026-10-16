<?php
/**
 * HOTEL REPORTS & FEEDBACK API ACTION
 * Provides comprehensive hotel operational, financial, and manifest reporting
 * alongside guest feedback/inquiry management.
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

/**
 * Returns canonical hotel credentials & registration metadata
 */
if (!function_exists('getHotelInformation')) {
    function getHotelInformation() {
        return [
            'name' => 'W Island Backpackers Inn',
            'trade_name' => 'W Island Hub Backpackers Inn',
            'legal_entity' => 'W Island Backpackers Inn Hospitality Services',
            'owner' => 'Welfrida Araneta Bolotaulo',
            'tagline' => 'Cozy Island Haven, Traveler Hub & Bohol Tour Gateway',
            'address' => 'Dao, Dauis - Panglao Center Road, Dauis, Panglao Island, Bohol 6339, Philippines',
            'island' => 'Panglao Island, Bohol',
            'landmarks' => 'Across Wilcon Depot Dauis • Adjacent to Royal Palms 2 • Near Cambagat & Hinagdanan Caves',
            'contact_numbers' => '+63 945 688 2266',
            'primary_phone' => '+63 945 688 2266',
            'secondary_phone' => '+63 945 688 2266',
            'email' => 'wislandhub2025@gmail.com',
            'reservations_email' => 'wislandhub2025@gmail.com',
            'website' => 'www.wislandhub.com',
            'dot_accreditation' => 'DOT-R7-ACC-2024-0318',
            'dot_region' => 'Department of Tourism Region VII (Central Visayas)',
            'tin' => '432-876-543-00000',
            'tax_type' => 'Non-VAT Registered Business',
            'business_permit' => 'BP-2024-DAUIS-0521',
            'lgu' => 'Municipality of Dauis, Province of Bohol',
            'tourism_fee_ref' => 'Panglao Island Environmental & Tourism Development Fee',
            'front_desk_hours' => '06:00 AM - 10:00 PM',
            'check_in_time' => '02:00 PM (14:00)',
            'check_out_time' => '11:00 AM (11:00)'
        ];
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];

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
        if ($reportType === 'front_desk_daily') {
            generateFrontDeskDailyReport($pdo, $startDate, $endDate, $rangeLabel, $params);
            return;
        } elseif ($reportType === 'daily_manifest') {
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

        // 1. Fetch Property Inventory
        $stmtAllRooms = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
        $allRooms = $stmtAllRooms ? $stmtAllRooms->fetchAll(PDO::FETCH_ASSOC) : [];
        $totalInventory = count($allRooms);
        $maintenanceRoomsList = array_filter($allRooms, function($r) { return ($r['status'] ?? '') === 'Maintenance'; });
        $maintenanceCount = count($maintenanceRoomsList);
        $rentableRoomsCount = max(1, $totalInventory - $maintenanceCount);

        // 2. Fetch Row-level Bookings for Main Reservation Ledger
        $sql = "
            SELECT 
                b.*, 
                r.name AS room_name, 
                r.type AS room_type, 
                r.price AS room_base_price, 
                r.capacity AS room_capacity,
                r.status AS room_inventory_status,
                COALESCE(b.guest_name, u.name, 'Guest') AS final_guest_name, 
                COALESCE(b.guest_email, u.email, 'N/A') AS guest_email,
                COALESCE(b.guest_phone, 'N/A') AS guest_phone,
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

        // 3. Compute Key Metrics across the ledger
        $totalBookings = count($rows);
        $confirmedCount = 0;
        $pendingCount = 0;
        $cancelledCount = 0;
        $checkedInCount = 0;
        $checkedOutCount = 0;
        $noShowCount = 0;

        $confirmedRevenue = 0.00;
        $pendingRevenue = 0.00;
        $cancelledRevenue = 0.00;
        $totalDepositsCollected = 0.00;
        $totalOutstandingBalance = 0.00;
        $totalNightsSold = 0;
        $totalOccupantsCount = 0;

        $roomTypeSummary = [];
        $paymentSummary = [];
        $dailySummary = [];
        $specialRemarksList = [];
        $occupiedRoomIds = [];

        foreach ($rows as &$b) {
            $rawStatus = $b['status'] ?? 'Pending';
            $bookingStatus = $b['booking_status'] ?? 'pending_verification';
            $nights = max(1, intval($b['nights'] ?? 1));
            $rType = $b['room_type'] ?? 'Standard';
            $capacity = max(1, intval($b['room_capacity'] ?? 2));
            $bDate = !empty($b['created_at']) ? date('Y-m-d', strtotime($b['created_at'])) : $today;
            
            // Financials
            $totalCharges = floatval($b['total_amount'] > 0 ? $b['total_amount'] : ($b['total_price'] ?? 0));
            $paidAmount = floatval($b['paid_amount'] ?? 0);
            $balanceDue = max(0.00, floatval($b['balance_due'] > 0 ? $b['balance_due'] : ($totalCharges - $paidAmount)));

            // Canonical Operational Status determination
            // Confirmed, Checked In, Checked Out, No-Show, or Cancelled
            $operationalStatus = 'Confirmed';
            $checkInVal = !empty($b['check_in_date']) ? $b['check_in_date'] : $b['check_in'];
            $checkOutVal = !empty($b['check_out_date']) ? $b['check_out_date'] : $b['check_out'];

            if ($rawStatus === 'Cancelled' || $bookingStatus === 'cancelled_expired') {
                $operationalStatus = 'Cancelled';
                $cancelledCount++;
                $cancelledRevenue += $totalCharges;
            } elseif ($bookingStatus === 'no_show') {
                $operationalStatus = 'No-Show';
                $noShowCount++;
            } elseif ($bookingStatus === 'completed' || ($rawStatus === 'Confirmed' && $checkOutVal < $today)) {
                $operationalStatus = 'Checked Out';
                $checkedOutCount++;
                $confirmedCount++;
                $confirmedRevenue += $totalCharges;
                $totalNightsSold += $nights;
                $totalDepositsCollected += $paidAmount;
                $totalOutstandingBalance += $balanceDue;
            } elseif ($rawStatus === 'Confirmed' || in_array($bookingStatus, ['confirmed', 'deposit_paid'])) {
                if ($today >= $checkInVal && $today < $checkOutVal) {
                    $operationalStatus = 'Checked In';
                    $checkedInCount++;
                    if (!empty($b['room_id'])) $occupiedRoomIds[$b['room_id']] = true;
                } else {
                    $operationalStatus = 'Confirmed';
                }
                $confirmedCount++;
                $confirmedRevenue += $totalCharges;
                $totalNightsSold += $nights;
                $totalDepositsCollected += $paidAmount;
                $totalOutstandingBalance += $balanceDue;
            } else { // Pending
                if ($today > $checkInVal) {
                    $operationalStatus = 'No-Show';
                    $noShowCount++;
                } else {
                    $operationalStatus = 'Confirmed';
                }
                $pendingCount++;
                $pendingRevenue += $totalCharges;
                $totalOutstandingBalance += $balanceDue;
            }

            // Occupants breakdown
            $adults = min($capacity, 2);
            $children = max(0, $capacity - 2);
            $occupantsSummary = "{$capacity} Pax ({$adults} Adults" . ($children > 0 ? ", {$children} Children" : ", 0 Children") . ")";
            $totalOccupantsCount += $capacity;

            // Normalized Payment Channel
            $paymentRaw = $b['payment_method'] ?? 'cash';
            $paymentChannel = 'Over-the-counter Cash / Pay at Hotel';
            if (stripos($paymentRaw, 'full_gcash') !== false || ($paymentRaw === 'gcash' && $balanceDue <= 0)) {
                $paymentChannel = 'GCash (Full Payment)';
            } elseif (stripos($paymentRaw, 'deposit') !== false || stripos($paymentRaw, '30%') !== false) {
                $paymentChannel = 'GCash (Down Payment)';
            } elseif (stripos($paymentRaw, 'maya') !== false) {
                $paymentChannel = 'Maya E-Wallet';
            } elseif (stripos($paymentRaw, 'card') !== false || stripos($paymentRaw, 'credit') !== false || stripos($paymentRaw, 'debit') !== false) {
                $paymentChannel = 'Credit / Debit Card';
            } elseif (stripos($paymentRaw, 'grab') !== false) {
                $paymentChannel = 'GrabPay';
            } elseif (stripos($paymentRaw, 'gcash') !== false) {
                $paymentChannel = 'GCash Digital Folio';
            } elseif (stripos($paymentRaw, 'cash') !== false) {
                $paymentChannel = 'Cash (Front Desk)';
            }

            // Down Payment / Deposit Status
            $depositStatus = 'Pending / Unpaid';
            if ($paidAmount >= $totalCharges && $totalCharges > 0) {
                $depositStatus = '100% Fully Settled';
            } elseif ($paidAmount > 0) {
                $depositStatus = 'Deposit Confirmed (₱' . number_format($paidAmount, 2) . ')';
            }

            // Enrich row object
            $b['operational_status'] = $operationalStatus;
            $b['total_charges'] = $totalCharges;
            $b['paid_amount'] = $paidAmount;
            $b['balance_due'] = $balanceDue;
            $b['occupants_summary'] = $occupantsSummary;
            $b['occupants_count'] = $capacity;
            $b['payment_channel'] = $paymentChannel;
            $b['deposit_status'] = $depositStatus;
            $b['check_in_formatted'] = date('M d, Y', strtotime($checkInVal));
            $b['check_out_formatted'] = date('M d, Y', strtotime($checkOutVal));
            $b['created_at_formatted'] = !empty($b['created_at']) ? date('M d, Y h:i A', strtotime($b['created_at'])) : '-';

            // Collect remarks for handover notes
            if (!empty($b['special_requests'])) {
                $specialRemarksList[] = [
                    'ref_id' => $b['ref_id'],
                    'guest' => $b['final_guest_name'],
                    'room' => $b['room_name'] ?? ('Room #' . $b['room_id']),
                    'category' => 'Guest Arrangement',
                    'note' => $b['special_requests']
                ];
            }
            if (!empty($b['expected_arrival_time']) && $b['expected_arrival_time'] >= '18:00:00') {
                $specialRemarksList[] = [
                    'ref_id' => $b['ref_id'],
                    'guest' => $b['final_guest_name'],
                    'room' => $b['room_name'] ?? ('Room #' . $b['room_id']),
                    'category' => 'Flight / Late Arrival',
                    'note' => 'Expected late arrival at ' . date('h:i A', strtotime($b['expected_arrival_time']))
                ];
            }

            // Aggregations for charts & breakdown
            if ($rawStatus === 'Confirmed' || $operationalStatus === 'Confirmed' || $operationalStatus === 'Checked In') {
                if (!isset($roomTypeSummary[$rType])) {
                    $roomTypeSummary[$rType] = ['count' => 0, 'revenue' => 0.00, 'nights' => 0];
                }
                $roomTypeSummary[$rType]['count']++;
                $roomTypeSummary[$rType]['revenue'] += $totalCharges;
                $roomTypeSummary[$rType]['nights'] += $nights;

                if (!isset($paymentSummary[$paymentChannel])) {
                    $paymentSummary[$paymentChannel] = ['count' => 0, 'revenue' => 0.00];
                }
                $paymentSummary[$paymentChannel]['count']++;
                $paymentSummary[$paymentChannel]['revenue'] += $totalCharges;

                if (!isset($dailySummary[$bDate])) {
                    $dailySummary[$bDate] = ['date' => $bDate, 'revenue' => 0.00, 'count' => 0];
                }
                $dailySummary[$bDate]['revenue'] += $totalCharges;
                $dailySummary[$bDate]['count']++;
            }
        }

        // Add maintenance holds into special handover remarks
        foreach ($maintenanceRoomsList as $mr) {
            $specialRemarksList[] = [
                'ref_id' => 'MAINT-' . $mr['id'],
                'guest' => 'Facility Staff',
                'room' => $mr['name'],
                'category' => 'Maintenance Hold',
                'note' => 'Room is currently tagged under Maintenance status (Out of Order)'
            ];
        }

        // 4. Movement Counters Calculation (Check-ins, Check-outs, Stay-overs, Vacant clean/dirty)
        // Check-ins: arriving today or within selected date range
        $stmtMovements = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN (COALESCE(check_in_date, check_in) BETWEEN :s_date AND :e_date AND status != 'Cancelled') THEN 1 ELSE 0 END) AS arrivals_count,
                SUM(CASE WHEN (COALESCE(check_out_date, check_out) BETWEEN :s_date AND :e_date AND status != 'Cancelled') THEN 1 ELSE 0 END) AS departures_count,
                SUM(CASE WHEN (COALESCE(check_in_date, check_in) < :s_date AND COALESCE(check_out_date, check_out) > :e_date AND status = 'Confirmed') THEN 1 ELSE 0 END) AS stayovers_count
            FROM bookings
        ");
        $stmtMovements->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $movementStats = $stmtMovements->fetch(PDO::FETCH_ASSOC);

        $expectedCheckIns = intval($movementStats['arrivals_count'] ?? 0);
        $expectedCheckOuts = intval($movementStats['departures_count'] ?? 0);
        $stayOvers = intval($movementStats['stayovers_count'] ?? 0);

        // Occupancy calculation
        $occupiedCount = max(count($occupiedRoomIds), min($rentableRoomsCount, $checkedInCount + $stayOvers));
        $vacantCleanCount = max(0, $rentableRoomsCount - $occupiedCount);
        $occupancyPercentage = $rentableRoomsCount > 0 ? round(($occupiedCount / $rentableRoomsCount) * 100, 1) : 0.0;

        // Revenue metrics
        $diffDays = max(1, round((strtotime($endDate) - strtotime($startDate)) / 86400) + 1);
        $availableRoomNights = $rentableRoomsCount * $diffDays;
        $averageDailyRate = $totalNightsSold > 0 ? ($confirmedRevenue / $totalNightsSold) : 0.00;
        $revPAR = $availableRoomNights > 0 ? round($confirmedRevenue / $availableRoomNights, 2) : 0.00;
        $averageBookingValue = $confirmedCount > 0 ? ($confirmedRevenue / $confirmedCount) : 0.00;
        $taxAndFeesPortion = 0.00; // Property is Non-VAT Registered; no arbitrary per-booking tax
        $netRoomRevenue = $confirmedRevenue;

        // Sort Daily Trend
        ksort($dailySummary);

        $reportTitle = 'Daily Reservation Summary & Front Office Night Audit';
        if ($reportType === 'financial') {
            $reportTitle = 'Financial & Revenue Summary Report';
        } elseif ($reportType === 'bookings') {
            $reportTitle = 'Hotel Reservations & Main Reservation Ledger';
        }

        echo json_encode([
            'success' => true,
            'report_type' => $reportType,
            'report_title' => $reportTitle,
            'report_ref' => 'RES-AUDIT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'database_instance' => 'w_island_hub',
            'system_version' => 'W Island Hub PMS v2.4.0 (Enterprise Reservation Engine)',
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_at_formatted' => date('F d, Y • h:i:s A') . ' PHT (UTC+8)',
            'hotel_info' => getHotelInformation(),
            'date_range_label' => $rangeLabel,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_in_period' => $diffDays,
            'operational_snapshot' => [
                // Occupancy Percentage
                'occupancy_percentage' => $occupancyPercentage,
                'occupied_rooms' => $occupiedCount,
                'rentable_rooms' => $rentableRoomsCount,
                'total_inventory' => $totalInventory,
                'maintenance_rooms' => $maintenanceCount,
                'vacant_clean_rooms' => $vacantCleanCount,
                
                // Movement Counters
                'expected_check_ins' => $expectedCheckIns,
                'expected_check_outs' => $expectedCheckOuts,
                'stay_overs' => $stayOvers,
                'vacant_clean' => $vacantCleanCount,
                'vacant_dirty' => $maintenanceCount,

                // Revenue Metrics
                'room_sales' => $confirmedRevenue,
                'average_daily_rate' => $averageDailyRate,
                'revpar' => $revPAR,
                'total_pending_balance' => $totalOutstandingBalance,
                'total_deposits_collected' => $totalDepositsCollected,
                'gross_folio_value' => ($confirmedRevenue + $pendingRevenue),
                'net_room_revenue' => $netRoomRevenue,
                'taxes_and_fees' => $taxAndFeesPortion,
                'available_room_nights' => $availableRoomNights,
                'days_in_period' => $diffDays
            ],
            'summary' => [
                'total_bookings' => $totalBookings,
                'confirmed_bookings' => $confirmedCount,
                'checked_in_bookings' => $checkedInCount,
                'checked_out_bookings' => $checkedOutCount,
                'no_show_bookings' => $noShowCount,
                'pending_bookings' => $pendingCount,
                'cancelled_bookings' => $cancelledCount,
                'confirmed_revenue' => $confirmedRevenue,
                'pending_revenue' => $pendingRevenue,
                'cancelled_revenue' => $cancelledRevenue,
                'total_nights_sold' => $totalNightsSold,
                'total_occupants' => $totalOccupantsCount,
                'average_daily_rate' => $averageDailyRate,
                'revpar' => $revPAR,
                'average_booking_value' => $averageBookingValue,
                'net_room_revenue' => $netRoomRevenue,
                'tax_and_fees' => $taxAndFeesPortion,
                'total_pending_balance' => $totalOutstandingBalance,
                'total_deposits_collected' => $totalDepositsCollected,
                'available_room_nights' => $availableRoomNights
            ],
            'room_type_summary' => $roomTypeSummary,
            'payment_summary' => $paymentSummary,
            'daily_summary' => array_values($dailySummary),
            'special_remarks' => $specialRemarksList,
            'rows' => $rows
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Report generation query failed: ' . $e->getMessage()]);
    }
}

/**
 * Generate Hotel Front Desk Business Daily Report (Exact 7-Section Executive Template)
 */
function generateFrontDeskDailyReport($pdo, $startDate, $endDate, $rangeLabel, $params = []) {
    try {
        $today = date('Y-m-d');
        $dateFormatted = date('l, F j, Y', strtotime($startDate));
        $timeRange = '8:00 AM - 8:00 PM'; // Front Desk Operational Shift Hours
        
        // 1. Transaction Summary
        $stmtTrans = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN (COALESCE(check_in_date, check_in) BETWEEN :s AND :e AND status != 'Cancelled') THEN 1 ELSE 0 END) AS total_check_ins,
                SUM(CASE WHEN (COALESCE(check_out_date, check_out) BETWEEN :s AND :e AND status != 'Cancelled') THEN 1 ELSE 0 END) AS total_check_outs,
                SUM(CASE WHEN status != 'Cancelled' AND (DATE(created_at) BETWEEN :s AND :e OR COALESCE(check_in_date, check_in) BETWEEN :s AND :e) THEN 1 ELSE 0 END) AS total_room_reservations,
                SUM(CASE WHEN (status = 'Cancelled' OR booking_status = 'cancelled_expired') AND (DATE(created_at) BETWEEN :s AND :e OR COALESCE(check_in_date, check_in) BETWEEN :s AND :e) THEN 1 ELSE 0 END) AS total_cancellations,
                SUM(CASE WHEN booking_status = 'no_show' AND (COALESCE(check_in_date, check_in) BETWEEN :s AND :e OR DATE(created_at) BETWEEN :s AND :e) THEN 1 ELSE 0 END) AS total_no_shows
            FROM bookings
        ");
        $stmtTrans->execute([':s' => $startDate, ':e' => $endDate]);
        $trans = $stmtTrans->fetch(PDO::FETCH_ASSOC);

        $totalCheckIns = intval($trans['total_check_ins'] ?? 0);
        $totalCheckOuts = intval($trans['total_check_outs'] ?? 0);
        $totalRoomReservations = intval($trans['total_room_reservations'] ?? 0);
        $totalCancellations = intval($trans['total_cancellations'] ?? 0);
        $totalNoShows = intval($trans['total_no_shows'] ?? 0);

        // 2. Room Occupancy Statistics
        $stmtAllRooms = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
        $allRooms = $stmtAllRooms ? $stmtAllRooms->fetchAll(PDO::FETCH_ASSOC) : [];
        $totalRooms = count($allRooms);
        $maintenanceRooms = count(array_filter($allRooms, function($r) { return ($r['status'] ?? '') === 'Maintenance'; }));
        
        $stmtInHouse = $pdo->prepare("
            SELECT COUNT(DISTINCT room_id) FROM bookings 
            WHERE status = 'Confirmed' 
              AND (
                  (:s BETWEEN COALESCE(check_in_date, check_in) AND COALESCE(check_out_date, check_out))
                  OR (COALESCE(check_in_date, check_in) BETWEEN :s AND :e)
              )
        ");
        $stmtInHouse->execute([':s' => $startDate, ':e' => $endDate]);
        $inHouseCount = intval($stmtInHouse->fetchColumn());
        $roomsOccupied = min($totalRooms, $inHouseCount);
        $roomsAvailable = max(0, $totalRooms - $roomsOccupied - $maintenanceRooms);
        $occupancyRate = $totalRooms > 0 ? round(($roomsOccupied / $totalRooms) * 100) : 0;

        // 3. Revenue Statistics
        $stmtRev = $pdo->prepare("
            SELECT 
                COALESCE(SUM(COALESCE(NULLIF(total_amount, 0), total_price)), 0) as room_rev,
                COUNT(*) as conf_count
            FROM bookings
            WHERE status = 'Confirmed'
              AND (DATE(created_at) BETWEEN :s AND :e OR COALESCE(check_in_date, check_in) BETWEEN :s AND :e)
        ");
        $stmtRev->execute([':s' => $startDate, ':e' => $endDate]);
        $revRow = $stmtRev->fetch(PDO::FETCH_ASSOC);
        $roomRevenue = floatval($revRow['room_rev'] ?? 0);
        $confCount = intval($revRow['conf_count'] ?? 0);

        $extraServicesRevenue = 0.00;
        $totalRevenue = $roomRevenue;

        // 4. Payment Summary
        $stmtPay = $pdo->prepare("
            SELECT 
                payment_method,
                COALESCE(SUM(COALESCE(NULLIF(total_amount, 0), total_price)), 0) as amt
            FROM bookings
            WHERE status = 'Confirmed'
              AND (DATE(created_at) BETWEEN :s AND :e OR COALESCE(check_in_date, check_in) BETWEEN :s AND :e)
            GROUP BY payment_method
        ");
        $stmtPay->execute([':s' => $startDate, ':e' => $endDate]);
        $payRows = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

        $cash = 0.00;
        $gcash = 0.00;
        $card = 0.00;
        $otherPayments = 0.00;

        foreach ($payRows as $pr) {
            $pm = strtolower($pr['payment_method'] ?? '');
            $amt = floatval($pr['amt']);
            if (strpos($pm, 'gcash') !== false) {
                $gcash += $amt;
            } elseif (strpos($pm, 'hotel') !== false || strpos($pm, 'cash') !== false) {
                $cash += $amt;
            } elseif (strpos($pm, 'card') !== false || strpos($pm, 'credit') !== false || strpos($pm, 'online') !== false) {
                $card += $amt;
            } else {
                $otherPayments += $amt;
            }
        }

         // 5. Staff Performance & Signatures
        $owner = !empty($params['staff_executive']) ? trim($params['staff_executive']) : 'Welfrida Araneta Bolotaulo';
        $frontDesk = !empty($params['staff_frontdesk']) ? trim($params['staff_frontdesk']) : 'Fredsil Araneta Bolotaulo, Michelle Nee Baco Bolotaulo';
        $shiftLead = !empty($params['staff_nightaudit']) ? trim($params['staff_nightaudit']) : 'Fredsil Araneta Bolotaulo';
        $supervisor = $shiftLead . ' (Night Audit)';

        // 6. Complaints and Feedback
        $complaintsReceived = intval($pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn());
        $issuesResolved = intval($pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'Resolved'")->fetchColumn());
        $stmtReviews = $pdo->query("
            SELECT 
                SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as pos,
                SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as neg
            FROM reviews
        ");
        $revStats = $stmtReviews ? $stmtReviews->fetch(PDO::FETCH_ASSOC) : [];
        $positiveReviews = intval($revStats['pos'] ?? 0);
        $negativeReviews = intval($revStats['neg'] ?? 0);
        $guestFeedback = "Positive: {$positiveReviews}, Negative: {$negativeReviews}";

        // 7. Reservation Overview & Room Type Availability
        $totalReservations = $totalRoomReservations;
        
        $stmtRoomTypes = $pdo->query("
            SELECT type, COUNT(*) as total_count,
                   SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as avail_count
            FROM rooms
            GROUP BY type
        ");
        $roomTypes = $stmtRoomTypes ? $stmtRoomTypes->fetchAll(PDO::FETCH_ASSOC) : [];
        $availPieces = [];
        $roomTypeDetails = [];
        foreach ($roomTypes as $rt) {
            $tName = $rt['type'];
            $aCnt = intval($rt['avail_count']);
            $availPieces[] = "{$tName}: {$aCnt}";
            $roomTypeDetails[$tName] = $aCnt;
        }
        $roomTypeAvailability = implode(' | ', $availPieces);
        if (empty($roomTypeAvailability)) {
            $roomTypeAvailability = 'None Available';
        }

        // Ledger Rows for detailed drill-down (filtered by selected date range)
        $stmtBookings = $pdo->prepare("
            SELECT b.*, r.name as room_name, r.type as room_type, r.price as room_base_price, r.capacity as room_capacity,
                   COALESCE(b.guest_name, u.name, 'Guest') as final_guest_name,
                   COALESCE(b.guest_email, u.email, 'N/A') as guest_email,
                   COALESCE(b.guest_phone, 'N/A') as guest_phone
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN users u ON b.user_id = u.id
            WHERE (DATE(b.created_at) BETWEEN :s AND :e)
               OR (COALESCE(b.check_in_date, b.check_in) BETWEEN :s AND :e)
               OR (COALESCE(b.check_out_date, b.check_out) BETWEEN :s AND :e)
            ORDER BY b.created_at DESC
        ");
        $stmtBookings->execute([':s' => $startDate, ':e' => $endDate]);
        $rows = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $charges = floatval($r['total_amount'] > 0 ? $r['total_amount'] : ($r['total_price'] ?? 0));
            $paid = floatval($r['paid_amount'] ?? 0);
            $r['total_charges'] = $charges;
            $r['paid_amount'] = $paid;
            $r['balance_due'] = max(0.00, $charges - $paid);
            $r['operational_status'] = ($r['status'] === 'Cancelled') ? 'Cancelled' : (($r['booking_status'] === 'no_show') ? 'No-Show' : 'Confirmed');
            $r['check_in_formatted'] = date('M d, Y', strtotime($r['check_in_date'] ?: $r['check_in']));
            $r['check_out_formatted'] = date('M d, Y', strtotime($r['check_out_date'] ?: $r['check_out']));
        }

        $reportRef = 'FDBR-' . date('Ymd', strtotime($startDate)) . '-' . strtoupper(substr(uniqid(), -4));

        echo json_encode([
            'success' => true,
            'report_type' => 'front_desk_daily',
            'report_title' => 'Hotel Front Desk Business Daily Report',
            'report_ref' => $reportRef,
            'date_range_label' => $rangeLabel,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_at_formatted' => date('F d, Y • h:i:s A') . ' PHT (UTC+8)',
            'hotel_info' => getHotelInformation(),
            'front_desk_report' => [
                'title' => 'Hotel Front Desk Business Daily Report',
                'date' => $dateFormatted,
                'time' => $timeRange,
                'sections' => [
                    'transaction_summary' => [
                        'title' => 'Transaction Summary',
                        'items' => [
                            ['label' => 'Total Check-Ins', 'value' => $totalCheckIns],
                            ['label' => 'Total Check-Outs', 'value' => $totalCheckOuts],
                            ['label' => 'Total Room Reservations', 'value' => $totalRoomReservations],
                            ['label' => 'Total Cancellations', 'value' => $totalCancellations],
                            ['label' => 'Total No-Shows', 'value' => $totalNoShows],
                        ]
                    ],
                    'room_occupancy' => [
                        'title' => 'Room Occupancy Statistics',
                        'items' => [
                            ['label' => 'Total Rooms', 'value' => $totalRooms],
                            ['label' => 'Rooms Occupied', 'value' => $roomsOccupied],
                            ['label' => 'Rooms Available', 'value' => $roomsAvailable],
                            ['label' => 'Occupancy Rate', 'value' => $occupancyRate . '%'],
                        ]
                    ],
                    'revenue_statistics' => [
                        'title' => 'Revenue Statistics',
                        'items' => [
                            ['label' => 'Extra Services Revenue', 'value' => $extraServicesRevenue, 'is_currency' => true],
                            ['label' => 'Room Revenue', 'value' => $roomRevenue, 'is_currency' => true],
                            ['label' => 'Total Revenue', 'value' => $totalRevenue, 'is_currency' => true],
                        ]
                    ],
                    'payment_summary' => [
                        'title' => 'Payment Summary',
                        'items' => [
                           ['label' => 'Over-the-counter Cash', 'value' => $cash, 'is_currency' => true],
                            ['label' => 'GCash / Digital', 'value' => $gcash, 'is_currency' => true],
                            ['label' => 'Credit / Debit Cards', 'value' => $card, 'is_currency' => true],
                            ['label' => 'Other Payment Methods', 'value' => $otherPayments, 'is_currency' => true],
                        ]
                    ],
                    'staff_performance' => [
                       'title' => 'Staff & Management',
                        'items' => [
                            ['label' => 'Front Desk', 'value' => $frontDesk],
                            ['label' => 'Night Audit', 'value' => $shiftLead],
                            ['label' => 'Executive', 'value' => $owner],
                        ]
                    ],
                    'complaints_feedback' => [
                        'title' => 'Complaints and Feedback',
                        'items' => [
                            ['label' => 'Complaints Received', 'value' => $complaintsReceived],
                            ['label' => 'Issues Resolved', 'value' => $issuesResolved],
                            ['label' => 'Guest Feedback', 'value' => $guestFeedback],
                        ]
                    ],
                    'reservation_overview' => [
                        'title' => 'Reservation Overview',
                        'items' => [
                            ['label' => 'Total Reservations', 'value' => $totalReservations],
                            ['label' => 'Room Type Availability', 'value' => $roomTypeAvailability, 'is_availability' => true],
                        ]
                    ]
                ],
                'room_types_breakdown' => $roomTypeDetails,
                'signoff' => [
                    'prepared_by' => $frontDesk,
                    'audited_by' => $shiftLead,
                    'approved_by' => $owner
                ]
            ],
            'operational_snapshot' => [
                'occupancy_percentage' => $occupancyRate,
                'occupied_rooms' => $roomsOccupied,
                'rentable_rooms' => max(1, $totalRooms - $maintenanceRooms),
                'total_inventory' => $totalRooms,
                'maintenance_rooms' => $maintenanceRooms,
                'vacant_clean_rooms' => $roomsAvailable,
                'expected_check_ins' => $totalCheckIns,
                'expected_check_outs' => $totalCheckOuts,
                'stay_overs' => max(0, $roomsOccupied - $totalCheckIns),
                'vacant_clean' => $roomsAvailable,
                'vacant_dirty' => $maintenanceRooms,
                'room_sales' => $roomRevenue,
                'average_daily_rate' => $totalRoomReservations > 0 ? round($roomRevenue / $totalRoomReservations, 2) : 0,
                'revpar' => $totalRooms > 0 ? round($roomRevenue / $totalRooms, 2) : 0,
                'total_pending_balance' => 0.00,
                'total_deposits_collected' => $totalRevenue,
                'gross_folio_value' => $totalRevenue,
                'net_room_revenue' => $roomRevenue,
                'taxes_and_fees' => 0.00,
                'available_room_nights' => $totalRooms,
                'days_in_period' => 1
            ],
            'summary' => [
                'total_bookings' => $totalReservations,
                'confirmed_bookings' => $totalRoomReservations,
                'checked_in_bookings' => $totalCheckIns,
                'checked_out_bookings' => $totalCheckOuts,
                'no_show_bookings' => $totalNoShows,
                'cancelled_bookings' => $totalCancellations,
                'confirmed_revenue' => $roomRevenue,
                'total_nights_sold' => $totalRoomReservations,
                'total_occupants' => $roomsOccupied * 2
            ],
            'rows' => $rows
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Front desk daily report failed: ' . $e->getMessage()]);
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
            WHERE COALESCE(b.check_in_date, b.check_in) = :target_date AND b.status != 'Cancelled'
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
            WHERE COALESCE(b.check_out_date, b.check_out) = :target_date AND b.status != 'Cancelled'
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
            WHERE COALESCE(b.check_in_date, b.check_in) < :target_date AND COALESCE(b.check_out_date, b.check_out) > :target_date AND b.status = 'Confirmed'
            ORDER BY r.name ASC, b.id ASC
        ");
        $stmtInHouse->execute([':target_date' => $targetDate]);
        $inHouse = $stmtInHouse->fetchAll(PDO::FETCH_ASSOC);

        // Total active rooms count
        $stmtTotalRooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status != 'Maintenance'");
        $activeRoomsCount = $stmtTotalRooms ? intval($stmtTotalRooms->fetchColumn()) : 1;

        $occupiedRoomIds = [];
        foreach ($inHouse as $ih) {
            if (!empty($ih['room_id'])) $occupiedRoomIds[$ih['room_id']] = true;
        }
        foreach ($arrivals as $ar) {
            if (!empty($ar['room_id'])) $occupiedRoomIds[$ar['room_id']] = true;
        }
        $totalOccupiedToday = count($occupiedRoomIds);
        $estimatedOccupancyRate = $activeRoomsCount > 0 ? min(100, round(($totalOccupiedToday / $activeRoomsCount) * 100, 1)) : 0;

        echo json_encode([
            'success' => true,
            'report_type' => 'daily_manifest',
            'report_title' => 'Front Desk Daily Operations Manifest',
            'report_ref' => 'MNF-' . date('Ymd', strtotime($targetDate)) . '-' . strtoupper(substr(uniqid(), -4)),
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_at_formatted' => date('F d, Y - h:i A') . ' PHT (UTC+8)',
            'hotel_info' => getHotelInformation(),
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
                  (COALESCE(b.check_in_date, b.check_in) BETWEEN :start_date AND :end_date) OR
                  (COALESCE(b.check_out_date, b.check_out) BETWEEN :start_date AND :end_date) OR
                  (COALESCE(b.check_in_date, b.check_in) <= :start_date AND COALESCE(b.check_out_date, b.check_out) >= :end_date)
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
                $revenue += floatval($rb['total_amount'] > 0 ? $rb['total_amount'] : ($rb['total_price'] ?? 0));
                
                $chkIn = strtotime(date('Y-m-d', strtotime($rb['check_in_date'] ?: $rb['check_in'])));
                $chkOut = strtotime(date('Y-m-d', strtotime($rb['check_out_date'] ?: $rb['check_out'])));
                $periodStart = strtotime($startDate);
                $periodEnd = strtotime($endDate . ' +1 day');

                $overlapStart = max($chkIn, $periodStart);
                $overlapEnd = min($chkOut, $periodEnd);

                if ($overlapEnd > $overlapStart) {
                    $nightsSold += round(($overlapEnd - $overlapStart) / 86400);
                } elseif ($chkIn == $chkOut && $chkIn >= $periodStart && $chkIn < $periodEnd) {
                    $nightsSold += 1;
                }
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
            'report_title' => 'Room Occupancy & Utilization Performance Report',
            'report_ref' => 'RPR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_at_formatted' => date('F d, Y - h:i A') . ' PHT (UTC+8)',
            'hotel_info' => getHotelInformation(),
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
                'occupancy_percentage' => (count($rooms) * $diff) > 0 ? min(100, round(($totalHotelNights / (count($rooms) * $diff)) * 100, 1)) : 0,
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
            'report_title' => 'Guest Satisfaction & Reviews Audit Report',
            'report_ref' => 'QST-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_at_formatted' => date('F d, Y - h:i A') . ' PHT (UTC+8)',
            'hotel_info' => getHotelInformation(),
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
    $newArrivalTime = $data['new_arrival_time'] ?? null;
    $contactPhone = $data['contact_phone'] ?? null;

    if (!$userId || !$message) {
        echo json_encode(['success' => false, 'message' => 'User ID and message are required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO reports (user_id, booking_id, subject, message, type, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->execute([$userId, $bookingId, $subject, $message, $type]);
        $reportId = $pdo->lastInsertId();

        // If a new arrival time is provided for late arrival notice, update bookings record
        if ($bookingId && $newArrivalTime) {
            $upd = $pdo->prepare("UPDATE bookings SET expected_arrival_time = ? WHERE id = ?");
            $upd->execute([$newArrivalTime, $bookingId]);
        }
        if ($bookingId && $contactPhone) {
            $updPhone = $pdo->prepare("UPDATE bookings SET guest_phone = ? WHERE id = ? AND (guest_phone IS NULL OR guest_phone = '')");
            $updPhone->execute([$contactPhone, $bookingId]);
        }

        echo json_encode([
            'success' => true,
            'message' => ($type === 'late_arrival') 
                ? 'Late arrival notice received! Front desk has been notified and your arrival window has been updated.' 
                : 'Report submitted successfully',
            'id' => $reportId,
            'updated_arrival_time' => $newArrivalTime
        ]);
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

