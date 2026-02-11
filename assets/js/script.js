function calculateAverage(quarter) {
    // Find the button for this quarter and get its row (Written Works row)
    const button = document.querySelector(`button[onclick="calculateAverage(${quarter})"]`);
    const wwRow = button.closest('tr');

    // Get Written Works percentage from the 6th td in the WW row
    const wwPercent = parseFloat(wwRow.querySelector('td:nth-child(6)').textContent.replace('%', '')) || 0;

    // Get Performance Tasks row (next sibling)
    const ptRow = wwRow.nextElementSibling;
    const ptPercent = parseFloat(ptRow.querySelector('td:nth-child(5)').textContent.replace('%', '')) || 0;

    // Get Assessment row (next sibling after PT)
    const asRow = ptRow.nextElementSibling;
    const asPercent = parseFloat(asRow.querySelector('td:nth-child(5)').textContent.replace('%', '')) || 0;

    // Calculate the quarter grade: ww * 0.3 + pt * 0.5 + as * 0.2
    const quarterGrade = (wwPercent * 0.3) + (ptPercent * 0.5) + (asPercent * 0.2);

    // Display the result in the div beside the button
    const gradeDiv = document.getElementById(`quarter-grade-${quarter}`);
    gradeDiv.textContent = `Grade: ${quarterGrade.toFixed(2)}`;
}

function generateFinalGrade() {
    document.getElementById('final-grade-modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('final-grade-modal').style.display = 'none';
}

function computeFinalGrade() {
    // Get quarter grades from the main page calculations
    let q1 = parseFloat(document.getElementById('quarter-grade-1').textContent.replace('Grade: ', '')) || 0;
    let q2 = parseFloat(document.getElementById('quarter-grade-2').textContent.replace('Grade: ', '')) || 0;
    let q3 = parseFloat(document.getElementById('quarter-grade-3').textContent.replace('Grade: ', '')) || 0;
    let q4 = parseFloat(document.getElementById('quarter-grade-4').textContent.replace('Grade: ', '')) || 0;

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
    const q1 = parseFloat(document.getElementById('q1-grade').textContent) || 0;
    const q2 = parseFloat(document.getElementById('q2-grade').textContent) || 0;
    const q3 = parseFloat(document.getElementById('q3-grade').textContent) || 0;
    const q4 = parseFloat(document.getElementById('q4-grade').textContent) || 0;
    const rawFinal = parseFloat(document.getElementById('raw-final').textContent) || 0;
    const roundedFinal = parseInt(document.getElementById('rounded-final').textContent) || 0;
    const remarks = document.getElementById('remarks').textContent;

    fetch('../controllers/final_grades_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'finalize',
            student_id: studentId,
            q1_grade: q1,
            q2_grade: q2,
            q3_grade: q3,
            q4_grade: q4,
            raw_final_grade: rawFinal,
            rounded_final_grade: roundedFinal,
            remarks: remarks
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Final grade finalized successfully!');
            closeModal();
        } else {
            alert('Error finalizing grade: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
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
