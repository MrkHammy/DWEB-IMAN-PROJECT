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
   TUTORIALS DATA
   ══════════════════════════════════════════════ */
const TUTORIALS = {
    python: [
        {
            title: '1. Hello World',
            desc: 'Your first Python program – printing text to the screen.',
            code: '# Lesson 1: Hello World\n# The print() function outputs text to the console\n\nprint("Hello, World!")\nprint("Welcome to Python programming!")\n\n# Try changing the text inside the quotes!'
        },
        {
            title: '2. Variables & Data Types',
            desc: 'Learn how to store and use data in variables.',
            code: '# Lesson 2: Variables & Data Types\n\n# Strings (text)\nname = "Fox Lab"\nprint("Name:", name)\n\n# Integers (whole numbers)\nage = 25\nprint("Age:", age)\n\n# Floats (decimal numbers)\nprice = 19.99\nprint("Price:", price)\n\n# Booleans (True/False)\nis_active = True\nprint("Active:", is_active)\n\n# Type checking\nprint("\\nType of name:", type(name))\nprint("Type of age:", type(age))'
        },
        {
            title: '3. User Input',
            desc: 'Get input from the user and use it in your program.',
            code: '# Lesson 3: User Input\n# Note: input() reads text from the user\n\nname = "Student"  # In a real terminal, you would use: input("Enter your name: ")\nprint("Hello, " + name + "!")\n\n# Converting input to numbers\nage_str = "20"  # In real terminal: input("Enter your age: ")\nage = int(age_str)\nprint("In 5 years, you will be", age + 5, "years old")\n\n# String formatting (f-strings)\nprint(f"\\n{name} is {age} years old.")'
        },
        {
            title: '4. If/Else Conditions',
            desc: 'Make decisions in your code with conditional statements.',
            code: '# Lesson 4: If/Else Conditions\n\nscore = 85\n\nif score >= 90:\n    grade = "A"\n    print("Excellent work!")\nelif score >= 80:\n    grade = "B"\n    print("Good job!")\nelif score >= 70:\n    grade = "C"\n    print("Satisfactory")\nelse:\n    grade = "F"\n    print("Needs improvement")\n\nprint(f"Score: {score} → Grade: {grade}")\n\n# Try changing the score to see different results!'
        },
        {
            title: '5. Loops',
            desc: 'Repeat actions with for and while loops.',
            code: '# Lesson 5: Loops\n\n# For loop with range\nprint("Counting to 5:")\nfor i in range(1, 6):\n    print(f"  {i}")\n\n# For loop with a list\nprint("\\nFruits:")\nfruits = ["Apple", "Banana", "Cherry", "Mango"]\nfor fruit in fruits:\n    print(f"  🍎 {fruit}")\n\n# While loop\nprint("\\nCountdown:")\ncount = 5\nwhile count > 0:\n    print(f"  {count}...")\n    count -= 1\nprint("  🚀 Liftoff!")'
        },
        {
            title: '6. Functions',
            desc: 'Create reusable blocks of code with functions.',
            code: '# Lesson 6: Functions\n\n# Basic function\ndef greet(name):\n    return f"Hello, {name}! Welcome to Fox Lab."\n\nprint(greet("Alice"))\nprint(greet("Bob"))\n\n# Function with default parameter\ndef power(base, exponent=2):\n    return base ** exponent\n\nprint(f"\\n3 squared = {power(3)}")\nprint(f"2 cubed = {power(2, 3)}")\n\n# Function that processes a list\ndef average(numbers):\n    return sum(numbers) / len(numbers)\n\nscores = [92, 87, 95, 78, 90]\nprint(f"\\nAverage score: {average(scores):.1f}")'
        },
        {
            title: '7. Lists & Dictionaries',
            desc: 'Work with collections of data.',
            code: '# Lesson 7: Lists & Dictionaries\n\n# Lists – ordered, mutable collections\ncolors = ["red", "green", "blue"]\ncolors.append("yellow")\nprint("Colors:", colors)\nprint("First color:", colors[0])\nprint("List length:", len(colors))\n\n# List comprehension\nsquares = [x**2 for x in range(1, 6)]\nprint("Squares:", squares)\n\n# Dictionaries – key-value pairs\nstudent = {\n    "name": "Alice",\n    "age": 20,\n    "major": "Cybersecurity",\n    "gpa": 3.8\n}\n\nprint(f"\\nStudent: {student[\'name\']}")\nprint(f"Major: {student[\'major\']}")\n\n# Looping through a dictionary\nprint("\\nAll details:")\nfor key, value in student.items():\n    print(f"  {key}: {value}")'
        },
        {
            title: '8. File Handling & Exceptions',
            desc: 'Handle errors gracefully with try/except.',
            code: '# Lesson 8: Error Handling with Try/Except\n\n# Basic try/except\ntry:\n    result = 10 / 0\nexcept ZeroDivisionError:\n    print("Error: Cannot divide by zero!")\n\n# Handling multiple exceptions\ndef safe_convert(value):\n    try:\n        return int(value)\n    except ValueError:\n        print(f"  Cannot convert \'{value}\' to integer")\n        return None\n\nprint("\\nConverting values:")\nprint(f"  \'42\' → {safe_convert(\'42\')}")\nprint(f"  \'hello\' → {safe_convert(\'hello\')}")\n\n# Try/except/finally\nprint("\\nFull pattern:")\ntry:\n    numbers = [1, 2, 3]\n    print(f"  Third element: {numbers[2]}")\n    print(f"  Fourth element: {numbers[3]}")  # IndexError!\nexcept IndexError:\n    print("  Error: Index out of range!")\nfinally:\n    print("  This always runs (cleanup code goes here)")'
        }
    ],
    java: [
        {
            title: '1. Hello World',
            desc: 'Your first Java program – the classic Hello World.',
            code: '// Lesson 1: Hello World\n// Every Java program needs a class and a main method\n\npublic class Main {\n    public static void main(String[] args) {\n        System.out.println("Hello, World!");\n        System.out.println("Welcome to Java programming!");\n        \n        // Try changing the text inside the quotes!\n    }\n}'
        },
        {
            title: '2. Variables & Data Types',
            desc: 'Learn about Java\'s typed variable system.',
            code: '// Lesson 2: Variables & Data Types\n\npublic class Main {\n    public static void main(String[] args) {\n        // String (text)\n        String name = "Fox Lab";\n        System.out.println("Name: " + name);\n        \n        // int (whole numbers)\n        int age = 25;\n        System.out.println("Age: " + age);\n        \n        // double (decimal numbers)\n        double price = 19.99;\n        System.out.println("Price: " + price);\n        \n        // boolean (true/false)\n        boolean isActive = true;\n        System.out.println("Active: " + isActive);\n        \n        // char (single character)\n        char grade = \'A\';\n        System.out.println("Grade: " + grade);\n        \n        // Type info\n        System.out.println("\\nname is a " + name.getClass().getSimpleName());\n    }\n}'
        },
        {
            title: '3. If/Else Conditions',
            desc: 'Control the flow of your program with conditions.',
            code: '// Lesson 3: If/Else Conditions\n\npublic class Main {\n    public static void main(String[] args) {\n        int score = 85;\n        String grade;\n        \n        if (score >= 90) {\n            grade = "A";\n            System.out.println("Excellent work!");\n        } else if (score >= 80) {\n            grade = "B";\n            System.out.println("Good job!");\n        } else if (score >= 70) {\n            grade = "C";\n            System.out.println("Satisfactory");\n        } else {\n            grade = "F";\n            System.out.println("Needs improvement");\n        }\n        \n        System.out.println("Score: " + score + " → Grade: " + grade);\n        \n        // Try changing the score to see different results!\n    }\n}'
        },
        {
            title: '4. Loops',
            desc: 'Repeat actions with for and while loops.',
            code: '// Lesson 4: Loops\n\npublic class Main {\n    public static void main(String[] args) {\n        // For loop\n        System.out.println("Counting to 5:");\n        for (int i = 1; i <= 5; i++) {\n            System.out.println("  " + i);\n        }\n        \n        // Enhanced for loop (for-each)\n        System.out.println("\\nFruits:");\n        String[] fruits = {"Apple", "Banana", "Cherry", "Mango"};\n        for (String fruit : fruits) {\n            System.out.println("  \\uD83C\\uDF4E " + fruit);\n        }\n        \n        // While loop\n        System.out.println("\\nCountdown:");\n        int count = 5;\n        while (count > 0) {\n            System.out.println("  " + count + "...");\n            count--;\n        }\n        System.out.println("  \\uD83D\\uDE80 Liftoff!");\n    }\n}'
        },
        {
            title: '5. Methods (Functions)',
            desc: 'Create reusable methods in Java.',
            code: '// Lesson 5: Methods\n\npublic class Main {\n    \n    // Method that returns a String\n    public static String greet(String name) {\n        return "Hello, " + name + "! Welcome to Fox Lab.";\n    }\n    \n    // Method with a default-like behavior (overloading)\n    public static int power(int base) {\n        return base * base;  // default: squared\n    }\n    \n    public static int power(int base, int exponent) {\n        int result = 1;\n        for (int i = 0; i < exponent; i++) {\n            result *= base;\n        }\n        return result;\n    }\n    \n    // Method that processes an array\n    public static double average(int[] numbers) {\n        int sum = 0;\n        for (int n : numbers) sum += n;\n        return (double) sum / numbers.length;\n    }\n    \n    public static void main(String[] args) {\n        System.out.println(greet("Alice"));\n        System.out.println(greet("Bob"));\n        \n        System.out.println("\\n3 squared = " + power(3));\n        System.out.println("2 cubed = " + power(2, 3));\n        \n        int[] scores = {92, 87, 95, 78, 90};\n        System.out.printf("\\nAverage score: %.1f%n", average(scores));\n    }\n}'
        },
        {
            title: '6. Arrays & ArrayLists',
            desc: 'Work with fixed-size arrays and dynamic ArrayLists.',
            code: 'import java.util.ArrayList;\nimport java.util.Collections;\n\n// Lesson 6: Arrays & ArrayLists\n\npublic class Main {\n    public static void main(String[] args) {\n        // Fixed-size array\n        String[] colors = {"red", "green", "blue"};\n        System.out.println("Array length: " + colors.length);\n        System.out.println("First color: " + colors[0]);\n        \n        // ArrayList – dynamic size\n        ArrayList<String> fruits = new ArrayList<>();\n        fruits.add("Apple");\n        fruits.add("Banana");\n        fruits.add("Cherry");\n        fruits.add("Mango");\n        \n        System.out.println("\\nFruits: " + fruits);\n        System.out.println("Size: " + fruits.size());\n        \n        // Sort\n        Collections.sort(fruits);\n        System.out.println("Sorted: " + fruits);\n        \n        // Remove\n        fruits.remove("Banana");\n        System.out.println("After remove: " + fruits);\n        \n        // Check if contains\n        System.out.println("Has Apple? " + fruits.contains("Apple"));\n    }\n}'
        },
        {
            title: '7. Classes & Objects (OOP)',
            desc: 'Introduction to Object-Oriented Programming in Java.',
            code: '// Lesson 7: Classes & Objects\n\npublic class Main {\n    \n    // Inner class: Student\n    static class Student {\n        String name;\n        int age;\n        String major;\n        double gpa;\n        \n        // Constructor\n        Student(String name, int age, String major, double gpa) {\n            this.name = name;\n            this.age = age;\n            this.major = major;\n            this.gpa = gpa;\n        }\n        \n        // Method\n        String getInfo() {\n            return name + " | Age: " + age + " | " + major + " | GPA: " + gpa;\n        }\n        \n        boolean isHonors() {\n            return gpa >= 3.5;\n        }\n    }\n    \n    public static void main(String[] args) {\n        // Create objects\n        Student s1 = new Student("Alice", 20, "Cybersecurity", 3.8);\n        Student s2 = new Student("Bob", 22, "Computer Science", 3.2);\n        \n        System.out.println(s1.getInfo());\n        System.out.println("  Honors? " + s1.isHonors());\n        \n        System.out.println(s2.getInfo());\n        System.out.println("  Honors? " + s2.isHonors());\n    }\n}'
        },
        {
            title: '8. Exception Handling',
            desc: 'Handle errors gracefully with try/catch in Java.',
            code: '// Lesson 8: Exception Handling\n\npublic class Main {\n    public static void main(String[] args) {\n        // Basic try/catch\n        try {\n            int result = 10 / 0;\n        } catch (ArithmeticException e) {\n            System.out.println("Error: Cannot divide by zero!");\n        }\n        \n        // Handling multiple exceptions\n        System.out.println("\\nConverting values:");\n        String[] values = {"42", "hello", "99"};\n        \n        for (String val : values) {\n            try {\n                int num = Integer.parseInt(val);\n                System.out.println("  \'" + val + "\' → " + num);\n            } catch (NumberFormatException e) {\n                System.out.println("  \'" + val + "\' → Cannot convert!");\n            }\n        }\n        \n        // Try/catch/finally\n        System.out.println("\\nFull pattern:");\n        try {\n            int[] numbers = {1, 2, 3};\n            System.out.println("  Third element: " + numbers[2]);\n            System.out.println("  Fourth element: " + numbers[3]);\n        } catch (ArrayIndexOutOfBoundsException e) {\n            System.out.println("  Error: Index out of range!");\n        } finally {\n            System.out.println("  This always runs (cleanup code goes here)");\n        }\n    }\n}'
        }
    ]
};

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
