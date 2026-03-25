/**
 * Fox Lab – Online Compiler JS
 * Real code execution via Piston API (Python & Java)
 * Includes interactive tutorials
 */

// Local PHP proxy – calls Piston API server-side, falls back to local Python/Java
const EXECUTE_API = '../api/execute.php';

const LANG_CONFIG = {
    python: { language: 'python', version: '3.10.0', label: 'Python 3.10' },
    java:   { language: 'java',   version: '15.0.2', label: 'Java 15' }
};

/* ══════════════════════════════════════════════
   TUTORIALS DATA (loaded from database)
   ══════════════════════════════════════════════ */
let TUTORIALS = { python: [], java: [] };

// Fetch tutorials from DB on page load
fetch('../api/tutorials.php')
    .then(res => res.json())
    .then(data => { TUTORIALS = data; })
    .catch(err => console.error('Failed to load tutorials:', err));

document.addEventListener('DOMContentLoaded', () => {
    const editor = document.getElementById('codeEditor');
    if (!editor) return;

    // Tab key inserts 4 spaces
    editor.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            e.preventDefault();
            const start = editor.selectionStart;
            const end = editor.selectionEnd;
            editor.value = editor.value.substring(0, start) + '    ' + editor.value.substring(end);
            editor.selectionStart = editor.selectionEnd = start + 4;
            updateLineNumbers();
        }
    });

    // Line numbers
    editor.addEventListener('input', updateLineNumbers);
    editor.addEventListener('scroll', syncScroll);
    updateLineNumbers();

    // Language selector – update file tab icon & quick reference
    const langSelect = document.getElementById('languageSelect');
    if (langSelect) {
        langSelect.addEventListener('change', () => {
            const lang = langSelect.value;
            const fileTab = document.querySelector('.file-tab i');
            if (fileTab) {
                fileTab.className = lang === 'java' ? 'fab fa-java' : 'fab fa-python';
            }
        });
    }

    // Ctrl+Enter to run
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            runCode();
        }
    });
});

/* ---- Line Numbers ---- */
function updateLineNumbers() {
    const editor = document.getElementById('codeEditor');
    const gutter = document.getElementById('lineNumbers');
    if (!editor || !gutter) return;
    const count = editor.value.split('\n').length;
    let nums = '';
    for (let i = 1; i <= count; i++) nums += i + '\n';
    gutter.textContent = nums;
}

function syncScroll() {
    const editor = document.getElementById('codeEditor');
    const gutter = document.getElementById('lineNumbers');
    if (editor && gutter) gutter.scrollTop = editor.scrollTop;
}

