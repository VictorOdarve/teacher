<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : '';
$section = isset($_GET['section']) ? $_GET['section'] : '';
$subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$grade_level = isset($_GET['grade_level']) ? $_GET['grade_level'] : '';

header('Content-Type: application/json');

function buildClassFilter($db, $section, $subject, $grade_level) {
    $conditions = [];
    $params = [];
    if ($section) { $conditions[] = "c.section = :section"; $params[':section'] = $section; }
    if ($subject) { $conditions[] = "c.subject = :subject"; $params[':subject'] = $subject; }
    if ($grade_level) { $conditions[] = "c.grade_level = :grade_level"; $params[':grade_level'] = $grade_level; }
    return [$conditions, $params];
}

switch ($action) {

    case 'honor_prediction':
        // Logistic regression: predict if student will be an honor student
        // Features: [1 (bias), final_grade/100]
        // Label: 1 = honor (final_grade >= 90), 0 = not
        [$conditions, $params] = buildClassFilter($db, $section, $subject, $grade_level);
        $where = $conditions ? "AND " . implode(" AND ", $conditions) : "";
        $sql = "SELECT cs.id, CONCAT(cs.last_name, ', ', cs.first_name) AS name,
                    cs.student_id AS student_no, c.section, c.subject,
                    fg.rounded_final_grade AS final_grade, fg.remarks
                FROM class_students cs
                JOIN classes c ON cs.class_id = c.id
                LEFT JOIN final_grades fg ON cs.id = fg.student_id
                WHERE 1=1 $where
                ORDER BY fg.rounded_final_grade DESC";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($students)) { echo json_encode([]); break; }

        // Build labeled set: honor = grade >= 90
        $labeled_h = [];
        foreach ($students as $s) {
            $g = $s['final_grade'] !== null ? floatval($s['final_grade']) / 100.0 : 0.0;
            $labeled_h[] = ['feat' => [1.0, $g], 'label' => ($s['final_grade'] !== null && $s['final_grade'] >= 90 ? 1 : 0), 'row' => $s];
        }

        // Logistic regression
        $sigmoid_h = fn($z) => 1.0 / (1.0 + exp(max(-500, min(500, -$z))));
        $wh = [0.0, 0.0];
        if (count($labeled_h) >= 2) {
            $lr_h = 0.1; $ep_h = 800;
            for ($e = 0; $e < $ep_h; $e++) {
                $grad = [0.0, 0.0];
                foreach ($labeled_h as $d) {
                    $z   = $wh[0]*$d['feat'][0] + $wh[1]*$d['feat'][1];
                    $err = $sigmoid_h($z) - $d['label'];
                    for ($j = 0; $j < 2; $j++) $grad[$j] += $err * $d['feat'][$j];
                }
                $n = count($labeled_h);
                for ($j = 0; $j < 2; $j++) $wh[$j] -= $lr_h * $grad[$j] / $n;
            }
        } else {
            $wh = [-9.0, 10.0]; // fallback: grade >= 90 → high prob
        }

        $result_h = [];
        foreach ($labeled_h as $d) {
            $z    = $wh[0]*$d['feat'][0] + $wh[1]*$d['feat'][1];
            $prob = round($sigmoid_h($z) * 100, 1);
            $predicted = $prob >= 50 ? 'Yes' : 'No';
            $grade = $d['row']['final_grade'] !== null ? intval($d['row']['final_grade']) : -1;
            $tier = '';
            if ($grade >= 98)      $tier = 'With Highest Honors';
            elseif ($grade >= 95)  $tier = 'With High Honors';
            elseif ($grade >= 90)  $tier = 'With Honors';
            $result_h[] = array_merge($d['row'], [
                'predicted'   => $predicted,
                'probability' => $prob,
                'honor_tier'  => $tier,
            ]);
        }
        // Sort by grade desc (honors first), all students shown
        echo json_encode($result_h);
        break;

    case 'attendance_risk':
        // Students with attendance rate < 75%
        [$conditions, $params] = buildClassFilter($db, $section, $subject, $grade_level);
        $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT cs.id, CONCAT(cs.last_name, ', ', cs.first_name) as name,
                    cs.student_id as student_no, c.section, c.subject,
                    COUNT(a.id) as total_days,
                    SUM(CASE WHEN a.status = 'present' OR a.status = 'late' THEN 1 ELSE 0 END) as attended,
                    ROUND(SUM(CASE WHEN a.status = 'present' OR a.status = 'late' THEN 1 ELSE 0 END) / NULLIF(COUNT(a.id),0) * 100, 1) as attendance_rate,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absences
                FROM class_students cs
                JOIN classes c ON cs.class_id = c.id
                LEFT JOIN attendance a ON cs.id = a.student_id
                $where
                GROUP BY cs.id
                HAVING total_days > 0 AND attendance_rate < 75
                ORDER BY attendance_rate ASC";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'safe_students':
        [$conditions, $params] = buildClassFilter($db, $section, $subject, $grade_level);
        $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT cs.id, CONCAT(cs.last_name, ', ', cs.first_name) as name,
                    cs.student_id as student_no, c.section, c.subject,
                    COUNT(a.id) as total_days,
                    COALESCE(SUM(CASE WHEN a.status IN ('present','late') THEN 1 ELSE 0 END), 0) as attended,
                    COALESCE(SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END), 0) as absences,
                    CASE WHEN COUNT(a.id) = 0 THEN 0
                         ELSE ROUND(SUM(CASE WHEN a.status IN ('present','late') THEN 1 ELSE 0 END) / COUNT(a.id) * 100, 1)
                    END as attendance_rate
                FROM class_students cs
                JOIN classes c ON cs.class_id = c.id
                LEFT JOIN attendance a ON cs.id = a.student_id
                $where
                GROUP BY cs.id
                ORDER BY attendance_rate DESC";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Keep only >= 75% OR no records (show as 0)
        $rows = array_values(array_filter($rows, fn($r) => $r['total_days'] == 0 || $r['attendance_rate'] >= 75));
        echo json_encode($rows);
        break;

    case 'absence_pattern':
        // Students absent 3+ times on the same day of week
        [$conditions, $params] = buildClassFilter($db, $section, $subject, $grade_level);
        $where = $conditions ? "AND " . implode(" AND ", $conditions) : "";
        $sql = "SELECT cs.id, CONCAT(cs.last_name, ', ', cs.first_name) as name,
                    cs.student_id as student_no, c.section, c.subject,
                    DAYNAME(a.date) as day_name,
                    COUNT(*) as absent_count
                FROM attendance a
                JOIN class_students cs ON a.student_id = cs.id
                JOIN classes c ON cs.class_id = c.id
                WHERE a.status = 'absent' $where
                GROUP BY cs.id, DAYNAME(a.date)
                HAVING absent_count >= 3
                ORDER BY absent_count DESC";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'final_grade_forecast':
        // Fetch all grade rows and compute quarter grades in PHP
        // grades.scores = JSON array of student scores, grades.total_score = JSON array of max totals
        // percentage = SUM(scores) / SUM(totals) * 100
        // quarter_grade = WW% * 0.3 + PT% * 0.5 + AS% * 0.2
        // needed_q4 = 300 - Q1 - Q2 - Q3
        [$conditions, $params] = buildClassFilter($db, $section, $subject, $grade_level);
        $where = $conditions ? "AND " . implode(" AND ", $conditions) : "";

        // Get all students
        $stuSql = "SELECT cs.id, CONCAT(cs.last_name, ', ', cs.first_name) AS name,
                       cs.student_id AS student_no, c.section, c.subject
                   FROM class_students cs
                   JOIN classes c ON cs.class_id = c.id
                   WHERE 1=1 $where
                   ORDER BY cs.last_name, cs.first_name";
        $stuStmt = $db->prepare($stuSql);
        foreach ($params as $k => $v) $stuStmt->bindValue($k, $v);
        $stuStmt->execute();
        $studentRows = $stuStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all grades rows for these students
        $ids = array_column($studentRows, 'id');
        if (empty($ids)) { echo json_encode([]); break; }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $gradeStmt = $db->prepare("SELECT student_id, quarter, grade_type, scores, total_score FROM grades WHERE student_id IN ($placeholders)");
        $gradeStmt->execute($ids);
        $gradeRows = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Index grades by [student_id][quarter][grade_type]
        $gradeMap = [];
        foreach ($gradeRows as $gr) {
            $gradeMap[$gr['student_id']][$gr['quarter']][$gr['grade_type']] = [
                'scores' => json_decode($gr['scores'], true) ?? [],
                'totals' => json_decode($gr['total_score'], true) ?? []
            ];
        }

        // Get finalized grades
        $fgStmt = $db->prepare("SELECT student_id, q4_grade, rounded_final_grade, remarks FROM final_grades WHERE student_id IN ($placeholders)");
        $fgStmt->execute($ids);
        $fgMap = [];
        foreach ($fgStmt->fetchAll(PDO::FETCH_ASSOC) as $fg) {
            $fgMap[$fg['student_id']] = $fg;
        }

        // Helper: compute percentage from scores/totals arrays
        function computePercent($scores, $totals) {
            $sumScores = 0; $sumTotals = 0;
            foreach ($scores as $i => $s) {
                $s = floatval($s); $t = floatval($totals[$i] ?? 0);
                if ($t > 0) { $sumScores += $s; $sumTotals += $t; }
            }
            return $sumTotals > 0 ? ($sumScores / $sumTotals) * 100 : null;
        }

        // Helper: compute quarter grade from gradeMap entry
        // WW=30%, PT=50%, AS=20% — passing is >= 75
        function computeQuarterGrade($gradeMap, $studentId, $quarter) {
            $data = $gradeMap[$studentId][$quarter] ?? [];
            $wwPct = isset($data['ww']) ? computePercent($data['ww']['scores'], $data['ww']['totals']) : null;
            $ptPct = isset($data['pt']) ? computePercent($data['pt']['scores'], $data['pt']['totals']) : null;
            $asPct = isset($data['as']) ? computePercent($data['as']['scores'], $data['as']['totals']) : null;
            if ($wwPct === null && $ptPct === null && $asPct === null) return null;
            return round(($wwPct ?? 0) * 0.3 + ($ptPct ?? 0) * 0.5 + ($asPct ?? 0) * 0.2, 2);
        }

        $result = [];
        foreach ($studentRows as $s) {
            $sid = $s['id'];
            $q1 = computeQuarterGrade($gradeMap, $sid, 1);
            $q2 = computeQuarterGrade($gradeMap, $sid, 2);
            $q3 = computeQuarterGrade($gradeMap, $sid, 3);

            // Skip students with no grades at all
            if ($q1 === null && $q2 === null && $q3 === null) continue;

            // To pass: (Q1 + Q2 + Q3 + Q4) / 4 >= 75  =>  Q4 needed = (75 * 4) - Q1 - Q2 - Q3
            $needed = round((75 * 4) - ($q1 ?? 0) - ($q2 ?? 0) - ($q3 ?? 0), 2);

            // The needed_q4 is the required Q4 quarter grade (0-100 scale).
            // Quarter grade formula: WW%*0.3 + PT%*0.5 + AS%*0.2 = needed_q4
            // To show actionable per-component targets, we assume the student
            // scores equally across all components: each must reach needed_q4.
            // This is the minimum uniform percentage needed in WW, PT, and AS.
            $fg = $fgMap[$sid] ?? null;

            $result[] = [
                'name'             => $s['name'],
                'student_no'       => $s['student_no'],
                'section'          => $s['section'],
                'subject'          => $s['subject'],
                'q1_grade'         => $q1 ?? 'N/A',
                'q2_grade'         => $q2 ?? 'N/A',
                'q3_grade'         => $q3 ?? 'N/A',
                'actual_q4'        => $fg ? $fg['q4_grade'] : null,
                'final_grade'      => $fg ? $fg['rounded_final_grade'] : null,
                'remarks'          => $fg ? $fg['remarks'] : null,
                'needed_q4'        => $needed,
                // Already Passing: needs <= 75 in Q4 (on track to pass)
                // At Risk: needs > 75 and <= 100 in Q4
                // Cannot Pass: needs > 100 in Q4 (impossible)
                'predicted_status' => $needed <= 75 ? 'PASSING' : ($needed > 100 ? 'CANNOT_PASS' : 'AT_RISK'),
            ];
        }

        // Sort by needed_q4 descending (most at risk first)
        usort($result, fn($a, $b) => $b['needed_q4'] <=> $a['needed_q4']);
        echo json_encode($result);
        break;

    case 'at_risk':
        // ── 1. Fetch all students with attendance + final grade ────────────────
        [$conditions, $params] = buildClassFilter($db, $section, $subject, $grade_level);
        $where = $conditions ? "AND " . implode(" AND ", $conditions) : "";
        $sql = "SELECT cs.id, CONCAT(cs.last_name, ', ', cs.first_name) AS name,
                    cs.student_id AS student_no, c.section, c.subject,
                    ROUND(COALESCE(SUM(CASE WHEN a.status IN ('present','late') THEN 1 ELSE 0 END),0)
                          / NULLIF(COUNT(a.id),0) * 100, 1) AS attendance_rate,
                    fg.rounded_final_grade, fg.remarks
                FROM class_students cs
                JOIN classes c ON cs.class_id = c.id
                LEFT JOIN attendance a ON cs.id = a.student_id
                LEFT JOIN final_grades fg ON cs.id = fg.student_id
                WHERE 1=1 $where
                GROUP BY cs.id";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $allStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── 2. Fetch quarter grades ────────────────────────────────────────────
        $ids = array_column($allStudents, 'id');
        if (empty($ids)) { echo json_encode([]); break; }
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $gStmt = $db->prepare("SELECT student_id, quarter, grade_type, scores, total_score FROM grades WHERE student_id IN ($ph)");
        $gStmt->execute($ids);
        $gMap = [];
        foreach ($gStmt->fetchAll(PDO::FETCH_ASSOC) as $gr) {
            $gMap[$gr['student_id']][$gr['quarter']][$gr['grade_type']] = [
                'scores' => json_decode($gr['scores'], true) ?? [],
                'totals' => json_decode($gr['total_score'], true) ?? []
            ];
        }

        // ── 3. Compute avg quarter grade for a student ────────────────────────
        $computeAvgQ = function($sid) use ($gMap) {
            $qs = [];
            for ($q = 1; $q <= 3; $q++) {
                $data = $gMap[$sid][$q] ?? [];
                $parts = [];
                foreach (['ww' => 0.3, 'pt' => 0.5, 'as' => 0.2] as $type => $weight) {
                    if (!isset($data[$type])) continue;
                    $s = array_sum(array_map('floatval', $data[$type]['scores']));
                    $t = array_sum(array_map('floatval', $data[$type]['totals']));
                    if ($t > 0) $parts[$type] = ($s / $t) * 100 * $weight;
                }
                if ($parts) $qs[] = array_sum($parts);
            }
            return count($qs) ? array_sum($qs) / count($qs) : null;
        };

        // ── 4. Build labeled / unlabeled sets ─────────────────────────────────
        // Features: [1 (bias), attendance_rate/100, avg_quarter_grade/100]
        // Label: 1 = at-risk (failed), 0 = safe
        $labeled = []; $unlabeled = [];
        foreach ($allStudents as $s) {
            $sid  = $s['id'];
            $att  = $s['attendance_rate'] !== null ? floatval($s['attendance_rate']) / 100.0 : 0.5;
            $avgQ = $computeAvgQ($sid);
            $avgQn = $avgQ !== null ? $avgQ / 100.0 : 0.5;
            $feat = [1.0, $att, $avgQn];
            if ($s['remarks'] !== null) {
                $label = ($s['remarks'] === 'FAILED') ? 1 : 0;
                $labeled[] = ['feat' => $feat, 'label' => $label, 'row' => $s, 'avgQ' => $avgQ];
            } else {
                $unlabeled[] = ['feat' => $feat, 'row' => $s, 'avgQ' => $avgQ];
            }
        }

        // ── 5. Logistic Regression via gradient descent ───────────────────────
        $sigmoid = fn($z) => 1.0 / (1.0 + exp(max(-500, min(500, -$z))));
        $w = [0.0, 0.0, 0.0];
        if (count($labeled) >= 2) {
            $lr_rate = 0.1; $epochs = 600;
            for ($e = 0; $e < $epochs; $e++) {
                $grad = [0.0, 0.0, 0.0];
                foreach ($labeled as $d) {
                    $z   = $w[0]*$d['feat'][0] + $w[1]*$d['feat'][1] + $w[2]*$d['feat'][2];
                    $err = $sigmoid($z) - $d['label'];
                    for ($j = 0; $j < 3; $j++) $grad[$j] += $err * $d['feat'][$j];
                }
                $n = count($labeled);
                for ($j = 0; $j < 3; $j++) $w[$j] -= $lr_rate * $grad[$j] / $n;
            }
        } else {
            // Fallback: low attendance & low grade = high risk
            $w = [2.5, -5.0, -4.0];
        }

        // ── 6. Predict, filter, sort ──────────────────────────────────────────
        $predict = function($feat) use ($w, $sigmoid) {
            $z = $w[0]*$feat[0] + $w[1]*$feat[1] + $w[2]*$feat[2];
            return round($sigmoid($z) * 100, 1);
        };

        $result = [];
        foreach (array_merge($labeled, $unlabeled) as $d) {
            $prob = $predict($d['feat']);
            if ($prob < 45) continue;
            $risk = $prob >= 70 ? 'HIGH' : 'MEDIUM';
            $result[] = array_merge($d['row'], [
                'avg_quarter_grade' => $d['avgQ'] !== null ? round($d['avgQ'], 1) : null,
                'risk_probability'  => $prob,
                'risk_level'        => $risk,
            ]);
        }
        usort($result, fn($a, $b) => $b['risk_probability'] <=> $a['risk_probability']);
        echo json_encode($result);
        break;

    case 'pass_fail_warning':
        // After Q1+Q2: flag students needing high scores in Q3+Q4 to pass
        [$conditions, $params] = buildClassFilter($db, $section, $subject, $grade_level);
        $where = $conditions ? "AND " . implode(" AND ", $conditions) : "";
        $sql = "SELECT cs.id, CONCAT(cs.last_name, ', ', cs.first_name) as name,
                    cs.student_id as student_no, c.section, c.subject,
                    fg.q1_grade, fg.q2_grade, fg.q3_grade, fg.q4_grade,
                    fg.rounded_final_grade, fg.remarks,
                    ROUND((fg.q1_grade + fg.q2_grade) / 2, 2) as avg_q1_q2,
                    ROUND(300 - fg.q1_grade - fg.q2_grade, 2) as needed_remaining
                FROM final_grades fg
                JOIN class_students cs ON fg.student_id = cs.id
                JOIN classes c ON cs.class_id = c.id
                WHERE fg.remarks = 'FAILED' $where
                ORDER BY fg.raw_final_grade ASC";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'attendance_vs_grade':
        // Correlation: attendance rate vs final grade
        [$conditions, $params] = buildClassFilter($db, $section, $subject, $grade_level);
        $where = $conditions ? "AND " . implode(" AND ", $conditions) : "";
        $sql = "SELECT cs.id, CONCAT(cs.last_name, ', ', cs.first_name) as name,
                    cs.student_id as student_no, c.section, c.subject,
                    ROUND(SUM(CASE WHEN a.status IN ('present','late') THEN 1 ELSE 0 END) / NULLIF(COUNT(a.id),0) * 100, 1) as attendance_rate,
                    fg.rounded_final_grade, fg.remarks
                FROM class_students cs
                JOIN classes c ON cs.class_id = c.id
                LEFT JOIN attendance a ON cs.id = a.student_id
                INNER JOIN final_grades fg ON cs.id = fg.student_id
                WHERE 1=1 $where
                GROUP BY cs.id
                ORDER BY attendance_rate DESC";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    default:
        echo json_encode([]);
}
?>
