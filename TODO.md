# Fix Generate Final Grade Button in Student View

## Steps:
- [x] 1. Update teacher/assets/js/script.js: Robustify computeFinalGrade (check quarters computed, simplify parsing), fix calculateAverage (plain text only), add logs/validation in finalizeGrade.
- [x] 2. Update teacher/views/student_view.php: Auto-call computeFinalGrade in generateFinalGrade OR add warning button.
- [x] 3. Update teacher/controllers/final_grades_controller.php: Better error handling/logging.
- [ ] 4. Test flow: Compute quarters → Generate Final Grade → Compute Final in modal → Finalize → verify alert/DB.
- [ ] 5. Check browser console for errors, verify final_grades table has data.
**Status**: Starting implementation...