/* ---- Run Code via Piston API ---- */
async function runCode() {
    const editor   = document.getElementById('codeEditor');
    const langSel  = document.getElementById('languageSelect');
    const output   = document.getElementById('outputArea');
    const console_ = document.getElementById('consoleArea');
    const errors_  = document.getElementById('errorsArea');
    const timeEl   = document.getElementById('execTime');
    const memBar   = document.getElementById('memoryBar');
    const memText  = document.getElementById('memoryText');
    const runBtn   = document.getElementById('runBtn');

    if (!editor || !output) return;

    const code = editor.value.trim();
    const lang = langSel ? langSel.value : 'python';

    if (!code) {
        output.innerHTML = '<span style="color:#e74c3c;">Error: No code to execute.</span>';
        switchOutputTab('output');
        return;
    }

    // Disable run button, show spinner
    if (runBtn) { runBtn.disabled = true; runBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running...'; }
    output.innerHTML = '<span style="color:#0074D9;"><i class="fas fa-spinner fa-spin"></i> Executing code...</span>';
    if (console_) console_.innerHTML = '<span style="color:#6c757d;">Waiting for execution...</span>';
    if (errors_)  errors_.innerHTML  = '';
    switchOutputTab('output');

    const startTime = performance.now();

    const config = LANG_CONFIG[lang] || LANG_CONFIG.python;

    // Build filename
    let filename = 'main.py';
    if (lang === 'java') {
        // Try to extract class name from code
        const classMatch = code.match(/public\s+class\s+(\w+)/);
        filename = classMatch ? classMatch[1] + '.java' : 'Main.java';
    }

    try {
        const res = await fetch(EXECUTE_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                language: config.language,
                version:  config.version,
                code:     code,
                filename: filename,
                stdin:    ''
            })
        });

        const elapsed = ((performance.now() - startTime) / 1000).toFixed(3);
        if (timeEl) timeEl.textContent = elapsed + 's';

        if (!res.ok) {
            const errBody = await res.json().catch(() => null);
            throw new Error(errBody?.error || 'Server returned status ' + res.status);
        }

        const data = await res.json();

        // Compile stage
        let compileOut = '';
        if (data.compile) {
            if (data.compile.stderr) {
                compileOut = data.compile.stderr;
            } else if (data.compile.output) {
                compileOut = data.compile.output;
            }
        }

        // Run stage
        const runOut = data.run?.stdout || '';
        const runErr = data.run?.stderr || '';
        const exitCode = data.run?.code ?? 0;

        // Populate output
        if (runOut) {
            output.innerHTML = escapeHtml(runOut);
        } else if (exitCode === 0 && !runErr && !compileOut) {
            output.innerHTML = '<span style="color:#27ae60;">Program executed successfully (no output).</span>';
        } else if (!runOut && !runErr && compileOut) {
            output.innerHTML = '<span style="color:#e74c3c;">Compilation failed. See Errors tab.</span>';
        } else {
            output.innerHTML = '<span style="color:#6c757d;">No output produced.</span>';
        }

        // Console tab – show compile info
        if (console_) {
            let log = '';
            if (lang === 'java') log += '[JVM] Compiling with javac...\n';
            else log += '[Python] Running with ' + config.label + '...\n';
            if (exitCode === 0) log += '[OK] Exit code: 0\n';
            else log += '[FAIL] Exit code: ' + exitCode + '\n';
            log += '[Time] ' + elapsed + 's';
            console_.innerHTML = escapeHtml(log);
        }

        // Errors tab
        if (errors_) {
            const allErrors = (compileOut + '\n' + runErr).trim();
            if (allErrors) {
                errors_.innerHTML = '<span style="color:#e74c3c;">' + escapeHtml(allErrors) + '</span>';
                if (!runOut) switchOutputTab('errors');
            } else {
                errors_.innerHTML = '<span style="color:#27ae60;">No errors detected.</span>';
            }
        }

        // Memory bar (simulated based on output size)
        const outSize = (runOut.length + runErr.length);
        const memPct = Math.min(95, Math.max(5, Math.floor(outSize / 50) + 10));
        if (memBar) {
            memBar.style.width = memPct + '%';
            memBar.style.background = memPct > 70 ? '#e74c3c' : memPct > 40 ? '#f39c12' : '#27ae60';
        }
        if (memText) memText.textContent = memPct + '% used';

    } catch (err) {
        const elapsed = ((performance.now() - startTime) / 1000).toFixed(3);
        if (timeEl) timeEl.textContent = elapsed + 's';
        output.innerHTML = '<span style="color:#e74c3c;">Execution error: ' + escapeHtml(err.message) + '</span>' +
                           '<br><span style="color:#6c757d;">Tip: Make sure Apache is running and try again.</span>';
        if (errors_) errors_.innerHTML = '<span style="color:#e74c3c;">' + escapeHtml(err.message) + '</span>';
    } finally {
        if (runBtn) { runBtn.disabled = false; runBtn.innerHTML = '<i class="fas fa-play"></i> Run Code'; }
    }
}

