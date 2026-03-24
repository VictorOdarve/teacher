<?php
require_once '../config/database.php';
require_once '../models/ClassModel.php';

$database = new Database();
$db = $database->getConnection();
$classModel = new ClassModel($db);

$classes = $classModel->getAll();
$sections = []; $subjects = []; $grade_levels = [];
while ($class = $classes->fetch(PDO::FETCH_ASSOC)) {
    if (!in_array($class['section'], $sections)) $sections[] = $class['section'];
    if (!in_array($class['subject'], $subjects)) $subjects[] = $class['subject'];
    if (!in_array($class['grade_level'], $grade_levels)) $grade_levels[] = $class['grade_level'];
}

$page_title = "Predictions";
include '../includes/header.php';
include '../includes/nav.php';
?>

<style>
.pred-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 30px 35px;
    color: #fff;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
}
.pred-hero i { font-size: 48px; opacity: 0.9; }
.pred-hero h1 { color: #fff; margin: 0 0 6px; font-size: 28px; }
.pred-hero p  { margin: 0; opacity: 0.88; font-size: 14px; }

.pred-type-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}
.pred-type-card {
    background: #fff;
    border: 2px solid #e0e0e0;
    border-radius: 14px;
    padding: 24px 18px;
    cursor: pointer;
    transition: all 0.25s ease;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.pred-type-card:hover {
    border-color: #667eea;
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(102,126,234,0.2);
}
.pred-type-card.active {
    border-color: #667eea;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 10px 24px rgba(102,126,234,0.35);
    transform: translateY(-4px);
}
.pred-type-card .pred-icon  { font-size: 36px; margin-bottom: 12px; }
.pred-type-card .pred-label { font-size: 14px; font-weight: 700; color: #333; line-height: 1.4; }
.pred-type-card .pred-sub   { font-size: 12px; color: #888; margin-top: 6px; line-height: 1.4; }
.pred-type-card.active .pred-label,
.pred-type-card.active .pred-sub { color: #fff; }

.pred-filters {
    background: #fff;
    border-radius: 12px;
    padding: 16px 22px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: flex-end;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
}
.pred-filters .fg { margin: 0; }
.pred-filters label { font-size: 11px; color: #888; margin-bottom: 4px; display: block; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.pred-filters select { padding: 9px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 13px; min-width: 140px; }
.pred-filters select:focus { border-color: #667eea; outline: none; }

.pred-banner {
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 18px;
    font-size: 14px;
    display: none;
    align-items: center;
    gap: 10px;
}
.pred-banner.show { display: flex; }
.pred-banner.warn    { background: #fff8e1; border-left: 4px solid #ffc107; color: #7a5c00; }
.pred-banner.danger  { background: #fff0f0; border-left: 4px solid #dc3545; color: #7a1c24; }
.pred-banner.info    { background: #f0f4ff; border-left: 4px solid #667eea; color: #2c3e8a; }

.pred-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.pred-stat {
    background: #fff;
    border-radius: 12px;
    padding: 18px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
}
.pred-stat .ps-num   { font-size: 34px; font-weight: bold; color: #667eea; }
.pred-stat .ps-label { font-size: 12px; color: #888; margin-top: 4px; }
.pred-stat.s-danger  .ps-num { color: #dc3545; }
.pred-stat.s-warning .ps-num { color: #e6a817; }
.pred-stat.s-success .ps-num { color: #28a745; }

.pred-result-card {
    background: #fff;
    border-radius: 12px;
    padding: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
}
.pred-result-card h3 { color: #667eea; margin-bottom: 15px; font-size: 15px; }

.pred-empty {
    text-align: center;
    padding: 50px 20px;
    color: #28a745;
}
.pred-empty i { font-size: 46px; margin-bottom: 12px; display: block; }
.pred-empty p { font-size: 15px; font-weight: 600; margin: 0; }

.safe-dashboard {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    margin-top: 20px;
    overflow: hidden;
}
.safe-dashboard-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #fff;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}
.safe-dashboard-header h3 { margin: 0; font-size: 15px; display: flex; align-items: center; gap: 8px; }
.safe-mini-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid #e8f5e9;
}
.safe-stat {
    background: #f0fdf4;
    border-radius: 10px;
    padding: 14px;
    text-align: center;
}
.safe-stat .ss-num   { font-size: 28px; font-weight: bold; color: #28a745; }
.safe-stat .ss-label { font-size: 11px; color: #666; margin-top: 3px; }
.safe-stat.s-perfect .ss-num { color: #20c997; }
.safe-dashboard-body { padding: 16px 20px; }
.safe-dashboard-body table { margin: 0; }
.r-perfect { color: #20c997; font-weight: 700; }
.r-safe    { color: #28a745; font-weight: 700; }

.pred-placeholder {
    text-align: center;
    padding: 60px 20px;
    color: #bbb;
}
.pred-placeholder i { font-size: 54px; margin-bottom: 14px; display: block; }
.pred-placeholder p { font-size: 15px; margin: 0; }

.pred-loading {
    text-align: center;
    padding: 40px;
    color: #667eea;
}
.pred-loading i { font-size: 30px; animation: spin 0.9s linear infinite; }
.pred-loading p { margin-top: 10px; font-size: 14px; }
@keyframes spin { to { transform: rotate(360deg); } }

.table-search {
    width: 100%;
    max-width: 280px;
    padding: 8px 12px 8px 34px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    background: #f8f9fa url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23aaa' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") no-repeat 10px center;
}
.table-search:focus { border-color: #667eea; }
.table-search-wrap { margin-bottom: 12px; }

.honor-filter-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
.honor-filter-bar button {
    padding: 6px 14px;
    border: 2px solid #e0e0e0;
    border-radius: 20px;
    background: #fff;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    color: #555;
}
.honor-filter-bar button:hover { border-color: #667eea; color: #667eea; }
.honor-filter-bar button.active { background: #667eea; border-color: #667eea; color: #fff; }

/* honor badges */
.b-highest { background: #6f42c1; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700; }
.b-high-h  { background: #0d6efd; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700; }
.b-honors  { background: #198754; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700; }
.b-yes     { color:#198754; font-weight:700; }
.b-no      { color:#dc3545; font-weight:700; }
/* table badges */
.b-high    { background:#dc3545; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700; }
.b-medium  { background:#ffc107; color:#333; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700; }
.b-passed  { color:#28a745; font-weight:700; }
.b-failed  { color:#dc3545; font-weight:700; }
.r-good    { color:#28a745; font-weight:700; }
.r-warn    { color:#e6a817; font-weight:700; }
.r-bad     { color:#dc3545; font-weight:700; }
</style>

<!-- Hero -->
<div class="pred-hero">
    <i class="fa-solid fa-brain"></i>
    <div>
        <h1>Predictions</h1>
        <p>Identify at-risk students and forecast academic outcomes before it's too late.</p>
    </div>
</div>

<!-- 3 Prediction Type Cards -->
<div class="pred-type-grid">
    <div class="pred-type-card" data-type="attendance_risk" onclick="selectType(this)">
        <div class="pred-icon">⚠️</div>
        <div class="pred-label">Attendance Risk Alert</div>
        <div class="pred-sub">Students below 75% attendance rate</div>
    </div>
    <div class="pred-type-card" data-type="final_grade_forecast" onclick="selectType(this)">
        <div class="pred-icon">📊</div>
        <div class="pred-label">Final Grade Forecast</div>
        <div class="pred-sub">Grade breakdown & pass/fail outlook</div>
    </div>
    <div class="pred-type-card" data-type="at_risk" onclick="selectType(this)">
        <div class="pred-icon">🚨</div>
        <div class="pred-label">At-Risk Dashboard</div>
        <div class="pred-sub">Combined low attendance & low grades</div>
    </div>
    <div class="pred-type-card" data-type="honor_prediction" onclick="selectType(this)">
        <div class="pred-icon">🏅</div>
        <div class="pred-label">Possible Honor Students</div>
        <div class="pred-sub">Logistic regression honor prediction</div>
    </div>
</div>

<!-- Filters -->
<div class="pred-filters">
    <div class="fg">
        <label>Section</label>
        <select id="pred-section" onchange="loadPrediction()">
            <option value="">All Sections</option>
            <?php foreach ($sections as $sec): ?>
                <option value="<?php echo htmlspecialchars($sec, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($sec, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="fg">
        <label>Subject</label>
        <select id="pred-subject" onchange="loadPrediction()">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $sub): ?>
                <option value="<?php echo htmlspecialchars($sub, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($sub, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="fg">
        <label>Grade Level</label>
        <select id="pred-grade-level" onchange="loadPrediction()">
            <option value="">All Grades</option>
            <?php foreach ($grade_levels as $gl): ?>
                <option value="<?php echo htmlspecialchars($gl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($gl, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Description Banner -->
<div id="pred-banner" class="pred-banner">
    <i id="pred-banner-icon" class="fa-solid fa-circle-info"></i>
    <span id="pred-banner-text"></span>
</div>

<!-- Summary Stats -->
<div id="pred-stats" class="pred-stats" style="display:none;"></div>

<!-- Results -->
<div id="pred-results">
    <div class="pred-result-card">
        <div class="pred-placeholder">
            <i class="fa-solid fa-brain"></i>
            <p>Select a prediction type above to get started.</p>
        </div>
    </div>
</div>

<!-- Safe Students Mini Dashboard (attendance_risk only) -->
<div id="safe-dashboard-wrap" style="display:none;"></div>

<script>
function applyTableFilter(tbodyId, search, filterVal) {
    document.querySelectorAll(`#${tbodyId} tr`).forEach(tr => {
        const nameMatch   = !search    || (tr.cells[0] && tr.cells[0].textContent.toLowerCase().includes(search));
        const filterMatch = filterVal === 'all' || (tr.dataset.filter === filterVal);
        tr.style.display = nameMatch && filterMatch ? '' : 'none';
    });
}

function setFilter(btn, val) {
    window._tableFilter = val;
    document.querySelectorAll('.honor-filter-bar button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const search = document.getElementById('flagged-search') ? document.getElementById('flagged-search').value.toLowerCase() : '';
    applyTableFilter('flagged-tbody', search, val);
}

function filterTable(inputId, tbodyId) {
    const q = document.getElementById(inputId).value.toLowerCase();
    applyTableFilter(tbodyId, q, window._tableFilter || 'all');
}

const filterBars = {
    honor_prediction: [
        { l: 'All',                  v: 'all' },
        { l: '🟣 With Highest Honors', v: 'With Highest Honors' },
        { l: '🔵 With High Honors',    v: 'With High Honors' },
        { l: '🟢 With Honors',         v: 'With Honors' },
        { l: 'Not Honor',             v: '' }
    ],
    attendance_risk: [
        { l: 'All',              v: 'all' },
        { l: '🔴 Critical (<60%)', v: 'critical' },
        { l: '🟡 Warning (60–74%)', v: 'warning' }
    ],
    final_grade_forecast: [
        { l: 'All',              v: 'all' },
        { l: '🟢 On Track',       v: 'PASSING' },
        { l: '🟡 Close to Pass',  v: 'AT_RISK' },
        { l: '🔴 Cannot Pass',    v: 'CANNOT_PASS' }
    ],
    at_risk: [
        { l: 'All',          v: 'all' },
        { l: '🔴 High Risk',  v: 'HIGH' },
        { l: '🟡 Medium Risk', v: 'MEDIUM' }
    ]
};

function getRowFilterVal(type, row) {
    if (type === 'honor_prediction')    return row.honor_tier || '';
    if (type === 'attendance_risk')     return parseFloat(row.attendance_rate) < 60 ? 'critical' : 'warning';
    if (type === 'final_grade_forecast') return row.predicted_status || 'all';
    if (type === 'at_risk')             return row.risk_level || 'all';
    return 'all';
}

let currentType = null;

const meta = {
    honor_prediction: {
        bannerClass: 'info',
        icon: 'fa-medal',
        desc: '<strong>Possible Honor Students</strong> — Uses logistic regression to predict if a student will qualify for honors based on their final grade. <strong>With Highest Honors</strong>: 98–100 &nbsp;|&nbsp; <strong>With High Honors</strong>: 95–97 &nbsp;|&nbsp; <strong>With Honors</strong>: 90–94'
    },
    attendance_risk: {
        bannerClass: 'warn',
        icon: 'fa-triangle-exclamation',
        desc: '<strong>Attendance Risk Alert</strong> — Flags students whose attendance rate has dropped below 75%. Early intervention can prevent further absences.'
    },
    final_grade_forecast: {
        bannerClass: 'info',
        icon: 'fa-chart-bar',
        desc: '<strong>Final Grade Forecast</strong> — Computes each student\'s Q1–Q3 grades and predicts the exact Q4 score they need to reach a passing final grade of 75.'
    },
    at_risk: {
        bannerClass: 'danger',
        icon: 'fa-circle-exclamation',
        desc: '<strong>At-Risk Dashboard</strong> — Combines attendance and grade data to assign a risk level (HIGH / MEDIUM) to students who need immediate attention.'
    }
};

const columns = {
    honor_prediction: [
        { k:'name',          l:'Student' },
        { k:'student_no',    l:'ID' },
        { k:'section',       l:'Section' },
        { k:'subject',       l:'Subject' },
        { k:'final_grade',   l:'Final Grade', r: v => v !== null && v !== '' ? `<strong>${v}</strong>` : '<span style="color:#aaa;">N/A</span>' },
        { k:'remarks',       l:'Status', r: v => v ? `<span class="${v === 'PASSED' ? 'b-passed' : 'b-failed'}">${v}</span>` : '<span style="color:#aaa;">No Grade</span>' },
        { k:'honor_tier',    l:'Honor Tier',  r: v => {
            if (!v) return '<span style="color:#aaa;">—</span>';
            if (v === 'With Highest Honors') return `<span class="b-highest">${v}</span>`;
            if (v === 'With High Honors')    return `<span class="b-high-h">${v}</span>`;
            return `<span class="b-honors">${v}</span>`;
        }},
        { k:'predicted',     l:'Predicted Honor?', r: v => `<span class="${v === 'Yes' ? 'b-yes' : 'b-no'}">${v}</span>` },
        { k:'probability',   l:'Probability', r: v => `<span style="color:#667eea;font-weight:700;">${v}%</span>` }
    ],
    attendance_risk: [
        { k:'name',            l:'Student' },
        { k:'student_no',      l:'ID' },
        { k:'section',         l:'Section' },
        { k:'subject',         l:'Subject' },
        { k:'total_days',      l:'Total Days' },
        { k:'attended',        l:'Attended' },
        { k:'absences',        l:'Absences' },
        { k:'attendance_rate', l:'Attendance Rate', r: v => `<span class="${v < 60 ? 'r-bad' : 'r-warn'}">${v}%</span>` }
    ],
    final_grade_forecast: [
        { k:'name',             l:'Student' },
        { k:'student_no',       l:'ID' },
        { k:'section',          l:'Section' },
        { k:'subject',          l:'Subject' },
        { k:'q1_grade',         l:'Q1 Grade',  r: v => gradeCell(v) },
        { k:'q2_grade',         l:'Q2 Grade',  r: v => gradeCell(v) },
        { k:'q3_grade',         l:'Q3 Grade',  r: v => gradeCell(v) },
        { k:'actual_q4',        l:'Actual Q4', r: v => v !== null && v !== '' ? gradeCell(v) : '<span style="color:#aaa;">Not yet</span>' },
        { k:'final_grade',      l:'Final Grade', r: v => v !== null && v !== '' ? gradeCell(v) : '<span style="color:#aaa;">—</span>' },
        { k:'remarks',          l:'Status',    r: v => v ? `<span class="${v === 'PASSED' ? 'b-passed' : 'b-failed'}">${v}</span>` : '<span style="color:#aaa;">Pending</span>' },
        { k:'needed_q4', l:'Q4 Score Needed to Pass', r: (v, row) => {
            if (row.actual_q4 !== null && row.actual_q4 !== '') {
                return '<span style="color:#aaa;">Q4 already graded</span>';
            }
            if (v === null || v === '') return 'N/A';
            const n = parseFloat(v);
            if (n <= 0) return '<span class="r-good">Already Passing</span>';
            const cls = n > 100 ? 'r-bad' : n >= 75 ? 'r-warn' : 'r-good';
            return `<span class="${cls}"><strong>${n.toFixed(2)}</strong></span>`;
        }}
    ],
    at_risk: [
        { k:'name',               l:'Student' },
        { k:'student_no',         l:'ID' },
        { k:'section',            l:'Section' },
        { k:'subject',            l:'Subject' },
        { k:'attendance_rate',    l:'Attendance %',   r: v => v !== null ? `<span class="${v < 60 ? 'r-bad' : v < 75 ? 'r-warn' : 'r-good'}">${v}%</span>` : 'N/A' },
        { k:'avg_quarter_grade',  l:'Avg Quarter Grade', r: v => v !== null && v !== '' ? `<span class="${v < 75 ? 'r-bad' : 'r-good'}">${v}</span>` : '<span style="color:#aaa;">N/A</span>' },
        { k:'rounded_final_grade',l:'Final Grade',    r: v => v !== null && v !== '' ? v : '<span style="color:#aaa;">Pending</span>' },
        { k:'remarks',            l:'Status',         r: v => v ? `<span class="${v === 'PASSED' ? 'b-passed' : 'b-failed'}">${v}</span>` : '<span style="color:#aaa;">Pending</span>' },
        { k:'risk_probability',   l:'Risk Probability', r: v => {
            const cls = v >= 70 ? 'r-bad' : 'r-warn';
            return `<span class="${cls}" title="Logistic Regression score">${v}%</span>`;
        }},
        { k:'risk_level',         l:'Risk Level',     r: v => `<span class="${v === 'HIGH' ? 'b-high' : 'b-medium'}">${v}</span>` }
    ]
};

function gradeCell(v) {
    if (v === null || v === '' || v === 'N/A') return '<span style="color:#aaa;">N/A</span>';
    const n = parseFloat(v);
    // 75 and above = passing (green), below 75 = failing (red)
    const cls = n >= 75 ? 'r-good' : 'r-bad';
    return `<span class="${cls}">${v}</span>`;
}

function buildSafeDashboard(data) {
    const safe     = data.filter(r => parseFloat(r.attendance_rate) >= 75);
    const noRecord = data.filter(r => parseInt(r.total_days) === 0);
    const perfect  = safe.filter(r => parseFloat(r.attendance_rate) >= 95).length;
    const good     = safe.filter(r => parseFloat(r.attendance_rate) >= 85 && parseFloat(r.attendance_rate) < 95).length;
    const adequate = safe.filter(r => parseFloat(r.attendance_rate) >= 75 && parseFloat(r.attendance_rate) < 85).length;
    const avg      = safe.length ? (safe.reduce((s, r) => s + parseFloat(r.attendance_rate), 0) / safe.length).toFixed(1) : '0.0';

    let statsHtml = `
        <div class="safe-mini-stats">
            <div class="safe-stat"><div class="ss-num">${safe.length}</div><div class="ss-label">Safe Students</div></div>
            <div class="safe-stat s-perfect"><div class="ss-num">${perfect}</div><div class="ss-label">Excellent (≥95%)</div></div>
            <div class="safe-stat"><div class="ss-num">${good}</div><div class="ss-label">Good (85–94%)</div></div>
            <div class="safe-stat"><div class="ss-num">${adequate}</div><div class="ss-label">Adequate (75–84%)</div></div>
            <div class="safe-stat"><div class="ss-num">${avg}%</div><div class="ss-label">Avg Attendance</div></div>
            <div class="safe-stat" style="background:#fff3cd;"><div class="ss-num" style="color:#e6a817;">${noRecord.length}</div><div class="ss-label">No Records (0%)</div></div>
        </div>`;

    let tableBody = data.length
        ? data.map(r => {
            const rate = parseFloat(r.attendance_rate);
            const cls  = rate === 0 ? 'r-bad' : rate >= 95 ? 'r-perfect' : 'r-safe';
            const display = parseInt(r.total_days) === 0 ? '<span class="r-bad">0</span>' : `<span class="${cls}">${r.attendance_rate}%</span>`;
            return `<tr><td>${r.name}</td><td>${r.student_no}</td><td>${r.section}</td><td>${r.subject}</td><td>${r.total_days}</td><td>${r.attended}</td><td>${r.absences}</td><td>${display}</td></tr>`;
          }).join('')
        : '<tr><td colspan="8" style="text-align:center;color:#aaa;">No students with 75%+ attendance yet.</td></tr>';

    let tableHtml = `<div class="safe-dashboard-body"><div class="table-search-wrap"><input type="text" class="table-search" id="safe-search" placeholder="Search student name..." oninput="filterTable('safe-search','safe-tbody')"></div><div style="overflow-x:auto;"><table><thead><tr><th>Student</th><th>ID</th><th>Section</th><th>Subject</th><th>Total Days</th><th>Attended</th><th>Absences</th><th>Attendance Rate</th></tr></thead><tbody id="safe-tbody">${tableBody}</tbody></table></div></div>`;

    return `
        <div class="safe-dashboard">
            <div class="safe-dashboard-header" onclick="toggleSafeDashboard(this)">
                <h3><i class="fa-solid fa-shield-halved"></i> Safe Students — Attendance ≥ 75% (${safe.length})</h3>
                <span><i class="fa-solid fa-chevron-down" id="safe-chevron"></i></span>
            </div>
            ${statsHtml}
            ${tableHtml}
        </div>`;
}

function toggleSafeDashboard(header) {
    const body  = header.parentElement.querySelector('.safe-dashboard-body');
    const stats = header.parentElement.querySelector('.safe-mini-stats');
    const icon  = document.getElementById('safe-chevron');
    const hidden = body.style.display === 'none';
    body.style.display  = hidden ? '' : 'none';
    stats.style.display = hidden ? '' : 'none';
    icon.className = hidden ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down';
}

function buildStats(type, data) {
    if (!data.length) return '';
    if (type === 'honor_prediction') {
        const highest = data.filter(r => r.honor_tier === 'With Highest Honors').length;
        const highH   = data.filter(r => r.honor_tier === 'With High Honors').length;
        const honors  = data.filter(r => r.honor_tier === 'With Honors').length;
        const no      = data.filter(r => r.honor_tier === '').length;
        return stat(data.length,'Total Students','') + stat(highest,'Highest Honors','s-success') + stat(highH,'High Honors','') + stat(honors,'With Honors','') + stat(no,'Not Honor','s-danger');
    }
    if (type === 'attendance_risk') {
        const critical = data.filter(r => r.attendance_rate < 60).length;
        const warning  = data.filter(r => r.attendance_rate >= 60).length;
        return stat(data.length,'Total Flagged','s-danger') + stat(critical,'Critical (<60%)','s-danger') + stat(warning,'Warning (60–74%)','s-warning');
    }
    if (type === 'at_risk') {
        const high   = data.filter(r => r.risk_level === 'HIGH').length;
        const medium = data.filter(r => r.risk_level === 'MEDIUM').length;
        const avgProb = data.length ? (data.reduce((s,r) => s + parseFloat(r.risk_probability), 0) / data.length).toFixed(1) : 0;
        return stat(data.length,'Total At-Risk','s-danger') + stat(high,'High Risk','s-danger') + stat(medium,'Medium Risk','s-warning') + stat(avgProb+'%','Avg Risk Score','s-warning');
    }
    if (type === 'final_grade_forecast') {
        const alreadyOk  = data.filter(r => r.predicted_status === 'PASSING').length;
        const canPass    = data.filter(r => r.predicted_status === 'AT_RISK').length;
        const impossible = data.filter(r => r.predicted_status === 'CANNOT_PASS').length;
        return stat(data.length,'Total Students','') + stat(alreadyOk,'On Track to Pass','s-success') + stat(canPass,'Close to Passing','s-warning') + stat(impossible,'Cannot Pass','s-danger');
    }
    return stat(data.length, 'Students Found', '');
}

function stat(n, label, cls) {
    return `<div class="pred-stat ${cls}"><div class="ps-num">${n}</div><div class="ps-label">${label}</div></div>`;
}

function selectType(card) {
    document.querySelectorAll('.pred-type-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    currentType = card.dataset.type;
    loadPrediction();
}

function loadPrediction() {
    if (!currentType) return;

    const section     = document.getElementById('pred-section').value;
    const subject     = document.getElementById('pred-subject').value;
    const grade_level = document.getElementById('pred-grade-level').value;
    const m           = meta[currentType];

    // Banner
    const banner = document.getElementById('pred-banner');
    document.getElementById('pred-banner-icon').className = `fa-solid ${m.icon}`;
    document.getElementById('pred-banner-text').innerHTML = m.desc;
    banner.className = `pred-banner show ${m.bannerClass}`;

    // Reset
    document.getElementById('pred-stats').style.display = 'none';
    document.getElementById('pred-results').innerHTML = `
        <div class="pred-result-card">
            <div class="pred-loading">
                <i class="fa-solid fa-spinner"></i>
                <p>Loading prediction data...</p>
            </div>
        </div>`;

    // Reset safe dashboard
    const safeDashWrap = document.getElementById('safe-dashboard-wrap');
    safeDashWrap.style.display = 'none';
    safeDashWrap.innerHTML = '';

    const mainFetch = fetch(`../controllers/prediction_controller.php?action=${currentType}&section=${encodeURIComponent(section)}&subject=${encodeURIComponent(subject)}&grade_level=${encodeURIComponent(grade_level)}`)
        .then(r => r.json())
        .then(data => {
            // Stats
            const statsEl = document.getElementById('pred-stats');
            statsEl.innerHTML = buildStats(currentType, data);
            statsEl.style.display = data.length ? 'grid' : 'none';

            // Results
            const resultsEl = document.getElementById('pred-results');
            if (!data.length) {
                resultsEl.innerHTML = `
                    <div class="pred-result-card">
                        <div class="pred-empty">
                            <i class="fa-solid fa-circle-check"></i>
                            <p>No students flagged for this prediction.</p>
                        </div>
                    </div>`;
            } else {
                const cols = columns[currentType];
                const bars = filterBars[currentType] || [];
                window._tableFilter = 'all';
                const filterBar = bars.length ? `
                    <div class="honor-filter-bar">
                        ${bars.map((b,i) => `<button class="${i===0?'active':''}" onclick="setFilter(this,'${b.v}')">${b.l}</button>`).join('')}
                    </div>` : '';
                let html = `<div class="pred-result-card"><h3><i class="fa-solid fa-table-list"></i> ${data.length} student(s) found</h3>${filterBar}<div class="table-search-wrap"><input type="text" class="table-search" id="flagged-search" placeholder="Search student name..." oninput="filterTable('flagged-search','flagged-tbody')"></div><div style="overflow-x:auto;"><table><thead><tr>`;
                cols.forEach(c => html += `<th>${c.l}</th>`);
                html += `</tr></thead><tbody id="flagged-tbody">`;
                data.forEach(row => {
                    html += `<tr data-filter="${getRowFilterVal(currentType, row)}">` ;
                    cols.forEach(c => {
                        const v = row[c.k] !== undefined ? row[c.k] : '';
                        html += `<td>${c.r ? c.r(v, row) : (v !== null && v !== '' ? v : 'N/A')}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody></table></div></div>';
                resultsEl.innerHTML = html;
            }
        })
        .catch(() => {
            document.getElementById('pred-results').innerHTML = `
                <div class="pred-result-card" style="text-align:center;padding:30px;color:#dc3545;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:28px;"></i>
                    <p style="margin-top:10px;">Error loading prediction data.</p>
                </div>`;
        });

    // Safe students dashboard — runs independently for attendance_risk
    if (currentType === 'attendance_risk') {
        fetch(`../controllers/prediction_controller.php?action=safe_students&section=${encodeURIComponent(section)}&subject=${encodeURIComponent(subject)}&grade_level=${encodeURIComponent(grade_level)}`)
            .then(r => r.text())
            .then(text => {
                let safeData = [];
                try { safeData = JSON.parse(text); } catch(e) { console.error('safe_students parse error:', text); }
                safeDashWrap.innerHTML = buildSafeDashboard(safeData);
                safeDashWrap.style.display = '';
            })
            .catch(() => {
                safeDashWrap.innerHTML = buildSafeDashboard([]);
                safeDashWrap.style.display = '';
            });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
