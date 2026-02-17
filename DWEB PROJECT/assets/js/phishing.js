/**
 * Fox Lab – Phishing Simulator JS
 * Interactive phishing email analysis with full scenario flow
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize indicator checkboxes
    const checkboxes = document.querySelectorAll('.indicator-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateIndicatorCount);
    });
});

/**
 * Update the count of checked indicators
 */
function updateIndicatorCount() {
    const total = document.querySelectorAll('.indicator-checkbox').length;
    const checked = document.querySelectorAll('.indicator-checkbox:checked').length;
    const counter = document.getElementById('indicatorCount');
    if (counter) {
        counter.textContent = `${checked} of ${total} indicators checked`;
    }
}

/**
 * Submit user's phishing assessment response
 * @param {string} response - 'phishing' or 'legitimate'
 * @param {number} scenarioId - The current scenario ID
 */
function submitResponse(response, scenarioId) {
    const analysisSection = document.getElementById('analysisSection');
    const resultIcon = document.getElementById('resultIcon');
    const resultTitle = document.getElementById('resultTitle');
    const resultMessage = document.getElementById('resultMessage');
    const redFlagsList = document.getElementById('redFlagsList');
    const correctCount = document.getElementById('correctCount');
    const incorrectCount = document.getElementById('incorrectCount');

    // Disable buttons to prevent double submission
    const buttons = document.querySelectorAll('.phishing-action-btn');
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';
    });

    // Also disable indicator checkboxes after submission
    document.querySelectorAll('.indicator-checkbox').forEach(cb => {
        cb.disabled = true;
    });

    // Get checked indicators for accuracy feedback
    const allIndicators = document.querySelectorAll('.indicator-checkbox');
    const checkedIndicators = [];
    let correctChecks = 0;
    let totalCorrectIndicators = 0;

    allIndicators.forEach(cb => {
        const isCorrectIndicator = cb.dataset.correct === '1';
        if (isCorrectIndicator) totalCorrectIndicators++;
        if (cb.checked) {
            checkedIndicators.push(cb.value);
            if (isCorrectIndicator) correctChecks++;
        }
    });

    // Send response to backend
    const formData = new FormData();
    formData.append('action', 'submit_response');
    formData.append('scenario_id', scenarioId);
    formData.append('user_response', response);
    formData.append('checked_indicators', JSON.stringify(checkedIndicators));

    fetch('phishing.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        // Show analysis section
        if (analysisSection) {
            analysisSection.style.display = 'block';
            analysisSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Update result display
        if (data.correct) {
            if (resultIcon) resultIcon.innerHTML = '<i class="fas fa-check-circle" style="color:#27ae60;font-size:3rem;"></i>';
            if (resultTitle) {
                resultTitle.textContent = 'Correct!';
                resultTitle.style.color = '#27ae60';
            }
        } else {
            if (resultIcon) resultIcon.innerHTML = '<i class="fas fa-times-circle" style="color:#e74c3c;font-size:3rem;"></i>';
            if (resultTitle) {
                resultTitle.textContent = 'Incorrect';
                resultTitle.style.color = '#e74c3c';
            }
        }
        if (resultMessage) resultMessage.textContent = data.message || '';

        // Update score counters
        if (correctCount && data.session_correct !== undefined) {
            correctCount.textContent = data.session_correct;
        }
        if (incorrectCount && data.session_incorrect !== undefined) {
            incorrectCount.textContent = data.session_incorrect;
        }

        // Display red flags
        const redFlagsHeading = document.getElementById('redFlagsHeading');
        if (redFlagsList && data.red_flags && data.red_flags.length > 0) {
            if (redFlagsHeading) redFlagsHeading.style.display = 'block';
            redFlagsList.style.display = 'block';
            redFlagsList.innerHTML = '';
            data.red_flags.forEach(flag => {
                const li = document.createElement('li');
                li.style.padding = '12px 0';
                li.style.borderBottom = '1px solid rgba(0,0,0,0.08)';
                li.innerHTML = `<i class="${escapeHtml(flag.flag_icon || 'fas fa-exclamation-triangle')}" style="color:#e74c3c;margin-right:10px;"></i>
                    <strong>${escapeHtml(flag.flag_title)}</strong><br>
                    <span style="color:#666;font-size:0.9rem;margin-left:24px;">${escapeHtml(flag.flag_description)}</span>`;
                redFlagsList.appendChild(li);
            });
        } else if (redFlagsList) {
            if (redFlagsHeading) redFlagsHeading.style.display = 'block';
            redFlagsList.style.display = 'block';
            redFlagsList.innerHTML = '<li style="padding:12px 0;color:#27ae60;"><i class="fas fa-check-circle" style="margin-right:8px;"></i>No red flags — this was a legitimate email.</li>';
        }

        // Show indicator accuracy feedback
        const indicatorAccuracy = document.getElementById('indicatorAccuracy');
        const indicatorAccuracyText = document.getElementById('indicatorAccuracyText');
        if (indicatorAccuracy && indicatorAccuracyText && allIndicators.length > 0) {
            indicatorAccuracy.style.display = 'block';
            if (totalCorrectIndicators === 0) {
                indicatorAccuracyText.innerHTML = 'This was a legitimate email — there were no phishing indicators to identify.';
            } else if (checkedIndicators.length === 0) {
                indicatorAccuracyText.innerHTML = `You didn't check any indicators. There were <strong>${totalCorrectIndicators}</strong> correct phishing indicator(s) to identify.`;
            } else {
                const percentage = Math.round((correctChecks / totalCorrectIndicators) * 100);
                indicatorAccuracyText.innerHTML = `You correctly identified <strong>${correctChecks} of ${totalCorrectIndicators}</strong> phishing indicators (${percentage}% accuracy).`;
            }
        }

        // Highlight correct/incorrect indicators visually
        allIndicators.forEach(cb => {
            const li = cb.closest('.indicator-item');
            if (!li) return;
            const isCorrect = cb.dataset.correct === '1';
            if (isCorrect) {
                li.style.borderLeft = '3px solid #27ae60';
                li.style.paddingLeft = '10px';
                if (!cb.checked) {
                    li.style.opacity = '0.7';
                }
            } else if (cb.checked) {
                li.style.borderLeft = '3px solid #e74c3c';
                li.style.paddingLeft = '10px';
            }
        });

        // Show navigation buttons
        const currentIndex = parseInt(document.getElementById('currentScenarioIndex')?.value || '0');
        const totalScenarios = parseInt(document.getElementById('totalScenarios')?.value || '1');
        const nextBtn = document.getElementById('nextScenarioBtn');
        const restartBtn = document.getElementById('restartBtn');
        const finalResults = document.getElementById('finalResults');

        if (currentIndex + 1 >= totalScenarios) {
            // Last scenario — show restart and final results
            if (nextBtn) nextBtn.style.display = 'none';
            if (restartBtn) restartBtn.style.display = 'inline-flex';

            setTimeout(() => {
                if (finalResults) {
                    finalResults.style.display = 'block';
                    const fc = document.getElementById('finalCorrect');
                    const fi = document.getElementById('finalIncorrect');
                    const fa = document.getElementById('finalAccuracy');
                    const sc = data.session_correct || 0;
                    const si = data.session_incorrect || 0;
                    const total = sc + si;
                    if (fc) fc.textContent = sc;
                    if (fi) fi.textContent = si;
                    if (fa) fa.textContent = total > 0 ? Math.round((sc / total) * 100) + '%' : '0%';
                    finalResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 800);
        } else {
            // More scenarios available
            if (nextBtn) nextBtn.style.display = 'inline-flex';
            if (restartBtn) restartBtn.style.display = 'none';
        }
    })
    .catch(err => {
        console.error('Failed to submit response:', err);
        if (analysisSection) analysisSection.style.display = 'block';
        if (resultTitle) {
            resultTitle.textContent = 'Error';
            resultTitle.style.color = '#e74c3c';
        }
        if (resultMessage) resultMessage.textContent = 'Failed to process your response. Please try again.';
        // Re-enable buttons
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        });
    });
}

/**
 * Skip to the next scenario without answering
 */
function skipScenario() {
    const currentIndex = parseInt(document.getElementById('currentScenarioIndex')?.value || '0');
    const totalScenarios = parseInt(document.getElementById('totalScenarios')?.value || '4');
    
    let nextIndex = currentIndex + 1;
    if (nextIndex >= totalScenarios) {
        // Last scenario – go to results
        window.location.href = 'phishing.php?restart=1';
    } else {
        window.location.href = 'phishing.php?scenario=' + nextIndex;
    }
}

/**
 * Navigate to the next phishing scenario after answering
 */
function goToNextScenario() {
    const currentIndex = parseInt(document.getElementById('currentScenarioIndex')?.value || '0');
    const totalScenarios = parseInt(document.getElementById('totalScenarios')?.value || '4');
    
    let nextIndex = currentIndex + 1;
    if (nextIndex >= totalScenarios) {
        nextIndex = 0;
    }

    window.location.href = 'phishing.php?scenario=' + nextIndex;
}

/**
 * Legacy alias
 */
function startNewTest() {
    goToNextScenario();
}

/**
 * Escape HTML characters for safe output
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