/* ---- Switch output tabs ---- */
function switchOutputTab(tab) {
    document.querySelectorAll('.output-tab').forEach(t => t.classList.remove('active'));
    const active = document.querySelector('.output-tab[data-tab="' + tab + '"]');
    if (active) active.classList.add('active');

    ['output', 'console', 'errors'].forEach(p => {
        const panel = document.getElementById(p + 'Panel');
        if (panel) panel.style.display = (p === tab) ? 'block' : 'none';
    });
}

/* ---- Clear editor ---- */
function clearEditor() {
    const editor = document.getElementById('codeEditor');
    if (editor && confirm('Clear all code?')) {
        editor.value = '';
        updateLineNumbers();
    }
}

/* ---- Escape HTML ---- */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML.replace(/\n/g, '<br>');
}

/* ══════════════════════════════════════════════
   TUTORIAL PANEL FUNCTIONS
   ══════════════════════════════════════════════ */

/**
 * Show the tutorial panel for a given language
 */
function showTutorial(lang) {
    const panel = document.getElementById('tutorialPanel');
    const quickRef = document.getElementById('quickRefSection');
    const title = document.getElementById('tutorialTitle');
    const lessonsDiv = document.getElementById('tutorialLessons');
    if (!panel || !lessonsDiv) return;

    const lessons = TUTORIALS[lang] || [];
    const langLabel = lang === 'java' ? 'Java' : 'Python';
    const icon = lang === 'java' ? 'fab fa-java' : 'fab fa-python';

    if (title) title.innerHTML = `<i class="${icon}" style="margin-right:8px;color:var(--accent);"></i>${langLabel} Tutorials`;

    lessonsDiv.innerHTML = '';
    lessons.forEach((lesson, idx) => {
        const card = document.createElement('div');
        card.className = 'tutorial-lesson-card';
        card.innerHTML = `
            <div class="tutorial-lesson-header" onclick="this.parentElement.classList.toggle('expanded')">
                <div>
                    <strong>${escapeHtmlPlain(lesson.title)}</strong>
                    <p style="margin:4px 0 0;font-size:0.8rem;color:var(--text-muted);">${escapeHtmlPlain(lesson.desc)}</p>
                </div>
                <i class="fas fa-chevron-down tutorial-chevron"></i>
            </div>
            <div class="tutorial-lesson-body">
                <pre class="tutorial-code-preview"><code>${escapeHtmlPlain(lesson.code)}</code></pre>
                <button class="btn btn-primary btn-sm tutorial-try-btn" onclick="loadTutorialCode('${lang}', ${idx})">
                    <i class="fas fa-play"></i> Try it Yourself
                </button>
            </div>
        `;
        lessonsDiv.appendChild(card);
    });

    // Hide quick ref, show tutorial panel
    if (quickRef) quickRef.style.display = 'none';
    panel.style.display = 'block';
}

/**
 * Close the tutorial panel and show quick ref again
 */
function closeTutorial() {
    const panel = document.getElementById('tutorialPanel');
    const quickRef = document.getElementById('quickRefSection');
    if (panel) panel.style.display = 'none';
    if (quickRef) quickRef.style.display = 'block';
}

/**
 * Load a tutorial's code into the editor and switch language
 */
function loadTutorialCode(lang, lessonIndex) {
    const lessons = TUTORIALS[lang] || [];
    if (!lessons[lessonIndex]) return;

    const editor = document.getElementById('codeEditor');
    const langSelect = document.getElementById('languageSelect');
    if (!editor) return;

    // Set language
    if (langSelect) {
        langSelect.value = lang;
        langSelect.dispatchEvent(new Event('change'));
    }

    // Set code
    editor.value = lessons[lessonIndex].code;
    if (typeof updateLineNumbers === 'function') updateLineNumbers();

    // Scroll editor into view
    editor.scrollTop = 0;
    editor.focus();
}

/**
 * Plain HTML escape (no <br> newline replacement)
 */
function escapeHtmlPlain(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
