<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["admin_name"])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

include("../../connection.php");
$connection->set_charset("utf8mb4");

function calculateHealthScore($company_id, $connection) {
    
    $dataCheckQuery = "
        SELECT 
            (SELECT COUNT(*) FROM invoices WHERE company_id = ?) as invoice_count,
            (SELECT COUNT(*) FROM expenses WHERE company_id = ?) as expense_count,
            (SELECT COUNT(*) FROM compliance_items WHERE company_id = ?) as compliance_count
    ";
    $stmt = $connection->prepare($dataCheckQuery);
    $stmt->bind_param("iii", $company_id, $company_id, $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dataCounts = $result->fetch_assoc();
    $stmt->close();
    
    $hasInvoices = $dataCounts['invoice_count'] > 0;
    $hasExpenses = $dataCounts['expense_count'] > 0;
    $hasCompliance = $dataCounts['compliance_count'] > 0;
    
    // If user has no data at all, return "Not enough data"
    if (!$hasInvoices && !$hasExpenses && !$hasCompliance) {
        return [
            'score' => null,
            'color' => 'gray',
            'status' => 'No Data',
            'message' => 'Upload your financial data to see your health score.',
            'insufficient_data' => true,
            'breakdown' => [
                'cash_ratio' => ['points' => 0, 'max' => 25, 'value' => 'Upload invoices'],
                'overdue_invoices' => ['points' => 0, 'max' => 25, 'value' => 'Upload invoices'],
                'expense_growth' => ['points' => 0, 'max' => 20, 'value' => 'Upload expenses'],
                'runway' => ['points' => 0, 'max' => 25, 'value' => 'Upload data'],
                'compliance' => ['points' => 0, 'max' => 20, 'value' => 'Upload compliance']
            ]
        ];
    }
    
    if (!$hasInvoices) {
        $cashRatioPoints = 0;
        $cashDisplay = 'No invoices uploaded';
        $cashPercent = 0;
    } else {
        $cashQuery = "
            SELECT 
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_invoices
            FROM invoices 
            WHERE company_id = ?
        ";
        $stmt = $connection->prepare($cashQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $totalInvoices = $row['total_invoices'] ?: 1;
        $paidInvoices = $row['paid_invoices'] ?: 0;
        $paidRatio = $paidInvoices / $totalInvoices;
        $cashRatioPoints = round($paidRatio * 25);
        $cashPercent = round($paidRatio * 100, 1);
        $cashDisplay = $cashPercent . '% paid';
        $stmt->close();
    }
    
    if (!$hasInvoices) {
        $overduePoints = 0;
        $overdueDisplay = 'No invoices uploaded';
        $overduePercent = 0;
    } else {
        $overdueQuery = "
            SELECT 
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_invoices
            FROM invoices 
            WHERE company_id = ?
        ";
        $stmt = $connection->prepare($overdueQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $totalInv = $row['total_invoices'] ?: 1;
        $overdueInv = $row['overdue_invoices'] ?: 0;
        $overdueRatio = $overdueInv / $totalInv;
        $overduePoints = round((1 - $overdueRatio) * 25);
        $overduePercent = round($overdueRatio * 100, 1);
        $overdueDisplay = $overdueInv . ' overdue (' . $overduePercent . '%)';
        $stmt->close();
    }
    
    if (!$hasExpenses) {
        $expensePoints = 0;
        $growthDisplay = 'No expenses uploaded';
    } else {
        // Get current month expenses
        $currentMonthStart = date('Y-m-01');
        $currentMonthQuery = "
            SELECT COALESCE(SUM(amount_cents), 0) as total
            FROM expenses 
            WHERE company_id = ? 
            AND invoice_date >= ?
        ";
        $stmt = $connection->prepare($currentMonthQuery);
        $stmt->bind_param("is", $company_id, $currentMonthStart);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentRow = $result->fetch_assoc();
        $currentMonth = ($currentRow['total'] ?? 0) / 100;
        $stmt->close();
        
        // Get last month expenses
        $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));
        $lastMonthQuery = "
            SELECT COALESCE(SUM(amount_cents), 0) as total
            FROM expenses 
            WHERE company_id = ? 
            AND invoice_date >= ? 
            AND invoice_date <= ?
        ";
        $stmt = $connection->prepare($lastMonthQuery);
        $stmt->bind_param("iss", $company_id, $lastMonthStart, $lastMonthEnd);
        $stmt->execute();
        $result = $stmt->get_result();
        $lastRow = $result->fetch_assoc();
        $lastMonth = ($lastRow['total'] ?? 0) / 100;
        $stmt->close();
        

        
        // No expense data at all in current or last month
        if ($currentMonth == 0 && $lastMonth == 0) {
            // Check if there are ANY expenses in the system
            $anyExpenseQuery = "SELECT COALESCE(SUM(amount_cents), 0) as total FROM expenses WHERE company_id = ?";
            $stmt = $connection->prepare($anyExpenseQuery);
            $stmt->bind_param("i", $company_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $anyExpense = $result->fetch_assoc();
            $totalExpenses = ($anyExpense['total'] ?? 0) / 100;
            $stmt->close();
            
            if ($totalExpenses > 0) {
                $expensePoints = 15;
                $growthDisplay = 'Expenses exist but not in current/last month';
                error_log("Total expenses found: $totalExpenses but not in date range");
            } else {
                $expensePoints = 0;
                $growthDisplay = 'No expense data found';
                error_log("No expenses found at all for company $company_id");
            }
        }
        // First month with expenses (no previous month to compare)
        elseif ($lastMonth == 0 && $currentMonth > 0) {
            $expensePoints = 20;
            $growthDisplay = 'First month recorded';
            error_log("First month recorded - current: $currentMonth");
        }
        // Normal case - compare current vs last month
        elseif ($lastMonth > 0) {
            $growthRate = (($currentMonth - $lastMonth) / $lastMonth) * 100;
            error_log("Growth Rate Calculated: current=$currentMonth, last=$lastMonth, rate=$growthRate%");
            
            // Cap the display value
            if ($growthRate > 100) {
                $growthDisplay = '>100% increase';
            } elseif ($growthRate < -100) {
                $growthDisplay = '>100% decrease';
            } else {
                $growthDisplay = ($growthRate >= 0 ? '+' : '') . round($growthRate, 1) . '% change';
            }
            
            // Score based on growth rate
            if ($growthRate < 5 && $growthRate > -5) {
                $expensePoints = 20;
                error_log("Growth rate $growthRate% - Awarded 20 points");
            } elseif ($growthRate <= 20 && $growthRate >= -20) {
                $expensePoints = 10;
                error_log("Growth rate $growthRate% - Awarded 10 points");
            } else {
                $expensePoints = 0;
                error_log("Growth rate $growthRate% - Awarded 0 points");
            }
        } else {
            $expensePoints = 0;
            $growthDisplay = 'Unable to calculate expense growth';
            error_log("Unable to calculate - current=$currentMonth, last=$lastMonth");
        }
    }
    
    if (!$hasExpenses || !$hasInvoices) {
        $runwayPoints = 0;
        $runwayDisplay = 'Need both expenses and invoices';
        $runwayMonths = 0;
    } else {
        // Get average monthly expense from last 3 months
        $avgExpenseQuery = "
            SELECT COALESCE(AVG(amount_cents), 0) as avg_monthly
            FROM expenses 
            WHERE company_id = ? 
            AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        ";
        $stmt = $connection->prepare($avgExpenseQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $avgMonthlyExpense = ($row['avg_monthly'] ?? 0) / 100;
        $stmt->close();
        
        // Get total overdue amount
        $cashAtRiskQuery = "
            SELECT COALESCE(SUM(amount_cents), 0) as total_overdue
            FROM invoices 
            WHERE company_id = ? AND status = 'overdue'
        ";
        $stmt = $connection->prepare($cashAtRiskQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $cashAtRisk = ($row['total_overdue'] ?? 0) / 100;
        $stmt->close();
        
        error_log("Runway Debug - Avg Monthly Expense: $avgMonthlyExpense, Cash at Risk: $cashAtRisk");
        
        if ($avgMonthlyExpense <= 0) {
            $runwayPoints = 0;
            $runwayDisplay = 'No expense data for runway calculation';
            $runwayMonths = 0;
            error_log("Runway: No expense data - avgMonthlyExpense=$avgMonthlyExpense");
        } elseif ($cashAtRisk <= 0) {
            $runwayPoints = 0;
            $runwayDisplay = 'No overdue invoices to calculate runway';
            $runwayMonths = 0;
            error_log("Runway: No overdue invoices - cashAtRisk=$cashAtRisk");
        } else {
            $runwayMonths = $cashAtRisk / $avgMonthlyExpense;
            $runwayMonthsFormatted = round($runwayMonths, 1);
            error_log("Runway: $runwayMonthsFormatted months calculated");
            
            if ($runwayMonths >= 6) {
                $runwayPoints = 25;
                $runwayDisplay = $runwayMonthsFormatted . ' months (Healthy)';
            } elseif ($runwayMonths >= 3) {
                $runwayPoints = 15;
                $runwayDisplay = $runwayMonthsFormatted . ' months (Adequate)';
            } elseif ($runwayMonths >= 1) {
                $runwayPoints = 8;
                $runwayDisplay = $runwayMonthsFormatted . ' months (Warning)';
            } else {
                $runwayPoints = 0;
                $runwayDisplay = $runwayMonthsFormatted . ' months (Critical)';
            }
            error_log("Runway Points awarded: $runwayPoints");
        }
    }
    
    // ========== 5. COMPLIANCE (20 points max) ==========
    if (!$hasCompliance) {
        $compliancePoints = 0;
        $complianceDisplay = 'No compliance items uploaded';
    } else {
        $complianceQuery = "
            SELECT 
                COUNT(*) as total_items,
                SUM(CASE WHEN due_date < CURDATE() AND status != 'completed' THEN 1 ELSE 0 END) as overdue_items,
                SUM(CASE WHEN due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND due_date >= CURDATE() AND status != 'completed' THEN 1 ELSE 0 END) as upcoming_items
            FROM compliance_items 
            WHERE company_id = ?
        ";
        $stmt = $connection->prepare($complianceQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $totalCompliance = $row['total_items'] ?? 0;
        $overdueCompliance = $row['overdue_items'] ?? 0;
        $upcomingCompliance = $row['upcoming_items'] ?? 0;
        
        error_log("Compliance Debug - Total: $totalCompliance, Overdue: $overdueCompliance, Upcoming: $upcomingCompliance");
        
        if ($totalCompliance == 0) {
            $compliancePoints = 0;
            $complianceDisplay = 'No compliance items';
        } else {
            if ($overdueCompliance == 0) {
                $compliancePoints = 20;
                $complianceDisplay = 'All up to date';
            } elseif ($overdueCompliance == 1) {
                $compliancePoints = 10;
                $complianceDisplay = $overdueCompliance . ' overdue item';
            } else {
                $compliancePoints = 0;
                $complianceDisplay = $overdueCompliance . ' overdue items';
            }
            
            if ($upcomingCompliance > 0) {
                $complianceDisplay .= ', ' . $upcomingCompliance . ' upcoming';
                if ($compliancePoints > 0) {
                    $compliancePoints = max(0, $compliancePoints - 2);
                }
            }
            error_log("Compliance Points awarded: $compliancePoints");
        }
    }
    
    // ========== CALCULATE TOTAL HEALTH SCORE ==========
    $totalScore = $cashRatioPoints + $overduePoints + $expensePoints + $runwayPoints + $compliancePoints;
    $totalScore = max(0, min(100, $totalScore));
    
    error_log("=== FINAL SCORE BREAKDOWN ===");
    error_log("Cash Ratio: $cashRatioPoints/25");
    error_log("Overdue Invoices: $overduePoints/25");
    error_log("Expense Growth: $expensePoints/20");
    error_log("Runway: $runwayPoints/25");
    error_log("Compliance: $compliancePoints/20");
    error_log("TOTAL SCORE: $totalScore/100");
    
    // Determine color and status
    if ($totalScore >= 70) {
        $color = 'blue';
        $status = 'Excellent';
        $message = 'Your business is in great shape. Keep up the good work!';
    } elseif ($totalScore >= 50) {
        $color = 'orange';
        $status = 'Fair';
        $message = 'Your business has some areas that need attention.';
    } elseif ($totalScore > 0) {
        $color = 'red';
        $status = 'Critical';
        $message = 'Your business needs immediate attention. Review the alerts above.';
    } else {
        $color = 'gray';
        $status = 'Setup Required';
        $message = 'Complete your data upload to see your health score.';
    }
    
    return [
        'score' => $totalScore > 0 ? $totalScore : null,
        'color' => $color,
        'status' => $status,
        'message' => $message,
        'breakdown' => [
            'cash_ratio' => [
                'points' => $cashRatioPoints,
                'max' => 25,
                'value' => $cashDisplay
            ],
            'overdue_invoices' => [
                'points' => $overduePoints,
                'max' => 25,
                'value' => $overdueDisplay
            ],
            'expense_growth' => [
                'points' => $expensePoints,
                'max' => 20,
                'value' => $growthDisplay
            ],
            'runway' => [
                'points' => $runwayPoints,
                'max' => 25,
                'value' => $runwayDisplay
            ],
            'compliance' => [
                'points' => $compliancePoints,
                'max' => 20,
                'value' => $complianceDisplay
            ]
        ]
    ];
}

$company_id = $_SESSION['company_id'];
$healthScore = calculateHealthScore($company_id, $connection);

echo json_encode([
    'success' => true,
    'health_score' => $healthScore,
    'calculated_at' => date('Y-m-d H:i:s')
]);

$connection->close();
?>