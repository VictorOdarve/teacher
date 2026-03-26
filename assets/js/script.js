function calculateAverage(quarter) {
    // Find the Calculate button for this quarter
    const button = document.querySelector('button[onclick="calculateAverage(' + quarter + ')"]');
    if (!button) {
        document.getElementById('quarter-grade-' + quarter).textContent = 'No data';
        return;
    }

    // Find WW row (has the button), PT/AS are next siblings
    const wwRow = button.closest('tr');
    const ptRow = wwRow.nextElementSibling;
    const asRow = ptRow ? ptRow.nextElementSibling : null;

    if (!ptRow || !asRow) {
        document.getElementById('quarter-grade-' + quarter).textContent = 'Incomplete rows';
        return;
    }

    // Extract %: col 6 (1-based nth-child(6)) for WW (has rowspan Actions col), col 5 for PT/AS
    const wwPctCell = wwRow.querySelector('td:nth-child(6)');
    const ptPctCell = ptRow.querySelector('td:nth-child(5)');
    const asPctCell = asRow.querySelector('td:nth-child(5)');

    const wwPct = wwPctCell ? parseFloat(wwPctCell.textContent.replace('%', '').trim()) || 0 : 0;
    const ptPct = ptPctCell ? parseFloat(ptPctCell.textContent.replace('%', '').trim()) || 0 : 0;
    const asPct = asPctCell ? parseFloat(asPctCell.textContent.replace('%', '').trim()) || 0 : 0;

    // Weighted average
    const quarterGrade = Math.round(((wwPct * 0.3) + (ptPct * 0.5) + (asPct * 0.2)) * 10) / 10;

    // Display plain text for reliable parsing
    const gradeDiv = document.getElementById('quarter-grade-' + quarter);
    gradeDiv.textContent = `Grade: ${quarterGrade.toFixed(1)}`;
}

function generateFinalGrade() {
    document.getElementById('final-grade-modal').style.display = 'block';
    // Auto compute when opening modal
    setTimeout(computeFinalGrade, 100); // Small delay for modal render
}

function closeModal() {
    document.getElementById('final-grade-modal').style.display = 'none';
}

function computeFinalGrade() {
    // Check if quarters computed
    const quartersDivs = ['quarter-grade-1', 'quarter-grade-2', 'quarter-grade-3', 'quarter-grade-4'];
    let computedCount = 0;
    let q1 = 0, q2 = 0, q3 = 0, q4 = 0;

    const q1Div = document.getElementById('quarter-grade-1');
    if (q1Div && q1Div.textContent.includes('Grade:')) {
        q1 = parseFloat(q1Div.textContent.split(':')[1]?.trim()) || 0;
        computedCount++;
    }

    const q2Div = document.getElementById('quarter-grade-2');
    if (q2Div && q2Div.textContent.includes('Grade:')) {
        q2 = parseFloat(q2Div.textContent.split(':')[1]?.trim()) || 0;
        computedCount++;
    }

    const q3Div = document.getElementById('quarter-grade-3');
    if (q3Div && q3Div.textContent.includes('Grade:')) {
        q3 = parseFloat(q3Div.textContent.split(':')[1]?.trim()) || 0;
        computedCount++;
    }

    const q4Div = document.getElementById('quarter-grade-4');
    if (q4Div && q4Div.textContent.includes('Grade:')) {
        q4 = parseFloat(q4Div.textContent.split(':')[1]?.trim()) || 0;
        computedCount++;
    }

    if (computedCount === 0) {
        alert("Please click 'Calculate' buttons for quarters first to generate final grade!");
        return;
    } else if (computedCount < 4) {
        console.warn(`Only ${computedCount}/4 quarters computed. Using 0 for missing.`);
    }

    // Populate the modal with these grades
    document.getElementById('q1-grade').textContent = q1.toFixed(2);
    document.getElementById('q2-grade').textContent = q2.toFixed(2);
    document.getElementById('q3-grade').textContent = q3.toFixed(2);
    document.getElementById('q4-grade').textContent = q4.toFixed(2);

    // Compute average
    const rawFinal = (q1 + q2 + q3 + q4) / 4;
    const roundedFinal = Math.round(rawFinal);

    document.getElementById('raw-final').textContent = rawFinal.toFixed(2);
    document.getElementById('rounded-final').textContent = roundedFinal;

    // Remarks
    const remarks = roundedFinal >= 75 ? 'PASSED' : 'FAILED';
    document.getElementById('remarks').textContent = remarks;
}

function finalizeGrade() {
    const studentId = document.getElementById('modal-student-id').value;
    if (!studentId) {
        alert('Student ID missing!');
        return;
    }

    const q1 = parseFloat(document.getElementById('q1-grade').textContent) || 0;
    const q2 = parseFloat(document.getElementById('q2-grade').textContent) || 0;
    const q3 = parseFloat(document.getElementById('q3-grade').textContent) || 0;
    const q4 = parseFloat(document.getElementById('q4-grade').textContent) || 0;
    const rawFinal = parseFloat(document.getElementById('raw-final').textContent) || 0;
    const roundedFinal = parseInt(document.getElementById('rounded-final').textContent) || 0;
    const remarks = document.getElementById('remarks').textContent;

    const postData = {
        action: 'finalize',
        student_id: studentId,
        q1_grade: q1,
        q2_grade: q2,
        q3_grade: q3,
        q4_grade: q4,
        raw_final_grade: rawFinal,
        rounded_final_grade: roundedFinal,
        remarks: remarks
    };
    console.log('Finalizing grade data:', postData);

    fetch('../controllers/final_grades_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(postData)
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data);
        if (data.success) {
            alert('Final grade finalized successfully!');
            closeModal();
            location.reload(); // Refresh page
        } else {
            alert('Error finalizing grade: ' + (data.error || 'Unknown server error'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Network/DB Error: ' + error.message + '. Check console.');
    });
}

function editGrade() {
    // Convert the quarter grade cells to inputs for editing
    const quarters = ['q1-grade', 'q2-grade', 'q3-grade', 'q4-grade'];
    quarters.forEach(id => {
        const cell = document.getElementById(id);
        const currentValue = cell.textContent;
        cell.innerHTML = `<input type="number" step="0.01" value="${currentValue}" style="width: 100%; text-align: center;">`;
    });
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    sidebar.classList.toggle('sidebar-hidden');
    toggle.classList.toggle('active');
}

