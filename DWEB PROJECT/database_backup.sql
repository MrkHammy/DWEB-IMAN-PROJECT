-- ============================================================
-- Fox Lab â€“ Cybersecurity Awareness & Training Platform
-- Complete Database Schema + Data
-- ============================================================

CREATE DATABASE IF NOT EXISTS foxlab_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE foxlab_db;

-- ----------------------------------------------------------
-- 0. Users (Authentication)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(200) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('student','admin') DEFAULT 'student',
    avatar_url  VARCHAR(500) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default demo account (password: Password123!)
INSERT INTO users (full_name, email, password, role) VALUES
('Charlie Kirk', 'charlie@foxlab.com', '$2y$10$42j5FdVU8yLRIA./Z6DHUudyYY/X3GQzz4sRVbmEuzi1J.eSeEc4G', 'student'),
('Admin User', 'admin@foxlab.com', '$2y$10$42j5FdVU8yLRIA./Z6DHUudyYY/X3GQzz4sRVbmEuzi1J.eSeEc4G', 'admin');

-- ----------------------------------------------------------
-- 1. Statistics (Home page dynamic counters)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS stats (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    stat_label  VARCHAR(100) NOT NULL,
    stat_value  VARCHAR(50)  NOT NULL,
    stat_icon   VARCHAR(100) DEFAULT NULL,
    display_order INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO stats (stat_label, stat_value, stat_icon, display_order) VALUES
('Active Users', '1K+', 'fas fa-users', 1),
('Organizations', '3+', 'fas fa-building', 2),
('Success Rate', '95%', 'fas fa-chart-line', 3),
('Support', '24/7', 'fas fa-headset', 4);

-- ----------------------------------------------------------
-- 2. Security Tips (Home page)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tips (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    icon        VARCHAR(100) DEFAULT 'fas fa-shield-alt',
    is_active   TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO tips (title, description, icon, display_order) VALUES
('Verify Sender Addresses', 'Always check the email sender\'s address carefully. Phishers often use addresses that look similar to legitimate ones.', 'fas fa-envelope-open-text', 1),
('Think Before You Click', 'Hover over links to preview the URL before clicking. Be wary of shortened URLs or suspicious domains.', 'fas fa-mouse-pointer', 2),
('Enable Two-Factor Authentication', 'Add an extra layer of security to your accounts with 2FA. This significantly reduces the risk of unauthorized access.', 'fas fa-lock', 3),
('Keep Software Updated', 'Regularly update your operating system, browser, and applications to patch known security vulnerabilities.', 'fas fa-sync-alt', 4),
('Use Strong Unique Passwords', 'Create complex passwords for each account. Consider using a password manager to keep track of them securely.', 'fas fa-key', 5);

-- ----------------------------------------------------------
-- 3. Blogs
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS blogs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT DEFAULT NULL,
    title       VARCHAR(255) NOT NULL,
    excerpt     TEXT,
    content     LONGTEXT NOT NULL,
    category    VARCHAR(100) DEFAULT 'Technology',
    author      VARCHAR(150) NOT NULL,
    author_org  VARCHAR(200) DEFAULT NULL,
    image_url   VARCHAR(500) DEFAULT NULL,
    fb_link     VARCHAR(500) DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    views       INT DEFAULT 0,
    read_time   INT DEFAULT 5,
    published_at DATE DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO blogs (title, excerpt, content, category, author, author_org, image_url, read_time, is_featured, published_at) VALUES

('How AI Is Transforming Cybersecurity Defense in 2026',
 'Artificial intelligence is no longer a buzzword in cybersecurity â€” it is the frontline. From real-time threat detection to automated incident response, AI is reshaping how organizations defend their digital assets.',
 '<p>The cybersecurity landscape has undergone a seismic shift in 2026. Artificial intelligence is no longer a supplementary tool â€” it has become the backbone of modern security operations. Organizations that fail to adopt AI-driven defenses are falling dangerously behind against increasingly sophisticated threat actors.</p>

<h3>1. AI-Powered Threat Detection</h3>
<p>Traditional signature-based detection methods are no match for today''s polymorphic malware and zero-day exploits. AI and machine learning models can analyze billions of events in real-time, identifying anomalous patterns that human analysts would miss. Leading security platforms now achieve sub-second detection times, compared to the industry average of 197 days just five years ago.</p>

<h3>2. Automated Incident Response</h3>
<p>Security Orchestration, Automation, and Response (SOAR) platforms powered by AI can automatically contain threats, isolate compromised endpoints, and initiate remediation workflows without human intervention. This dramatically reduces mean time to respond (MTTR) from hours to minutes.</p>

<h3>3. Predictive Threat Intelligence</h3>
<p>AI models trained on global threat data can predict attack patterns before they materialize. By analyzing dark web chatter, vulnerability disclosures, and geopolitical events, these systems provide early warning capabilities that allow organizations to proactively strengthen defenses.</p>

<h3>4. The Double-Edged Sword</h3>
<p>However, threat actors are also leveraging AI. AI-generated phishing emails are nearly indistinguishable from legitimate communications, and adversarial AI techniques can evade machine learning-based detection. The cybersecurity industry must continuously evolve to stay ahead of this AI arms race.</p>

<h3>Key Takeaways</h3>
<ul>
<li>Invest in AI-augmented security tools for real-time detection</li>
<li>Implement SOAR platforms for automated response capabilities</li>
<li>Train your SOC team to work alongside AI systems effectively</li>
<li>Stay informed about adversarial AI techniques and defenses</li>
</ul>',
 'Technology', 'Fox Lab', 'Fox Lab', 'IMGS/blog/ai-cybersecurity.svg', 8, 1, '2026-02-10'),

('The Complete Guide to Zero Trust Architecture',
 'Zero Trust is not just a buzzword â€” it is a fundamental shift in how we approach network security. Learn how to implement Zero Trust principles in your organization step by step.',
 '<p>The traditional castle-and-moat approach to network security is dead. In an era of remote work, cloud computing, and sophisticated insider threats, Zero Trust Architecture (ZTA) has emerged as the gold standard for enterprise security.</p>

<h3>What Is Zero Trust?</h3>
<p>Zero Trust is a security framework built on the principle of "never trust, always verify." Unlike traditional models that grant broad access once inside the network perimeter, Zero Trust requires continuous verification of every user, device, and application attempting to access resources.</p>

<h3>Core Principles</h3>
<ul>
<li><strong>Verify Explicitly:</strong> Always authenticate and authorize based on all available data points, including user identity, location, device health, and behavioral patterns.</li>
<li><strong>Use Least Privilege Access:</strong> Limit user access rights to the minimum necessary for their role, using just-in-time and just-enough-access policies.</li>
<li><strong>Assume Breach:</strong> Design your security architecture under the assumption that a breach has already occurred, minimizing blast radius through micro-segmentation.</li>
</ul>

<h3>Implementation Roadmap</h3>
<p><strong>Phase 1 â€” Identity Foundation:</strong> Deploy strong multi-factor authentication and implement identity governance. This is the foundation of Zero Trust.</p>
<p><strong>Phase 2 â€” Device Trust:</strong> Ensure only compliant, managed devices can access resources. Implement endpoint detection and response (EDR) across all endpoints.</p>
<p><strong>Phase 3 â€” Network Segmentation:</strong> Implement micro-segmentation to restrict lateral movement. Each workload should operate in its own security zone.</p>
<p><strong>Phase 4 â€” Continuous Monitoring:</strong> Deploy SIEM and behavioral analytics to continuously verify trust throughout every session.</p>

<h3>Real-World Results</h3>
<p>Organizations that have fully implemented Zero Trust report a 50% reduction in data breaches and a 40% decrease in security incident response times. The initial investment pays for itself within 18 months through reduced breach costs and improved operational efficiency.</p>',
 'Technology', 'Fox Lab', 'Fox Lab', 'IMGS/blog/zero-trust.svg', 10, 0, '2026-02-05'),

('Ransomware in 2026: Evolution, Impact, and Defense Strategies',
 'Ransomware attacks have evolved beyond simple file encryption. Double extortion, triple extortion, and ransomware-as-a-service are the new normal. Here is how to defend your organization.',
 '<p>Ransomware continues to be the most financially devastating form of cybercrime in 2026. The FBI reports that ransomware payments exceeded $1.5 billion globally last year, with the average ransom demand reaching $2.7 million â€” a 300% increase from 2023.</p>

<h3>The Evolution of Ransomware</h3>
<p><strong>Single Extortion (pre-2020):</strong> Encrypt files, demand payment for decryption key.</p>
<p><strong>Double Extortion (2020-2023):</strong> Encrypt files AND steal data, threatening to publish if ransom is not paid.</p>
<p><strong>Triple Extortion (2024-present):</strong> All of the above PLUS DDoS attacks and direct threats to customers and partners.</p>

<h3>Ransomware-as-a-Service (RaaS)</h3>
<p>The most alarming trend is the professionalization of ransomware operations. Criminal groups now offer RaaS platforms with customer support, negotiation services, and profit-sharing models. This has lowered the barrier to entry, enabling less sophisticated attackers to launch devastating campaigns.</p>

<h3>Defense Strategies That Actually Work</h3>
<ol>
<li><strong>Immutable Backups:</strong> Maintain offline, air-gapped backups that cannot be encrypted by ransomware. Test restoration procedures regularly.</li>
<li><strong>Network Segmentation:</strong> Limit lateral movement to contain the blast radius of an attack.</li>
<li><strong>Email Security:</strong> 91% of ransomware attacks begin with a phishing email. Invest in advanced email filtering and security awareness training.</li>
<li><strong>Endpoint Protection:</strong> Deploy next-generation endpoint protection with behavioral analysis and rollback capabilities.</li>
<li><strong>Incident Response Plan:</strong> Have a tested, documented IR plan that includes communication templates, legal contacts, and decision frameworks for ransom payment.</li>
</ol>

<p><strong>Bottom line:</strong> The question is not whether your organization will face a ransomware attack, but when. Preparation is your best defense.</p>',
 'Threats', 'Fox Lab', 'Fox Lab', 'IMGS/blog/ransomware-defense.svg', 9, 0, '2026-01-28'),

('5 Password Mistakes That Put Your Accounts at Risk',
 'Despite years of warnings, password hygiene remains one of the weakest links in personal and organizational security. Here are the five most common password mistakes and how to fix them.',
 '<p>Passwords remain the primary authentication method for most online services. Yet the Verizon Data Breach Investigations Report consistently shows that over 80% of hacking-related breaches involve compromised credentials. Here are the five most common password mistakes we see â€” and practical solutions for each.</p>

<h3>Mistake #1: Using the Same Password Everywhere</h3>
<p>Password reuse is the single most dangerous password habit. When one service is breached (and breaches happen constantly), attackers try those leaked credentials against every major platform â€” a technique called credential stuffing. If your Netflix password is the same as your banking password, a single breach exposes everything.</p>
<p><strong>Fix:</strong> Use a unique password for every account. A password manager makes this effortless.</p>

<h3>Mistake #2: Choosing Short, Simple Passwords</h3>
<p>Modern hardware can crack an 8-character password in under 5 minutes using brute force. Every additional character exponentially increases cracking time. A 16-character password with mixed character types would take centuries to crack with current technology.</p>
<p><strong>Fix:</strong> Use at least 16 characters. Consider passphrases â€” four or more random words strung together (e.g., "correct-horse-battery-staple").</p>

<h3>Mistake #3: Not Enabling Two-Factor Authentication</h3>
<p>Even the strongest password is useless if it gets phished or leaked. Two-factor authentication (2FA) provides a critical second layer of defense. Google reports that SMS-based 2FA blocks 96% of bulk phishing attacks and 100% of automated bot attacks.</p>
<p><strong>Fix:</strong> Enable 2FA on every account that supports it. Use an authenticator app (Authy, Google Authenticator) or a hardware key (YubiKey) instead of SMS when possible.</p>

<h3>Mistake #4: Using Personal Information</h3>
<p>Passwords based on birthdays, pet names, sports teams, or family names are among the first things attackers try. Social media makes this information trivially easy to find.</p>
<p><strong>Fix:</strong> Never use personally identifiable information in passwords. Let your password manager generate truly random passwords.</p>

<h3>Mistake #5: Never Changing Compromised Passwords</h3>
<p>Many users ignore breach notifications and continue using compromised passwords. Services like Have I Been Pwned show that billions of credentials are freely available to attackers.</p>
<p><strong>Fix:</strong> Register for breach notifications. When notified, change the compromised password immediately â€” and any other accounts where you used the same password.</p>',
 'Education', 'Fox Lab', 'Fox Lab', 'IMGS/blog/password-mistakes.svg', 6, 0, '2026-01-20'),

('Understanding Phishing: How to Spot and Stop Social Engineering Attacks',
 'Phishing attacks are growing more sophisticated thanks to AI-generated content. Learn the telltale signs of phishing emails and how to protect yourself and your organization.',
 '<p>Phishing remains the number one attack vector for cybercriminals, accounting for over 36% of all data breaches according to the 2025 Verizon DBIR. With the advent of AI-generated phishing emails, attacks have become nearly indistinguishable from legitimate communications. Here is your comprehensive guide to identifying and preventing phishing attacks.</p>

<h3>Types of Phishing Attacks</h3>
<ul>
<li><strong>Email Phishing:</strong> Mass emails impersonating trusted organizations â€” the most common form.</li>
<li><strong>Spear Phishing:</strong> Targeted attacks against specific individuals using personalized information.</li>
<li><strong>Whaling:</strong> Spear phishing targeting C-level executives and senior management.</li>
<li><strong>Smishing:</strong> Phishing via SMS text messages with malicious links.</li>
<li><strong>Vishing:</strong> Voice phishing conducted over phone calls.</li>
<li><strong>Quishing:</strong> Phishing via QR codes that redirect to malicious websites.</li>
</ul>

<h3>Red Flags to Watch For</h3>
<ol>
<li><strong>Urgency and threats:</strong> "Your account will be suspended in 24 hours"</li>
<li><strong>Suspicious sender addresses:</strong> Look carefully â€” "support@paypaI.com" uses a capital I, not lowercase L</li>
<li><strong>Generic greetings:</strong> "Dear Customer" instead of your actual name</li>
<li><strong>Grammar and spelling errors:</strong> Though AI has made these less common</li>
<li><strong>Mismatched URLs:</strong> Hover over links before clicking to verify the actual destination</li>
<li><strong>Unexpected attachments:</strong> Especially .exe, .zip, or macro-enabled Office files</li>
</ol>

<h3>How Fox Lab Helps</h3>
<p>Our Phishing Simulator provides hands-on training with realistic scenarios. Users learn to identify phishing indicators in a safe environment, building the muscle memory needed to spot real attacks. Organizations using simulation-based training see a 75% reduction in successful phishing attacks within six months.</p>',
 'Education', 'Fox Lab', 'Fox Lab', 'IMGS/blog/phishing-guide.svg', 7, 0, '2026-01-15'),

('Building Your First Home Lab for Cybersecurity Practice',
 'A home lab is an essential tool for aspiring cybersecurity professionals. Here is a practical, budget-friendly guide to building your own cybersecurity practice environment.',
 '<p>Every cybersecurity professional needs a safe environment to practice offensive and defensive techniques. A home lab provides exactly that â€” a controlled space where you can break things, experiment with tools, and build real-world skills without risking production systems.</p>

<h3>Hardware Options</h3>
<p><strong>Budget Option ($0):</strong> Use your existing computer with virtualization. VirtualBox is free and runs on Windows, macOS, and Linux. You can run 2-3 virtual machines on a computer with 8GB RAM.</p>
<p><strong>Mid-Range Option ($200-500):</strong> Buy a used enterprise server or mini PC (Dell OptiPlex, HP EliteDesk) with 32GB RAM. This lets you run 5-10 VMs simultaneously.</p>
<p><strong>Advanced Option ($500+):</strong> Dedicated server with 64GB+ RAM, or a Proxmox cluster with multiple nodes for enterprise-grade practice.</p>

<h3>Essential Software (All Free)</h3>
<ul>
<li><strong>Hypervisor:</strong> VirtualBox, VMware Workstation Player, or Proxmox VE</li>
<li><strong>Attacking:</strong> Kali Linux â€” the de facto standard for offensive security</li>
<li><strong>Defending:</strong> Security Onion â€” network security monitoring suite</li>
<li><strong>Vulnerable targets:</strong> DVWA, Metasploitable, VulnHub machines</li>
<li><strong>SIEM:</strong> Wazuh or ELK Stack for log analysis practice</li>
<li><strong>Networking:</strong> pfSense or OPNsense for firewall practice</li>
</ul>

<h3>Beginner Projects</h3>
<ol>
<li>Set up a network with pfSense as the gateway and practice firewall rules</li>
<li>Deploy DVWA and practice OWASP Top 10 attacks with Burp Suite</li>
<li>Install Wazuh and monitor your lab for simulated attacks</li>
<li>Practice forensics with pre-built disk images from Digital Corpora</li>
<li>Set up a honeypot and analyze attacker behavior</li>
</ol>

<p>Remember: only practice on systems you own or have explicit permission to test. Unauthorized access to computer systems is illegal regardless of intent.</p>',
 'Education', 'Fox Lab', 'Fox Lab', 'IMGS/blog/home-lab.svg', 8, 0, '2026-01-08'),

('The OWASP Top 10 Explained: Web Application Security in Plain English',
 'The OWASP Top 10 is the most widely referenced web application security standard. We break down each vulnerability category with real-world examples and practical prevention strategies.',
 '<p>The Open Web Application Security Project (OWASP) Top 10 represents the most critical security risks to web applications. Updated regularly based on real-world breach data, it serves as the foundation for web application security testing worldwide. Let us break down each category in plain, actionable language.</p>

<h3>A01: Broken Access Control</h3>
<p>When users can act outside their intended permissions. Example: changing the URL from /user/profile/123 to /user/profile/456 to view another user''s data. Prevention: implement proper access control checks on every request, deny by default.</p>

<h3>A02: Cryptographic Failures</h3>
<p>Sensitive data exposure due to weak or missing encryption. Example: storing passwords in plaintext or using deprecated MD5 hashing. Prevention: use strong encryption (AES-256), hash passwords with bcrypt, enforce HTTPS everywhere.</p>

<h3>A03: Injection</h3>
<p>Untrusted data sent to an interpreter as part of a command. Example: SQL injection allowing an attacker to dump your database. Prevention: use parameterized queries, input validation, and ORM frameworks.</p>

<h3>A04: Insecure Design</h3>
<p>Missing or ineffective security controls built into the application architecture. Prevention: threat modeling, secure design patterns, and security requirements in the development lifecycle.</p>

<h3>A05: Security Misconfiguration</h3>
<p>Default configurations, unnecessary features, and missing security hardening. Example: leaving debug mode enabled in production. Prevention: automated configuration scanning, hardening guides, minimal installations.</p>

<h3>A06: Vulnerable Components</h3>
<p>Using libraries, frameworks, or software with known vulnerabilities. Prevention: maintain a software bill of materials (SBOM), automated dependency scanning with tools like Snyk or Dependabot.</p>

<h3>A07-A10</h3>
<p>Additional categories include identification and authentication failures, software and data integrity failures, security logging and monitoring failures, and server-side request forgery (SSRF). Each requires specific controls and monitoring strategies.</p>

<p>Fox Lab''s Online Compiler lets you practice writing secure code with real-time feedback. Start coding at our platform and learn to avoid these vulnerabilities hands-on.</p>',
 'Technology', 'Fox Lab', 'Fox Lab', 'IMGS/blog/owasp-top10.svg', 11, 0, '2025-12-20'),

('Cybersecurity Career Paths: From Beginner to Expert',
 'The cybersecurity field offers diverse career paths with strong job growth and competitive salaries. Here is a comprehensive roadmap for launching and advancing your cybersecurity career.',
 '<p>The cybersecurity workforce gap continues to grow, with an estimated 3.5 million unfilled positions globally. This shortage means exceptional opportunities for those willing to invest in building cybersecurity skills. Here is your roadmap from beginner to expert.</p>

<h3>Entry-Level Roles (0-2 Years)</h3>
<ul>
<li><strong>SOC Analyst (Tier 1):</strong> Monitor security alerts, triage incidents, and escalate threats. Average salary: $55,000-$75,000.</li>
<li><strong>IT Security Specialist:</strong> Implement and manage security tools, patch management, and access controls.</li>
<li><strong>Junior Penetration Tester:</strong> Assist with security assessments under senior guidance.</li>
</ul>

<h3>Mid-Level Roles (3-5 Years)</h3>
<ul>
<li><strong>Security Engineer:</strong> Design and build security solutions. Average salary: $95,000-$130,000.</li>
<li><strong>Threat Analyst:</strong> Research emerging threats and develop detection signatures.</li>
<li><strong>Incident Response Analyst:</strong> Lead investigations and coordinate breach response.</li>
</ul>

<h3>Senior Roles (5+ Years)</h3>
<ul>
<li><strong>Security Architect:</strong> Design enterprise security frameworks. Average salary: $140,000-$190,000.</li>
<li><strong>CISO:</strong> Chief Information Security Officer â€” executive leadership of security program.</li>
<li><strong>Principal Security Researcher:</strong> Discover vulnerabilities and advance the field.</li>
</ul>

<h3>Essential Certifications</h3>
<p><strong>Beginner:</strong> CompTIA Security+, Google Cybersecurity Certificate</p>
<p><strong>Intermediate:</strong> CEH, CySA+, CCNA Security</p>
<p><strong>Advanced:</strong> CISSP, OSCP, GIAC certifications</p>

<h3>Getting Started Today</h3>
<p>Start with free resources: TryHackMe, Hack The Box Academy, and Fox Lab''s training modules. Build a home lab, contribute to open-source security projects, and network with the community. The cybersecurity community is remarkably welcoming to newcomers.</p>',
 'Education', 'Fox Lab', 'Fox Lab', 'IMGS/blog/career-paths.svg', 9, 0, '2025-12-10');

-- ----------------------------------------------------------
-- 4. Projects (Online Compiler)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT DEFAULT NULL,
    filename    VARCHAR(255) NOT NULL,
    language    VARCHAR(50) DEFAULT 'python',
    code        LONGTEXT,
    is_recent   TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO projects (filename, language, code, is_recent) VALUES
('HelloWorld.py', 'python', 'def greet(name):\n    return f\"Hello, {name}!\"\n\nif __name__ == \"__main__\":\n    name = \"Fox Lab\"\n    message = greet(name)\n    print(message)', 0),
('Calculator.py', 'python', '# Simple Calculator\ndef add(a, b):\n    return a + b\n\ndef subtract(a, b):\n    return a - b\n\ndef multiply(a, b):\n    return a * b\n\nprint(\"5 + 3 =\", add(5, 3))\nprint(\"10 - 4 =\", subtract(10, 4))\nprint(\"6 * 7 =\", multiply(6, 7))', 0),
('FizzBuzz.py', 'python', '# FizzBuzz Challenge\nfor i in range(1, 21):\n    if i % 15 == 0:\n        print(\"FizzBuzz\")\n    elif i % 3 == 0:\n        print(\"Fizz\")\n    elif i % 5 == 0:\n        print(\"Buzz\")\n    else:\n        print(i)', 0),
('HelloWorld.java', 'java', 'public class HelloWorld {\n    public static void main(String[] args) {\n        System.out.println(\"Hello from Fox Lab!\");\n        System.out.println(\"Welcome to Java programming.\");\n    }\n}', 0),
('SortAlgo.java', 'java', 'public class SortAlgo {\n    public static void bubbleSort(int[] arr) {\n        int n = arr.length;\n        for (int i = 0; i < n - 1; i++) {\n            for (int j = 0; j < n - i - 1; j++) {\n                if (arr[j] > arr[j + 1]) {\n                    int temp = arr[j];\n                    arr[j] = arr[j + 1];\n                    arr[j + 1] = temp;\n                }\n            }\n        }\n    }\n\n    public static void main(String[] args) {\n        int[] arr = {64, 34, 25, 12, 22, 11, 90};\n        bubbleSort(arr);\n        for (int val : arr) {\n            System.out.print(val + \" \");\n        }\n        System.out.println();\n    }\n}', 1),
('DataParser.py', 'python', '# Data Parser\nimport json\n\ndata = {\"name\": \"Fox Lab\", \"type\": \"Cybersecurity\", \"tools\": [\"Python\", \"Java\"]}\n\nprint(\"Platform:\", data[\"name\"])\nprint(\"Type:\", data[\"type\"])\nprint(\"Tools:\", \", \".join(data[\"tools\"]))\nprint(\"JSON:\", json.dumps(data, indent=2))', 1);

-- ----------------------------------------------------------
-- 5. Security Logs (Password Checker)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS pw_logs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT DEFAULT NULL,
    strength_level  VARCHAR(50) NOT NULL,
    char_count      INT DEFAULT 0,
    has_uppercase   TINYINT(1) DEFAULT 0,
    has_lowercase   TINYINT(1) DEFAULT 0,
    has_numbers     TINYINT(1) DEFAULT 0,
    has_symbols     TINYINT(1) DEFAULT 0,
    is_compromised  TINYINT(1) DEFAULT 0,
    ip_address      VARCHAR(45) DEFAULT NULL,
    checked_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 6. Phishing Scenarios
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS scenarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    sender_email    VARCHAR(255) NOT NULL,
    sender_name     VARCHAR(200) DEFAULT NULL,
    recipient_email VARCHAR(255) DEFAULT 'john.doe@company.com',
    subject         VARCHAR(500) NOT NULL,
    email_date      DATETIME DEFAULT NULL,
    body_html       LONGTEXT NOT NULL,
    cta_text        VARCHAR(200) DEFAULT NULL,
    is_phishing     TINYINT(1) NOT NULL DEFAULT 1,
    difficulty      ENUM('easy','medium','hard') DEFAULT 'easy',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS red_flags (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    scenario_id     INT NOT NULL,
    flag_title      VARCHAR(255) NOT NULL,
    flag_description TEXT NOT NULL,
    flag_icon       VARCHAR(100) DEFAULT 'fas fa-exclamation-triangle',
    FOREIGN KEY (scenario_id) REFERENCES scenarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS indicators (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    scenario_id     INT NOT NULL,
    indicator_text  VARCHAR(255) NOT NULL,
    is_correct      TINYINT(1) DEFAULT 0,
    FOREIGN KEY (scenario_id) REFERENCES scenarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO scenarios (sender_email, sender_name, recipient_email, subject, email_date, body_html, cta_text, is_phishing, difficulty) VALUES
('security@paypaI-verification.com', 'PayPal Security Team', 'john.doe@company.com',
 'Urgent: Verify Your PayPal Account â€“ Action Required NOW!!!',
 '2026-01-05 14:34:00',
 '<p class="greeting">Dear Valued Customers,</p><p>We have detected unusual activity on your PayPal account. For your security, we have temporarily limited access to your account.</p><p>To restore full access to your account, please verify your identity by click the button below:</p>',
 'Verify Account Now', 1, 'easy'),

('it-support@microsoft.com', 'Microsoft IT Support', 'john.doe@company.com',
 'Your Microsoft 365 License Expires Today',
 '2026-01-10 09:15:00',
 '<p>Dear User,</p><p>Your Microsoft 365 subscription has expired. To avoid losing access to your files and emails, please renew your subscription immediately by clicking the link below.</p><p>If you do not renew within 24 hours, all your data will be permanently deleted.</p>',
 'Renew Now', 1, 'medium'),

('newsletter@techcrunch.com', 'TechCrunch Daily', 'john.doe@company.com',
 'Your Daily Tech Digest â€“ January 12, 2026',
 '2026-01-12 07:00:00',
 '<p>Good morning,</p><p>Here are today\'s top tech stories curated just for you. From the latest in AI developments to startup funding news, stay informed with our daily digest.</p><p>Thank you for being a valued subscriber.</p>',
 'Read More Stories', 0, 'easy'),

('support@amaz0n-orders.com', 'Amazon Customer Service', 'john.doe@company.com',
 'Your Amazon Order #112-4938571-2948301 Has Been Placed',
 '2026-01-18 11:22:00',
 '<p>Hello,</p><p>Thank you for your recent purchase! Your order of <strong>iPhone 16 Pro Max</strong> ($1,299.99) has been confirmed.</p><p>If you did not place this order, please click the link below immediately to cancel and secure your account. You have 2 hours before the package ships.</p>',
 'Cancel Order Now', 1, 'easy'),

('no-reply@linkedin.com', 'LinkedIn', 'john.doe@company.com',
 'You appeared in 15 searches this week',
 '2026-01-20 08:00:00',
 '<p>Hi John,</p><p>You appeared in 15 searches this week. Your profile is getting noticed! See who viewed your profile and discover new career opportunities.</p><p>Keep your profile updated to maximize visibility.</p>',
 'See All Views', 0, 'easy'),

('hr-department@g00gle-careers.net', 'Google HR Department', 'john.doe@company.com',
 'Job Offer: Senior Software Engineer â€“ $250,000/year',
 '2026-01-22 16:45:00',
 '<p>Dear Applicant,</p><p>Congratulations! After reviewing your resume on Indeed, we are pleased to extend a job offer for the position of <strong>Senior Software Engineer</strong> at Google with an annual salary of $250,000.</p><p>To proceed with your onboarding, please fill out the attached form with your personal details, social security number, and banking information for direct deposit setup.</p>',
 'Accept Offer & Submit Details', 1, 'medium'),

('noreply@github.com', 'GitHub', 'john.doe@company.com',
 '[GitHub] A new personal access token has been added to your account',
 '2026-01-25 13:10:00',
 '<p>Hey john-doe,</p><p>A fine-grained personal access token (<strong>CI/CD Pipeline Token</strong>) was recently added to your account.</p><p><strong>Permissions:</strong> Repository access (read/write), Actions (read/write)</p><p>If this was you, no further action is needed. If you did not create this token, please revoke it immediately in your token settings.</p>',
 'View Token Settings', 0, 'medium'),

('billing@netf1ix-account.com', 'Netflix Billing', 'john.doe@company.com',
 'Payment Failed â€“ Your Netflix Account Will Be Suspended',
 '2026-02-01 10:30:00',
 '<p>Dear Customer,</p><p>We were unable to process your last payment. Your Netflix subscription will be suspended within 48 hours unless you update your payment information.</p><p>Please update your billing details immediately to continue enjoying unlimited streaming.</p><p>Note: For security reasons, we require you to re-enter your full credit card details.</p>',
 'Update Payment Now', 1, 'easy'),

('team@slack.com', 'Slack', 'john.doe@company.com',
 'Your weekly Slack digest',
 '2026-02-03 07:30:00',
 '<p>Hi John,</p><p>Here\'s a summary of what happened in your Slack workspace this week:</p><ul><li>42 new messages in #general</li><li>12 mentions across 3 channels</li><li>3 new files shared</li></ul><p>Jump back in and catch up with your team.</p>',
 'Open Slack', 0, 'easy'),

('admin@dropb0x-share.com', 'Dropbox Business', 'john.doe@company.com',
 'URGENT: Someone shared a confidential document with you',
 '2026-02-05 14:20:00',
 '<p>Hi,</p><p>Your colleague has shared a confidential document titled <strong>"Q4 Financial Report - RESTRICTED.pdf"</strong> with you via Dropbox.</p><p>This document contains sensitive financial data and requires immediate review. Please sign in to access the file.</p><p>This link will expire in 6 hours for security purposes.</p>',
 'View Document', 1, 'medium'),

('security-alert@apple.com', 'Apple', 'john.doe@company.com',
 'Your Apple ID was used to sign in to iCloud on a new device',
 '2026-02-06 19:45:00',
 '<p>Dear John Doe,</p><p>Your Apple ID (j.doe@icloud.com) was used to sign in to iCloud via a web browser.</p><p><strong>Date and Time:</strong> February 6, 2026, 7:43 PM EST<br><strong>Operating System:</strong> Windows 10<br><strong>Browser:</strong> Chrome</p><p>If this was you, no action is needed. If you don\'t recognize this sign-in, visit iforgot.apple.com to change your password.</p>',
 NULL, 0, 'medium'),

('support@bankofamerica-secure.xyz', 'Bank of America Security', 'john.doe@company.com',
 'Suspicious ATM Withdrawal Detected on Your Account',
 '2026-02-08 06:15:00',
 '<p>Dear Account Holder,</p><p>We detected an unusual ATM withdrawal of <strong>$2,500.00</strong> from your checking account at an unfamiliar location in Lagos, Nigeria.</p><p>If this was not you, please verify your identity immediately by clicking the secure link below. Failure to respond within 12 hours may result in your account being permanently frozen.</p>',
 'Verify Identity Now', 1, 'easy'),

('noreply@zoom.us', 'Zoom', 'john.doe@company.com',
 'Cloud Recording Available: Team Standup â€“ Feb 10',
 '2026-02-10 09:00:00',
 '<p>Hi John Doe,</p><p>Your cloud recording is now available.</p><p><strong>Topic:</strong> Team Standup<br><strong>Date:</strong> Feb 10, 2026 09:00 AM<br><strong>Duration:</strong> 23 minutes</p><p>The recording will be available for 30 days.</p>',
 'View Recording', 0, 'easy'),

('cso@intern4l-security.com', 'Internal IT Security', 'john.doe@company.com',
 'MANDATORY: Annual Security Compliance Training Overdue',
 '2026-02-11 08:00:00',
 '<p>Dear Employee,</p><p>Our records indicate you have NOT completed the mandatory Annual Security Compliance Training. This training was due on January 31, 2026.</p><p>Failure to complete this training within 48 hours will result in temporary suspension of your network access and escalation to your direct manager and HR.</p><p>Click below to begin the training immediately. You will need to provide your employee ID and network credentials to log in.</p>',
 'Start Training Now', 1, 'hard'),

('notifications@figma.com', 'Figma', 'john.doe@company.com',
 'You were mentioned in "Homepage Redesign v3"',
 '2026-02-12 11:30:00',
 '<p>Hey John,</p><p><strong>Sarah Chen</strong> mentioned you in a comment on <strong>Homepage Redesign v3</strong>:</p><blockquote>"@John can you review the updated hero section? I changed the color palette based on our last meeting."</blockquote><p>Reply or react to this comment directly in Figma.</p>',
 'Open in Figma', 0, 'medium');

INSERT INTO red_flags (scenario_id, flag_title, flag_description, flag_icon) VALUES
(1, 'Suspicious Domain', 'The sender domain "paypaI-verification.com" uses a capital "I" instead of lowercase "l" to mimic PayPal\'s official domain.', 'fas fa-exclamation-triangle'),
(1, 'Urgency Tactics', 'The email creates false urgency by threatening account suspension within 24 hours.', 'fas fa-clock'),
(1, 'Suspicious Call-to-Action', 'The "Verify Account Now" button likely leads to a malicious website designed to steal credentials.', 'fas fa-link'),
(2, 'Threatening Language', 'The email threatens permanent data deletion to create panic and force immediate action.', 'fas fa-exclamation-triangle'),
(2, 'Impersonation', 'While the domain looks legitimate, Microsoft would never threaten immediate data deletion.', 'fas fa-user-secret'),
(4, 'Fake Domain', 'The domain "amaz0n-orders.com" uses a zero instead of the letter "o" â€” a classic typosquatting trick.', 'fas fa-exclamation-triangle'),
(4, 'Scare Tactic', 'Claiming a high-value order you didn\'t place to create panic and urgency.', 'fas fa-clock'),
(4, 'Time Pressure', 'Giving a 2-hour deadline to force you to click before thinking critically.', 'fas fa-hourglass-half'),
(6, 'Spoofed Domain', 'The domain "g00gle-careers.net" uses zeros instead of "o" and is not Google\'s real domain (google.com).', 'fas fa-exclamation-triangle'),
(6, 'Too Good to Be True', 'Unsolicited high-salary job offers from major companies are a classic social engineering lure.', 'fas fa-gem'),
(6, 'Requesting Sensitive Data', 'Legitimate employers never ask for SSN or banking information before a formal interview process.', 'fas fa-id-card'),
(8, 'Fake Billing Domain', 'The domain "netf1ix-account.com" uses the number "1" instead of the letter "l" to impersonate Netflix.', 'fas fa-exclamation-triangle'),
(8, 'Requesting Full Card Details', 'Legitimate payment update flows never ask you to re-enter full credit card details via email links.', 'fas fa-credit-card'),
(8, 'Urgency Pressure', 'A 48-hour suspension threat is designed to make you act without verifying.', 'fas fa-clock'),
(10, 'Suspicious Domain', 'The domain "dropb0x-share.com" uses a zero instead of "o" â€” not the real Dropbox domain.', 'fas fa-exclamation-triangle'),
(10, 'Manufactured Urgency', 'A 6-hour expiration on a "confidential" document creates artificial time pressure.', 'fas fa-clock'),
(10, 'Curiosity Bait', 'Using words like "confidential" and "RESTRICTED" exploits curiosity to get you to click.', 'fas fa-eye'),
(12, 'Fake Bank Domain', 'The domain "bankofamerica-secure.xyz" uses a .xyz TLD â€” not Bank of America\'s real domain (bankofamerica.com).', 'fas fa-exclamation-triangle'),
(12, 'Foreign Location Scare', 'Mentioning a withdrawal in Lagos, Nigeria is designed to trigger immediate panic.', 'fas fa-globe'),
(12, 'Account Freeze Threat', 'Threatening to permanently freeze your account within 12 hours forces hasty action.', 'fas fa-lock'),
(14, 'Fake Internal Domain', 'The domain "intern4l-security.com" uses "4" instead of "a" â€” not a real company domain.', 'fas fa-exclamation-triangle'),
(14, 'Authority Impersonation', 'Impersonating IT/Security/HR departments to add legitimacy and fear of consequences.', 'fas fa-user-tie'),
(14, 'Credential Harvesting', 'Asking for employee ID and network credentials is a classic credential phishing setup.', 'fas fa-key'),
(14, 'Consequences Threat', 'Threatening network suspension and HR escalation to force compliance.', 'fas fa-gavel');

INSERT INTO indicators (scenario_id, indicator_text, is_correct) VALUES
(1, 'Suspicious sender domain', 1),
(1, 'Urgent action required', 1),
(1, 'Generic greeting', 1),
(1, 'Suspicious links/buttons', 1),
(1, 'Poor grammar/spelling', 1),
(1, 'Threats of account closure', 1),
(2, 'Suspicious sender domain', 0),
(2, 'Urgent action required', 1),
(2, 'Generic greeting', 1),
(2, 'Suspicious links/buttons', 1),
(2, 'Poor grammar/spelling', 0),
(2, 'Threats of account closure', 1),
(3, 'Suspicious sender domain', 0),
(3, 'Urgent action required', 0),
(3, 'Generic greeting', 0),
(3, 'Suspicious links/buttons', 0),
(3, 'Poor grammar/spelling', 0),
(3, 'Threats of account closure', 0),
(4, 'Suspicious sender domain', 1),
(4, 'Urgent action required', 1),
(4, 'Generic greeting', 0),
(4, 'Suspicious links/buttons', 1),
(4, 'Poor grammar/spelling', 0),
(4, 'Threats of account closure', 0),
(5, 'Suspicious sender domain', 0),
(5, 'Urgent action required', 0),
(5, 'Generic greeting', 0),
(5, 'Suspicious links/buttons', 0),
(5, 'Poor grammar/spelling', 0),
(5, 'Threats of account closure', 0),
(6, 'Suspicious sender domain', 1),
(6, 'Urgent action required', 0),
(6, 'Generic greeting', 1),
(6, 'Suspicious links/buttons', 1),
(6, 'Poor grammar/spelling', 0),
(6, 'Requests personal information', 1),
(7, 'Suspicious sender domain', 0),
(7, 'Urgent action required', 0),
(7, 'Generic greeting', 0),
(7, 'Suspicious links/buttons', 0),
(7, 'Poor grammar/spelling', 0),
(7, 'Threats of account closure', 0),
(8, 'Suspicious sender domain', 1),
(8, 'Urgent action required', 1),
(8, 'Generic greeting', 1),
(8, 'Suspicious links/buttons', 1),
(8, 'Poor grammar/spelling', 0),
(8, 'Requests financial information', 1),
(9, 'Suspicious sender domain', 0),
(9, 'Urgent action required', 0),
(9, 'Generic greeting', 0),
(9, 'Suspicious links/buttons', 0),
(9, 'Poor grammar/spelling', 0),
(9, 'Threats of account closure', 0),
(10, 'Suspicious sender domain', 1),
(10, 'Urgent action required', 1),
(10, 'Generic greeting', 0),
(10, 'Suspicious links/buttons', 1),
(10, 'Poor grammar/spelling', 0),
(10, 'Curiosity/confidentiality bait', 1),
(11, 'Suspicious sender domain', 0),
(11, 'Urgent action required', 0),
(11, 'Generic greeting', 0),
(11, 'Suspicious links/buttons', 0),
(11, 'Poor grammar/spelling', 0),
(11, 'Threats of account closure', 0),
(12, 'Suspicious sender domain', 1),
(12, 'Urgent action required', 1),
(12, 'Generic greeting', 1),
(12, 'Suspicious links/buttons', 1),
(12, 'Poor grammar/spelling', 0),
(12, 'Threats of account closure', 1),
(13, 'Suspicious sender domain', 0),
(13, 'Urgent action required', 0),
(13, 'Generic greeting', 0),
(13, 'Suspicious links/buttons', 0),
(13, 'Poor grammar/spelling', 0),
(13, 'Threats of account closure', 0),
(14, 'Suspicious sender domain', 1),
(14, 'Urgent action required', 1),
(14, 'Generic greeting', 1),
(14, 'Suspicious links/buttons', 1),
(14, 'Requests credentials', 1),
(14, 'Threats of consequences', 1),
(15, 'Suspicious sender domain', 0),
(15, 'Urgent action required', 0),
(15, 'Generic greeting', 0),
(15, 'Suspicious links/buttons', 0),
(15, 'Poor grammar/spelling', 0),
(15, 'Threats of account closure', 0);

-- Phishing user progress tracking
CREATE TABLE IF NOT EXISTS quiz_results (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    scenario_id     INT NOT NULL,
    user_response   VARCHAR(20) NOT NULL,
    is_correct      TINYINT(1) DEFAULT 0,
    answered_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (scenario_id) REFERENCES scenarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 6b. User Term Bookmarks
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookmarks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    term_id     INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bookmark (user_id, term_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 7. Terminologies / Glossary
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS terms (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    pronunciation   VARCHAR(255) DEFAULT NULL,
    category        VARCHAR(100) NOT NULL DEFAULT 'General',
    subcategory     VARCHAR(100) DEFAULT 'Protocols',
    definition      TEXT NOT NULL,
    usage_context   TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS term_links (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    term_id     INT NOT NULL,
    linked_id   INT NOT NULL,
    UNIQUE KEY unique_link (term_id, linked_id),
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_id) REFERENCES terms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS threats (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    term_id     INT NOT NULL,
    threat_title VARCHAR(255) NOT NULL,
    threat_desc  TEXT,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS resources (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    term_id     INT NOT NULL,
    resource_title VARCHAR(255) NOT NULL,
    resource_url   VARCHAR(500) NOT NULL,
    resource_icon  VARCHAR(100) DEFAULT 'fas fa-external-link-alt',
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 7a. Glossary Terms (45 terms, A-Z coverage)
-- ----------------------------------------------------------
INSERT INTO terms (id, title, pronunciation, category, subcategory, definition, usage_context) VALUES

-- A
(1, 'Access Control', '/ËˆÃ¦k.ses kÉ™nËˆtroÊŠl/', 'Protocols', 'Identity & Access',
 'Access Control is a security technique that regulates who or what can view or use resources in a computing environment. It is a fundamental concept in security that minimizes risk to the business or organization.\n\nAccess control mechanisms include discretionary access control (DAC), mandatory access control (MAC), and role-based access control (RBAC). Modern systems often implement attribute-based access control (ABAC) for fine-grained permissions.',
 'Example 1: "The company implemented role-based **access control** to restrict sensitive data access to authorized personnel only."\n\nExample 2: "After the breach, the team tightened **access control** policies to enforce least-privilege principles across all departments."'),

(2, 'Advanced Persistent Threat', '/É™dËˆvÉ‘Ënst pÉ™ËˆsÉªs.tÉ™nt Î¸ret/', 'Threats', 'Attack Types',
 'An Advanced Persistent Threat (APT) is a prolonged and targeted cyberattack in which an intruder gains access to a network and remains undetected for an extended period. APTs typically target high-value organizations such as government agencies, defense contractors, and large corporations.\n\nAPT groups are usually well-funded, often state-sponsored, and employ sophisticated techniques including zero-day exploits, spear phishing, and custom malware.',
 'Example: "State-sponsored **APT** groups targeted critical infrastructure using sophisticated malware that evaded detection for over six months."'),

(3, 'API Security', '/eÉª.piË.aÉª sÉªËˆkjÊŠÉ™.rÉª.ti/', 'Protocols', 'Application Security',
 'API Security refers to the practices, protocols, and tools used to protect Application Programming Interfaces (APIs) from malicious attacks, unauthorized access, and data breaches. It encompasses authentication, authorization, encryption, rate limiting, input validation, and monitoring.\n\nAs APIs serve as the backbone of modern applications and facilitate communication between different software systems, securing them is critical to maintaining the integrity, confidentiality, and availability of data and services.',
 'Example 1: "Our team implemented comprehensive **API security** measures including OAuth 2.0 authentication and JWT tokens to protect our RESTful services."\n\nExample 2: "The security audit revealed vulnerabilities in our **API security** framework, prompting immediate implementation of rate limiting."'),

(4, 'Authentication', '/É”ËËŒÎ¸en.tÉªËˆkeÉª.ÊƒÉ™n/', 'Protocols', 'Identity & Access',
 'Authentication is the process of verifying the identity of a user, device, or system. It ensures that the entity requesting access is who they claim to be. Common authentication factors include something you know (password), something you have (security token), and something you are (biometric).\n\nMulti-factor authentication (MFA) combines two or more factors to significantly improve security.',
 'Example: "Multi-factor **authentication** adds an extra layer of security beyond just passwords, requiring a second verification step."'),

(5, 'Authorization', '/ËŒÉ”Ë.Î¸É™r.aÉªËˆzeÉª.ÊƒÉ™n/', 'Protocols', 'Identity & Access',
 'Authorization is the process of granting or denying specific permissions to an authenticated user, determining what resources they can access and what actions they can perform. It occurs after authentication and defines the scope of access.\n\nCommon authorization models include Role-Based Access Control (RBAC), Attribute-Based Access Control (ABAC), and Policy-Based Access Control (PBAC).',
 'Example: "After **authorization**, the user was granted read-only access to the database while admin users retained full write permissions."'),

(6, 'Attack Vector', '/É™ËˆtÃ¦k Ëˆvek.tÉ™r/', 'Threats', 'Attack Types',
 'An attack vector is a path or method by which a hacker gains unauthorized access to a computer or network to deliver a payload or carry out a malicious action. Understanding attack vectors is essential for building effective defense strategies.\n\nCommon attack vectors include phishing emails, malicious websites, software vulnerabilities, removable media, and insider threats.',
 'Example: "Phishing emails remain one of the most common **attack vectors** used by cybercriminals to infiltrate corporate networks."'),

(7, 'Antivirus', '/ËŒÃ¦n.tiËˆvaÉª.rÉ™s/', 'Tools', 'Endpoint Security',
 'Antivirus software is a program designed to detect, prevent, and remove malicious software (malware) from computer systems. Modern antivirus solutions use signature-based detection, heuristic analysis, and behavioral monitoring to identify threats.\n\nNext-generation antivirus (NGAV) solutions incorporate machine learning and AI for more advanced threat detection capabilities.',
 'Example: "Ensure your **antivirus** software is updated to protect against the latest threats and zero-day vulnerabilities."'),

-- B
(8, 'Botnet', '/ËˆbÉ’t.net/', 'Threats', 'Attack Types',
 'A botnet is a network of compromised computers (bots or zombies) that are controlled remotely by an attacker (botmaster). Botnets are used to perform distributed denial-of-service (DDoS) attacks, send spam, mine cryptocurrency, and steal data.\n\nModern botnets can consist of millions of infected devices, including IoT devices, and communicate through encrypted channels to avoid detection.',
 'Example: "The Mirai **botnet** exploited default credentials on IoT devices to launch massive DDoS attacks against major internet services."'),

(9, 'Brute Force Attack', '/bruËt fÉ”Ërs É™ËˆtÃ¦k/', 'Threats', 'Attack Types',
 'A brute force attack is a trial-and-error method used to decode encrypted data such as passwords or Data Encryption Standard (DES) keys through exhaustive effort rather than employing intellectual strategies.\n\nVariations include dictionary attacks (using common words), credential stuffing (using leaked credentials), and reverse brute force (trying one password against many usernames).',
 'Example: "The attacker used a **brute force attack** to try millions of password combinations against the login portal, prompting the team to implement account lockout policies."'),

(10, 'Backdoor', '/ËˆbÃ¦k.dÉ”Ër/', 'Threats', 'Malware',
 'A backdoor is a method of bypassing normal authentication or encryption in a computer system, product, or embedded device. Backdoors can be installed by attackers after compromising a system, or they may be intentionally built into software by developers.\n\nBackdoors pose a serious security risk as they allow unauthorized access while bypassing security controls.',
 'Example: "The malware installed a **backdoor** that allowed the attacker to maintain persistent access to the compromised server even after the initial vulnerability was patched."'),

-- C
(11, 'CIA Triad', '/siË.aÉª.eÉª ËˆtraÉª.Ã¦d/', 'Concepts', 'Fundamentals',
 'The CIA Triad is a foundational model in information security consisting of three core principles: Confidentiality (ensuring information is accessible only to authorized parties), Integrity (ensuring data accuracy and completeness), and Availability (ensuring information is accessible when needed).\n\nEvery security measure and control can be mapped back to one or more of these three principles.',
 'Example: "When designing our security architecture, we ensured every control addressed at least one pillar of the **CIA Triad** to provide comprehensive protection."'),

(12, 'Cryptography', '/krÉªpËˆtÉ’É¡.rÉ™.fi/', 'Encryption', 'Cryptography',
 'Cryptography is the practice and study of techniques for secure communication in the presence of adversarial behavior. It involves creating and analyzing protocols that prevent third parties from reading private messages.\n\nModern cryptography includes symmetric encryption (AES), asymmetric encryption (RSA), hash functions (SHA-256), and digital signatures.',
 'Example: "The team implemented strong **cryptography** standards including AES-256 encryption for data at rest and TLS 1.3 for data in transit."'),

(13, 'Cross-Site Scripting', '/krÉ’s saÉªt ËˆskrÉªp.tÉªÅ‹/', 'Threats', 'Web Vulnerabilities',
 'Cross-Site Scripting (XSS) is a web security vulnerability that allows an attacker to inject malicious scripts into web pages viewed by other users. XSS attacks occur when an application includes untrusted data in a web page without proper validation or escaping.\n\nThere are three main types: Stored XSS (persistent), Reflected XSS (non-persistent), and DOM-based XSS.',
 'Example: "The penetration test revealed a stored **XSS** vulnerability in the comment section that could be exploited to steal user session cookies."'),

(14, 'CSRF', '/siË.É›s.É‘Ër.É›f/', 'Threats', 'Web Vulnerabilities',
 'Cross-Site Request Forgery (CSRF) is an attack that forces authenticated users to submit unintended requests to a web application. The attacker tricks the user\'s browser into making requests to a site where the user is already authenticated.\n\nCSRF tokens, SameSite cookies, and checking the Origin header are common defenses against these attacks.',
 'Example: "By implementing anti-**CSRF** tokens in all forms, the development team prevented attackers from forging requests on behalf of authenticated users."'),

-- D
(15, 'DDoS Attack', '/diË.diË.É’s É™ËˆtÃ¦k/', 'Threats', 'Attack Types',
 'A Distributed Denial-of-Service (DDoS) attack attempts to disrupt normal traffic to a targeted server, service, or network by overwhelming it with a flood of internet traffic from multiple compromised sources.\n\nDDoS attacks can be volumetric (bandwidth flooding), protocol-based (exploiting network protocols), or application-layer (targeting web server resources). Modern attacks often combine multiple vectors.',
 'Example: "The company experienced a massive **DDoS attack** that peaked at 1.2 Tbps, temporarily taking down their customer-facing services for several hours."'),

(16, 'Data Breach', '/ËˆdeÉª.tÉ™ briËtÊƒ/', 'Concepts', 'Incidents',
 'A data breach is a security incident in which sensitive, protected, or confidential data is copied, transmitted, viewed, stolen, or used by an unauthorized individual. Data breaches can involve personal health information, personally identifiable information, trade secrets, or intellectual property.\n\nOrganizations must typically notify affected individuals and regulatory bodies when a breach occurs, as mandated by laws like GDPR and CCPA.',
 'Example: "The **data breach** exposed 40 million customer records including names, email addresses, and hashed passwords, triggering mandatory disclosure under GDPR."'),

(17, 'Dark Web', '/dÉ‘Ërk web/', 'Concepts', 'Internet',
 'The Dark Web is a part of the internet that is intentionally hidden and requires special software (typically the Tor browser) to access. It exists on overlay networks that use the internet but require specific protocols or authorization to access.\n\nWhile the dark web has legitimate uses for privacy and anonymity, it is also known for hosting illegal marketplaces, stolen data sales, and cybercriminal forums.',
 'Example: "Threat intelligence analysts monitor the **dark web** for stolen corporate credentials and data being sold on underground forums."'),

-- E
(18, 'Encryption', '/ÉªnËˆkrÉªp.ÊƒÉ™n/', 'Encryption', 'Cryptography',
 'Encryption is the process of converting information or data into a code to prevent unauthorized access. It uses algorithms to transform plaintext into ciphertext that can only be decoded with the correct key.\n\nThere are two main types: symmetric encryption (same key for encrypt/decrypt, e.g., AES) and asymmetric encryption (public/private key pair, e.g., RSA). Encryption protects data at rest, in transit, and in use.',
 'Example: "End-to-end **encryption** ensures that only the sender and recipient can read the messages, preventing even the service provider from accessing the content."'),

(19, 'Endpoint Security', '/Ëˆend.pÉ”Éªnt sÉªËˆkjÊŠÉ™.rÉª.ti/', 'Tools', 'Endpoint Security',
 'Endpoint security refers to the practice of securing endpoints or entry points of end-user devices such as desktops, laptops, and mobile devices from being exploited by malicious actors. Endpoint security solutions include antivirus, endpoint detection and response (EDR), and mobile device management (MDM).\n\nModern endpoint security platforms use behavioral analysis and AI to detect threats that signature-based solutions miss.',
 'Example: "After deploying an **endpoint security** solution with EDR capabilities, the SOC gained visibility into all device activities across the corporate network."'),

(20, 'Exploit', '/ÉªkËˆsplÉ”Éªt/', 'Threats', 'Attack Types',
 'An exploit is a piece of software, a chunk of data, or a sequence of commands that takes advantage of a bug or vulnerability in a system to cause unintended behavior. Exploits can be used to gain unauthorized access, escalate privileges, or execute arbitrary code.\n\nExploits can target operating systems, applications, network protocols, or hardware. Zero-day exploits target vulnerabilities that are not yet known to the software vendor.',
 'Example: "The attacker used a known **exploit** in the unpatched Apache server to gain initial access to the internal network."'),

-- F
(21, 'Firewall', '/ËˆfaÉªÉ™r.wÉ”Ël/', 'Tools', 'Network Security',
 'A firewall is a network security device that monitors and filters incoming and outgoing network traffic based on predetermined security rules. Firewalls establish a barrier between a trusted internal network and untrusted external networks.\n\nTypes include packet-filtering firewalls, stateful inspection firewalls, proxy firewalls, and next-generation firewalls (NGFW) that include deep packet inspection and intrusion prevention.',
 'Example: "The **firewall** blocked several suspicious connection attempts from unknown IP addresses located in countries where the company has no business operations."'),

-- G
(22, 'GDPR', '/dÊ’iË.diË.piË.É‘Ër/', 'Compliance', 'Regulations',
 'The General Data Protection Regulation (GDPR) is a comprehensive data privacy law enacted by the European Union that governs how personal data of EU residents is collected, processed, stored, and shared. It came into effect on May 25, 2018.\n\nGDPR grants individuals rights including the right to access their data, the right to be forgotten, data portability, and the right to object to processing. Non-compliance can result in fines up to â‚¬20 million or 4% of global annual revenue.',
 'Example: "To comply with **GDPR**, the organization implemented data minimization practices and established a Data Protection Officer role."'),

-- H
(23, 'Hashing', '/ËˆhÃ¦Êƒ.ÉªÅ‹/', 'Encryption', 'Cryptography',
 'Hashing is the process of converting input data of any size into a fixed-size string of characters using a mathematical function (hash function). Unlike encryption, hashing is a one-way process â€” the original data cannot be recovered from the hash.\n\nCommon hash algorithms include MD5 (deprecated for security), SHA-256, SHA-3, and bcrypt (designed for password hashing). Hashing is used for password storage, data integrity verification, and digital signatures.',
 'Example: "Passwords are stored as salted **hashes** using bcrypt, making it computationally infeasible for attackers to recover the original passwords even if the database is compromised."'),

(24, 'Honeypot', '/ËˆhÊŒn.i.pÉ’t/', 'Tools', 'Deception Technology',
 'A honeypot is a security mechanism set to detect, deflect, or study attempts to gain unauthorized access to information systems. It consists of a computer system or data that appears to be a legitimate part of the network but is actually isolated and monitored.\n\nHoneypots can be low-interaction (simulating services) or high-interaction (running real systems). They provide valuable threat intelligence about attacker techniques and tools.',
 'Example: "The security team deployed several **honeypots** across the network to detect lateral movement attempts and gather intelligence about attacker behavior."'),

-- I
(25, 'Intrusion Detection System', '/ÉªnËˆtruË.Ê’É™n dÉªËˆtek.ÊƒÉ™n ËˆsÉªs.tÉ™m/', 'Tools', 'Network Security',
 'An Intrusion Detection System (IDS) is a device or software application that monitors a network or systems for malicious activity or policy violations. An IDS can be network-based (NIDS) monitoring network traffic, or host-based (HIDS) monitoring system logs and file changes.\n\nAn Intrusion Prevention System (IPS) goes a step further by actively blocking detected threats in addition to alerting on them.',
 'Example: "The network-based **IDS** detected anomalous traffic patterns consistent with a data exfiltration attempt and immediately alerted the SOC team."'),

(26, 'Incident Response', '/ËˆÉªn.sÉª.dÉ™nt rÉªËˆspÉ’ns/', 'Concepts', 'Operations',
 'Incident Response is the organized approach to addressing and managing the aftermath of a security breach or cyberattack. The goal is to handle the situation in a way that limits damage, reduces recovery time and costs, and prevents future incidents.\n\nThe NIST Incident Response framework includes four phases: Preparation, Detection & Analysis, Containment Eradication & Recovery, and Post-Incident Activity.',
 'Example: "The **incident response** team contained the ransomware attack within 30 minutes by isolating affected systems, preventing lateral spread across the network."'),

-- K
(27, 'Keylogger', '/ËˆkiË.lÉ’É¡.É™r/', 'Threats', 'Malware',
 'A keylogger is a type of surveillance software or hardware that records every keystroke made on a computer or mobile device. Keyloggers can capture passwords, credit card numbers, messages, and other sensitive data.\n\nKeyloggers can be software-based (installed as malware) or hardware-based (physical devices attached to the keyboard). They are commonly used in targeted attacks and corporate espionage.',
 'Example: "The forensic analysis revealed that a **keylogger** had been installed through a phishing email, capturing the employee\'s credentials for three weeks before detection."'),

-- M
(28, 'Malware', '/ËˆmÃ¦l.weÉ™r/', 'Threats', 'Malware',
 'Malware (malicious software) is any software intentionally designed to cause disruption, damage, or gain unauthorized access to computer systems. Major types include viruses, worms, trojans, ransomware, spyware, adware, and rootkits.\n\nModern malware often uses polymorphic code to evade detection, fileless techniques to hide in memory, and encrypted communication channels to exfiltrate data.',
 'Example: "The **malware** infected the system through a compromised email attachment and proceeded to encrypt all files on connected network shares."'),

(29, 'Man-in-the-Middle Attack', '/mÃ¦n Éªn Ã°É™ ËˆmÉªd.É™l É™ËˆtÃ¦k/', 'Threats', 'Attack Types',
 'A Man-in-the-Middle (MitM) attack occurs when an attacker secretly intercepts and potentially alters communication between two parties who believe they are communicating directly with each other.\n\nMitM attacks can occur through compromised Wi-Fi networks, ARP spoofing, DNS spoofing, or SSL stripping. HTTPS, certificate pinning, and VPNs help defend against these attacks.',
 'Example: "The attacker performed a **man-in-the-middle attack** on the public Wi-Fi network, intercepting unencrypted login credentials from unsuspecting users."'),

-- N
(30, 'Network Segmentation', '/Ëˆnet.wÉœËrk ËŒseÉ¡.menËˆteÉª.ÊƒÉ™n/', 'Concepts', 'Network Security',
 'Network segmentation is the practice of dividing a computer network into smaller subnetworks (segments) to improve security and performance. Each segment can have its own security policies and access controls.\n\nMicro-segmentation takes this further by creating secure zones within data centers and cloud environments, restricting lateral movement between workloads.',
 'Example: "By implementing **network segmentation**, the team ensured that a breach in the guest Wi-Fi network could not spread to the production servers."'),

-- P
(31, 'Penetration Testing', '/ËŒpen.ÉªËˆtreÉª.ÊƒÉ™n Ëˆtes.tÉªÅ‹/', 'Concepts', 'Operations',
 'Penetration testing (pen testing) is a simulated cyberattack against a computer system, network, or web application to find security vulnerabilities that an attacker could exploit. It helps organizations identify and fix weaknesses before malicious actors discover them.\n\nTypes include black box (no prior knowledge), white box (full knowledge), and gray box (partial knowledge). Common methodologies include OWASP, PTES, and NIST SP 800-115.',
 'Example: "The annual **penetration test** uncovered a critical SQL injection vulnerability in the customer portal that could have exposed the entire user database."'),

(32, 'Phishing', '/ËˆfÉªÊƒ.ÉªÅ‹/', 'Threats', 'Social Engineering',
 'Phishing is a type of social engineering attack where attackers disguise themselves as trustworthy entities to trick individuals into revealing sensitive information such as login credentials, credit card numbers, or personal data.\n\nVariants include spear phishing (targeted), whaling (targeting executives), smishing (SMS-based), and vishing (voice-based). Phishing remains the most common initial attack vector in data breaches.',
 'Example: "The employee fell victim to a **phishing** attack that mimicked the company\'s login page, exposing credentials that led to a major data breach."'),

-- R
(33, 'Ransomware', '/ËˆrÃ¦n.sÉ™m.weÉ™r/', 'Threats', 'Malware',
 'Ransomware is a type of malware that encrypts a victim\'s files or locks them out of their system, demanding a ransom payment (typically in cryptocurrency) to restore access. Modern ransomware often employs double extortion â€” encrypting data and threatening to publish it.\n\nNotable ransomware families include WannaCry, REvil, LockBit, and BlackCat. Defense strategies include regular backups, network segmentation, and endpoint protection.',
 'Example: "The hospital was hit by a **ransomware** attack that encrypted patient records, forcing them to divert emergency patients while the incident response team worked to restore systems."'),

(34, 'Risk Assessment', '/rÉªsk É™Ëˆses.mÉ™nt/', 'Concepts', 'Governance',
 'Risk assessment is the process of identifying, analyzing, and evaluating potential risks to an organization\'s information assets. It helps organizations prioritize security investments based on the likelihood and potential impact of different threats.\n\nFrameworks for risk assessment include NIST SP 800-30, ISO 27005, and FAIR (Factor Analysis of Information Risk).',
 'Example: "The annual **risk assessment** identified cloud misconfiguration as the highest-priority risk, leading to immediate remediation efforts and enhanced monitoring."'),

-- S
(35, 'Social Engineering', '/ËˆsoÊŠ.ÊƒÉ™l ËŒen.dÊ’ÉªËˆnÉªr.ÉªÅ‹/', 'Threats', 'Social Engineering',
 'Social engineering is the art of manipulating people so they give up confidential information or take actions that compromise security. It relies on human psychology rather than technical hacking techniques.\n\nTechniques include pretexting, baiting, quid pro quo, tailgating, and phishing. Security awareness training is the primary defense against social engineering attacks.',
 'Example: "The attacker used **social engineering** techniques to convince the help desk to reset the CEO\'s password, gaining full access to executive communications."'),

(36, 'SQL Injection', '/ËŒes.kjuË.Ëˆel ÉªnËˆdÊ’ek.ÊƒÉ™n/', 'Threats', 'Web Vulnerabilities',
 'SQL Injection (SQLi) is a web security vulnerability that allows an attacker to interfere with the queries that an application makes to its database. It can allow attackers to view, modify, or delete data they are not authorized to access.\n\nTypes include in-band SQLi (error-based and union-based), blind SQLi (boolean-based and time-based), and out-of-band SQLi. Parameterized queries and input validation are primary defenses.',
 'Example: "The web application was vulnerable to **SQL injection** because user input was concatenated directly into database queries without proper sanitization."'),

(37, 'SSL/TLS', '/ËŒes.es.Ëˆel tiË.el.Ëˆes/', 'Encryption', 'Protocols',
 'SSL (Secure Sockets Layer) and TLS (Transport Layer Security) are cryptographic protocols designed to provide secure communications over a computer network. TLS is the successor to SSL and is the standard for securing web traffic (HTTPS).\n\nTLS 1.3, the latest version, provides improved security and performance by eliminating legacy algorithms and reducing the handshake to a single round trip.',
 'Example: "The website uses **TLS 1.3** encryption to secure all data transmitted between the browser and server, protecting against eavesdropping and tampering."'),

(38, 'SIEM', '/sÉªm/', 'Tools', 'Security Operations',
 'Security Information and Event Management (SIEM) is a solution that provides real-time analysis of security alerts generated by applications and network hardware. SIEM systems collect and aggregate log data from across the organization, detect anomalies, and trigger alerts.\n\nModern SIEM platforms integrate with SOAR (Security Orchestration, Automation, and Response) tools for automated incident response. Popular SIEM solutions include Splunk, Microsoft Sentinel, and IBM QRadar.',
 'Example: "The **SIEM** correlated log events from the firewall, endpoint agents, and authentication servers to detect a coordinated attack in real-time."'),

-- T
(39, 'Threat Intelligence', '/Î¸ret ÉªnËˆtel.Éª.dÊ’É™ns/', 'Concepts', 'Operations',
 'Threat intelligence is evidence-based knowledge about existing or emerging threats to an organization\'s information assets. It includes context, indicators of compromise (IOCs), implications, and actionable advice that inform decisions about responding to threats.\n\nThreat intelligence is categorized into strategic (high-level trends), tactical (TTPs), operational (specific attacks), and technical (IOCs like IPs, hashes, domains).',
 'Example: "The **threat intelligence** team identified indicators of compromise associated with the APT group, enabling proactive blocking before the attack reached production systems."'),

(40, 'Two-Factor Authentication', '/tuË ËˆfÃ¦k.tÉ™r É”ËËŒÎ¸en.tÉªËˆkeÉª.ÊƒÉ™n/', 'Protocols', 'Identity & Access',
 'Two-Factor Authentication (2FA) is a security process in which users provide two different authentication factors to verify themselves. This adds an additional layer of security beyond just a username and password.\n\nCommon second factors include SMS codes, authenticator app codes (TOTP), hardware security keys (FIDO2/WebAuthn), and biometrics. Hardware keys provide the strongest protection against phishing.',
 'Example: "After enabling **two-factor authentication** with a hardware security key, account takeover attempts were reduced to zero across the organization."'),

-- V
(41, 'VPN', '/viË.piË.Ëˆen/', 'Tools', 'Network Security',
 'A Virtual Private Network (VPN) creates a secure, encrypted connection over a less secure network such as the public internet. VPNs are used to protect data in transit, mask IP addresses, and provide secure remote access to corporate networks.\n\nTypes include remote access VPN (for individual users), site-to-site VPN (connecting networks), and SSL/TLS VPN (browser-based). WireGuard and OpenVPN are popular open-source protocols.',
 'Example: "All remote employees are required to connect through the corporate **VPN** to ensure their traffic is encrypted and they can securely access internal resources."'),

(42, 'Vulnerability', '/ËŒvÊŒl.nÉ™r.É™ËˆbÉªl.Éª.ti/', 'Concepts', 'Fundamentals',
 'A vulnerability is a weakness or flaw in a system\'s design, implementation, or configuration that can be exploited by a threat actor to gain unauthorized access or cause harm. Vulnerabilities can exist in software, hardware, processes, or people.\n\nThe Common Vulnerabilities and Exposures (CVE) system provides a reference for publicly known vulnerabilities, while the CVSS scoring system rates their severity from 0 to 10.',
 'Example: "The vulnerability scanner identified a critical **vulnerability** (CVSS 9.8) in the production web server that required immediate patching."'),

-- W
(43, 'Worm', '/wÉœËrm/', 'Threats', 'Malware',
 'A computer worm is a type of malware that self-replicates and spreads across networks without requiring human interaction or a host program. Unlike viruses, worms do not need to attach to existing programs and can propagate automatically by exploiting vulnerabilities.\n\nNotable worms include Code Red, Slammer, Conficker, and WannaCry (which combined worm and ransomware capabilities).',
 'Example: "The **worm** exploited an unpatched SMB vulnerability to spread across the entire network within minutes, affecting over 300,000 computers worldwide."'),

-- Z
(44, 'Zero-Day', '/ËˆzÉªÉ™.roÊŠ deÉª/', 'Threats', 'Attack Types',
 'A zero-day vulnerability is a software security flaw that is unknown to the vendor and for which no patch exists. A zero-day exploit takes advantage of such vulnerabilities before the developer has had a chance to create a fix.\n\nZero-days are particularly dangerous because traditional signature-based security tools cannot detect them. They are highly valued on both legitimate (bug bounty) and black markets.',
 'Example: "The attackers leveraged a **zero-day** exploit in the email server software, gaining access before the vendor was even aware the vulnerability existed."'),

(45, 'Zero Trust', '/ËˆzÉªÉ™.roÊŠ trÊŒst/', 'Concepts', 'Architecture',
 'Zero Trust is a security framework based on the principle of "never trust, always verify." Unlike traditional perimeter-based security, Zero Trust assumes that threats exist both inside and outside the network and requires strict verification for every user and device.\n\nKey principles include least-privilege access, micro-segmentation, continuous verification, and assuming breach. NIST SP 800-207 provides the standard architecture for Zero Trust.',
 'Example: "By adopting a **Zero Trust** architecture, the organization eliminated implicit trust zones and required all users to authenticate and authorize for every resource access."'),

-- ============================================================
-- COMPLIANCE (new terms 46-55)
-- ============================================================

(46, 'HIPAA', '/ËˆhÉªp.É™/', 'Compliance', 'Regulations',
 'The Health Insurance Portability and Accountability Act (HIPAA) is a United States federal law enacted in 1996 that establishes national standards for the protection of sensitive patient health information. It applies to healthcare providers, health plans, and healthcare clearinghouses (covered entities).\n\nHIPAA includes the Privacy Rule (how PHI can be used/disclosed), the Security Rule (safeguards for electronic PHI), and the Breach Notification Rule (requirements when PHI is compromised). Penalties for violations can reach up to $1.5 million per incident category per year.',
 'Example: "The hospital implemented encryption for all electronic patient records to comply with **HIPAA** Security Rule requirements and protect against unauthorized access."'),

(47, 'PCI DSS', '/piË.siË.aÉª diË.É›s.É›s/', 'Compliance', 'Standards',
 'The Payment Card Industry Data Security Standard (PCI DSS) is a set of security requirements designed to ensure that all companies that accept, process, store, or transmit credit card information maintain a secure environment. PCI DSS is administered by the PCI Security Standards Council.\n\nPCI DSS v4.0 includes 12 core requirements organized into six categories: build and maintain a secure network, protect cardholder data, maintain a vulnerability management program, implement strong access control measures, regularly monitor and test networks, and maintain an information security policy.',
 'Example: "The e-commerce platform achieved **PCI DSS** Level 1 compliance by implementing tokenization, point-to-point encryption, and quarterly vulnerability scans."'),

(48, 'SOC 2', '/sÉ’k tuË/', 'Compliance', 'Auditing',
 'SOC 2 (System and Organization Controls 2) is an auditing framework developed by the American Institute of CPAs (AICPA) that evaluates an organization\'s controls relevant to security, availability, processing integrity, confidentiality, and privacy â€” known as the Trust Services Criteria.\n\nSOC 2 reports come in two types: Type I (point-in-time assessment of control design) and Type II (assessment of control effectiveness over a period, typically 6â€“12 months). SOC 2 certification is increasingly required by enterprise customers when selecting SaaS vendors.',
 'Example: "Our SaaS company completed a **SOC 2** Type II audit to demonstrate to enterprise clients that our security controls are properly designed and operating effectively."'),

(49, 'ISO 27001', '/ËŒaÉª.É›s.oÊŠ twÉ›n.tiËŒsÉ›v.É™n ËˆÎ¸aÊŠ.zÉ™nd wÊŒn/', 'Compliance', 'Standards',
 'ISO/IEC 27001 is an international standard for information security management systems (ISMS). Published by the International Organization for Standardization (ISO) and the International Electrotechnical Commission (IEC), it provides a systematic approach to managing sensitive company information through risk management processes.\n\nISO 27001 certification requires organizations to establish, implement, maintain, and continually improve an ISMS. The standard is complemented by ISO 27002, which provides best practice recommendations for information security controls.',
 'Example: "Achieving **ISO 27001** certification positioned the company as a trusted partner, demonstrating a mature and internationally recognized approach to information security management."'),

(50, 'NIST Framework', '/nÉªst ËˆfreÉªm.wÉœËrk/', 'Compliance', 'Frameworks',
 'The NIST Cybersecurity Framework (CSF) is a voluntary set of guidelines developed by the National Institute of Standards and Technology to help organizations manage and reduce cybersecurity risk. The framework is widely adopted across industries and government agencies.\n\nNIST CSF 2.0 organizes cybersecurity activities into six core functions: Govern, Identify, Protect, Detect, Respond, and Recover. Each function contains categories and subcategories mapped to existing standards and best practices.',
 'Example: "The organization adopted the **NIST Cybersecurity Framework** to structure its security program around the five core functions and identify gaps in their current controls."'),

(51, 'CCPA', '/siË.siË.piË.eÉª/', 'Compliance', 'Regulations',
 'The California Consumer Privacy Act (CCPA), effective since January 2020, is a state-level data privacy law that grants California residents specific rights over their personal information. It was later amended and strengthened by the California Privacy Rights Act (CPRA) in 2023.\n\nCCPA grants consumers the right to know what personal data is collected, the right to delete it, the right to opt out of the sale of personal information, and the right to non-discrimination for exercising these rights. It applies to businesses meeting certain thresholds of revenue or data handling volume.',
 'Example: "To comply with the **CCPA**, the company added a \'Do Not Sell My Personal Information\' link on their website and implemented data deletion request workflows."'),

(52, 'SOX Compliance', '/sÉ’ks kÉ™mËˆplaÉª.É™ns/', 'Compliance', 'Regulations',
 'The Sarbanes-Oxley Act (SOX) is a United States federal law enacted in 2002 that established requirements for financial reporting and corporate governance to protect investors from fraudulent accounting activities. SOX applies to all publicly traded companies in the US and their auditors.\n\nFrom an IT security perspective, SOX Section 404 requires management to assess the effectiveness of internal controls over financial reporting, including access controls to financial systems, audit logging, change management, and data backup and recovery procedures.',
 'Example: "The IT team implemented strict access controls and comprehensive audit logs on all financial systems to satisfy **SOX** Section 404 internal control requirements."'),

(53, 'FERPA', '/ËˆfÉœËr.pÉ™/', 'Compliance', 'Regulations',
 'The Family Educational Rights and Privacy Act (FERPA) is a United States federal law that protects the privacy of student education records. It applies to all educational institutions that receive federal funding and gives parents and eligible students rights over their educational records.\n\nFERPA requires institutions to obtain written consent before disclosing personally identifiable information from education records, with limited exceptions such as directory information and legitimate educational interest by school officials.',
 'Example: "The university upgraded its student information system security to ensure **FERPA** compliance, implementing role-based access to prevent unauthorized viewing of student records."'),

(54, 'CMMC', '/siË.É›m.É›m.siË/', 'Compliance', 'Frameworks',
 'The Cybersecurity Maturity Model Certification (CMMC) is a framework developed by the U.S. Department of Defense (DoD) to assess and enhance the cybersecurity posture of the Defense Industrial Base (DIB). CMMC 2.0 streamlined the original five levels into three.\n\nCMMC Level 1 requires basic cyber hygiene (17 practices), Level 2 aligns with NIST SP 800-171 (110 practices), and Level 3 requires enhanced security for protecting against APTs. Third-party assessments are required for Levels 2 and 3.',
 'Example: "The defense contractor invested heavily in upgrading their security infrastructure to achieve **CMMC** Level 2 certification, a requirement for bidding on DoD contracts."'),

(55, 'Data Privacy', '/ËˆdeÉª.tÉ™ ËˆprÉªv.É™.si/', 'Compliance', 'Governance',
 'Data privacy refers to the proper handling, processing, storage, and usage of personal information in accordance with regulations, consent, and reasonable expectations. It encompasses an individual\'s right to control how their personal data is collected, used, shared, and retained.\n\nData privacy is governed by a patchwork of international laws including GDPR (EU), CCPA (California), LGPD (Brazil), POPIA (South Africa), and PDPA (Singapore). Privacy by Design and Privacy Impact Assessments are key methodologies for building privacy into systems.',
 'Example: "The company appointed a Data Protection Officer and conducted Privacy Impact Assessments to embed **data privacy** principles into every new product development cycle."'),

-- ============================================================
-- ENCRYPTION (new terms 56-65)
-- ============================================================

(56, 'AES', '/eÉª.iË.É›s/', 'Encryption', 'Algorithms',
 'The Advanced Encryption Standard (AES) is a symmetric block cipher adopted by the U.S. government and widely used worldwide for encrypting sensitive data. AES was established by NIST in 2001 as FIPS 197, replacing the older Data Encryption Standard (DES).\n\nAES operates on 128-bit blocks and supports key sizes of 128, 192, and 256 bits. AES-256 is considered suitable for protecting classified information up to the TOP SECRET level. Common modes of operation include CBC, GCM (providing authenticated encryption), and CTR.',
 'Example: "All customer data at rest is protected with **AES-256** encryption in GCM mode, providing both confidentiality and integrity protection for stored records."'),

(57, 'RSA Encryption', '/É‘Ër.É›s.eÉª ÉªnËˆkrÉªp.ÊƒÉ™n/', 'Encryption', 'Algorithms',
 'RSA (Rivest-Shamir-Adleman) is an asymmetric cryptographic algorithm used for secure data transmission. It relies on the mathematical difficulty of factoring the product of two large prime numbers. RSA is one of the first public-key cryptosystems and is widely used for secure key exchange and digital signatures.\n\nRSA key sizes of 2048 bits or larger are recommended for adequate security. While RSA is slower than symmetric algorithms, it solves the key distribution problem and is commonly used in TLS handshakes to negotiate symmetric session keys.',
 'Example: "The system uses **RSA-2048** for the initial key exchange during the TLS handshake, then switches to AES for fast symmetric encryption of the session data."'),

(58, 'Public Key Infrastructure', '/ËˆpÊŒb.lÉªk kiË ËˆÉªn.frÉ™.strÊŒk.tÊƒÉ™r/', 'Encryption', 'Infrastructure',
 'Public Key Infrastructure (PKI) is a framework of policies, procedures, hardware, software, and people needed to create, manage, distribute, use, store, and revoke digital certificates. PKI enables secure electronic communication by binding public keys to entities through certificate authorities (CAs).\n\nPKI components include Certificate Authorities (CAs), Registration Authorities (RAs), certificate revocation lists (CRLs), and Online Certificate Status Protocol (OCSP). PKI underpins HTTPS, email encryption (S/MIME), code signing, and VPN authentication.',
 'Example: "The enterprise deployed an internal **PKI** to issue digital certificates for employee authentication, device identity, and encrypted email communication."'),

(59, 'End-to-End Encryption', '/É›nd tuË É›nd ÉªnËˆkrÉªp.ÊƒÉ™n/', 'Encryption', 'Protocols',
 'End-to-End Encryption (E2EE) is a method of secure communication where only the communicating parties can read the messages. In E2EE, data is encrypted on the sender\'s device and only decrypted on the recipient\'s device, preventing intermediaries â€” including the service provider â€” from accessing the plaintext.\n\nE2EE is used in messaging apps like Signal and WhatsApp, and is based on protocols such as the Signal Protocol (Double Ratchet Algorithm). Challenges include key management, multi-device support, and key verification.',
 'Example: "The messaging platform implemented **end-to-end encryption** using the Signal Protocol, ensuring that even their own servers cannot read user messages."'),

(60, 'Symmetric Encryption', '/sÉªËˆmet.rÉªk ÉªnËˆkrÉªp.ÊƒÉ™n/', 'Encryption', 'Cryptography',
 'Symmetric encryption is a type of encryption where the same key is used for both encrypting and decrypting data. It is significantly faster than asymmetric encryption, making it the preferred choice for encrypting large volumes of data.\n\nCommon symmetric algorithms include AES (current standard), ChaCha20 (used in mobile/streaming), 3DES (legacy, being phased out), and Blowfish. The primary challenge is secure key distribution â€” both parties must share the secret key through a secure channel.',
 'Example: "The database uses **symmetric encryption** with AES-256 to protect sensitive fields, achieving high-speed encryption and decryption of millions of records."'),

(61, 'Asymmetric Encryption', '/ËŒeÉª.sÉªËˆmet.rÉªk ÉªnËˆkrÉªp.ÊƒÉ™n/', 'Encryption', 'Cryptography',
 'Asymmetric encryption (public-key cryptography) uses a pair of mathematically related keys: a public key for encryption and a private key for decryption. Anyone can encrypt data with the public key, but only the holder of the private key can decrypt it.\n\nCommon asymmetric algorithms include RSA, Elliptic Curve Cryptography (ECC), and Diffie-Hellman key exchange. Asymmetric encryption is slower than symmetric but solves the key distribution problem. It is commonly used for digital signatures, key exchange, and authentication.',
 'Example: "The application uses **asymmetric encryption** so that users can submit encrypted reports using the organization\'s public key, which only the private key holder can decrypt."'),

(62, 'Digital Signature', '/ËˆdÉªdÊ’.Éª.tÉ™l ËˆsÉªÉ¡.nÉ™.tÊƒÉ™r/', 'Encryption', 'Authentication',
 'A digital signature is a mathematical scheme for verifying the authenticity and integrity of digital messages or documents. It uses asymmetric cryptography â€” the signer creates a signature with their private key, and anyone can verify it using the signer\'s public key.\n\nDigital signatures provide authentication (proof of signer identity), integrity (proof data has not been altered), and non-repudiation (signer cannot deny signing). Common standards include RSA-PSS, ECDSA, and EdDSA. They are used in code signing, email (S/MIME), PDF documents, and blockchain transactions.',
 'Example: "All software updates are protected with **digital signatures** so that devices automatically verify the update came from the legitimate vendor before installation."'),

(63, 'Key Management', '/kiË ËˆmÃ¦n.ÉªdÊ’.mÉ™nt/', 'Encryption', 'Infrastructure',
 'Key management encompasses the processes and procedures for generating, distributing, storing, rotating, revoking, and destroying cryptographic keys throughout their lifecycle. Effective key management is essential â€” encryption is only as strong as the protection of its keys.\n\nKey management solutions include Hardware Security Modules (HSMs), cloud-based Key Management Services (KMS) like AWS KMS and Azure Key Vault, and key management protocols such as KMIP. NIST SP 800-57 provides comprehensive guidelines for key management best practices.',
 'Example: "The security team implemented automated **key management** with 90-day rotation policies and HSM-backed storage to ensure encryption keys are never exposed in plaintext."'),

(64, 'Elliptic Curve Cryptography', '/ÉªËˆlÉªp.tÉªk kÉœËrv krÉªpËˆtÉ’É¡.rÉ™.fi/', 'Encryption', 'Algorithms',
 'Elliptic Curve Cryptography (ECC) is an approach to public-key cryptography based on the algebraic structure of elliptic curves over finite fields. ECC provides equivalent security to RSA with significantly smaller key sizes â€” a 256-bit ECC key offers comparable security to a 3072-bit RSA key.\n\nECC algorithms include ECDSA (signatures), ECDH (key exchange), and EdDSA/Ed25519 (modern signatures). ECC is widely used in TLS, cryptocurrency (Bitcoin uses secp256k1), mobile devices, and IoT where computational resources are limited.',
 'Example: "The IoT devices use **Elliptic Curve Cryptography** for authentication because ECC provides strong security with minimal processing overhead, ideal for resource-constrained hardware."'),

(65, 'Homomorphic Encryption', '/ËŒhoÊŠ.moÊŠËˆmÉ”Ër.fÉªk ÉªnËˆkrÉªp.ÊƒÉ™n/', 'Encryption', 'Advanced',
 'Homomorphic Encryption (HE) is a form of encryption that allows computations to be performed on ciphertext, producing an encrypted result which, when decrypted, matches the result of operations performed on the plaintext. This enables data processing without ever exposing the underlying data.\n\nTypes include Partially Homomorphic Encryption (supports one operation), Somewhat Homomorphic Encryption (limited operations), and Fully Homomorphic Encryption (FHE, any computation). While FHE remains computationally expensive, it has promising applications in cloud computing, healthcare analytics, and privacy-preserving machine learning.',
 'Example: "The healthcare research consortium used **homomorphic encryption** to run statistical analyses on patient data across multiple hospitals without any institution revealing raw patient records."'),

-- ============================================================
-- PROTOCOLS (new terms 66-75)
-- ============================================================

(66, 'OAuth 2.0', '/oÊŠ.É”ËÎ¸ tuË poÉªnt oÊŠ/', 'Protocols', 'Authorization',
 'OAuth 2.0 is an industry-standard authorization framework that enables third-party applications to obtain limited access to a user\'s resources without exposing their credentials. It is the protocol behind "Sign in with Google/Facebook/GitHub" buttons.\n\nOAuth 2.0 defines four grant types: Authorization Code (most secure, used with PKCE for public clients), Client Credentials (server-to-server), Resource Owner Password (legacy), and Implicit (deprecated). OAuth 2.0 is typically combined with OpenID Connect (OIDC) for authentication.',
 'Example: "The mobile app uses **OAuth 2.0** with PKCE to securely authenticate users through their Google accounts without the app ever handling their Google passwords."'),

(67, 'HTTPS', '/ËŒeÉªtÊƒ.tiË.tiË.piË.ËˆÉ›s/', 'Protocols', 'Web Security',
 'HTTPS (HyperText Transfer Protocol Secure) is the secure version of HTTP, using TLS encryption to protect data transmitted between a web browser and a web server. HTTPS provides confidentiality, integrity, and authentication for web communications.\n\nHTTPS uses digital certificates issued by Certificate Authorities to verify server identity. Modern best practices include HSTS (HTTP Strict Transport Security) headers, certificate transparency logs, and TLS 1.3. Since 2018, major browsers mark non-HTTPS sites as "Not Secure."',
 'Example: "After migrating to **HTTPS** with HSTS preloading, the website secured all user data in transit and improved its search engine ranking as Google prioritizes secure sites."'),

(68, 'DNSSEC', '/diË.É›n.É›s.sÉ›k/', 'Protocols', 'Network Security',
 'DNS Security Extensions (DNSSEC) is a suite of specifications that adds authentication and integrity to the Domain Name System (DNS). DNSSEC uses digital signatures to verify that DNS responses have not been tampered with and originate from the authoritative source.\n\nDNSSEC protects against DNS spoofing and cache poisoning attacks by creating a chain of trust from the root DNS servers down to individual domains. While DNSSEC ensures data integrity, it does not encrypt DNS queries â€” DNS over HTTPS (DoH) and DNS over TLS (DoT) address privacy.',
 'Example: "After implementing **DNSSEC** on all company domains, the risk of DNS cache poisoning attacks redirecting users to fraudulent websites was eliminated."'),

(69, 'LDAP', '/ËˆÉ›l.dÃ¦p/', 'Protocols', 'Identity & Access',
 'Lightweight Directory Access Protocol (LDAP) is an open, vendor-neutral protocol for accessing and maintaining distributed directory information services over an IP network. It is commonly used for centralized authentication, storing user accounts, organizational structures, and other directory data.\n\nLDAP is the foundation of Microsoft Active Directory and other directory services. LDAPS (LDAP over SSL/TLS) encrypts the connection. Common operations include bind (authenticate), search, add, delete, and modify. LDAP injection attacks target applications that construct LDAP queries from user input.',
 'Example: "The organization uses **LDAP** integrated with Active Directory to provide single sign-on across all internal applications and enforce centralized access control policies."'),

(70, 'Kerberos', '/ËˆkÉœËr.bÉ™r.É’s/', 'Protocols', 'Authentication',
 'Kerberos is a network authentication protocol that uses secret-key cryptography to provide strong authentication for client/server applications. Developed at MIT, it is the default authentication protocol for Windows Active Directory environments.\n\nKerberos uses a trusted third party â€” the Key Distribution Center (KDC) â€” consisting of an Authentication Server (AS) and a Ticket Granting Server (TGS). Users authenticate once and receive time-limited tickets (TGTs) to access multiple services without re-entering credentials (single sign-on). Kerberoasting is a common attack technique against the protocol.',
 'Example: "The enterprise network relies on **Kerberos** authentication through Active Directory to provide employees seamless single sign-on to email, file shares, and internal applications."'),

(71, 'SAML', '/sÃ¦m.É™l/', 'Protocols', 'Authentication',
 'Security Assertion Markup Language (SAML) is an open XML-based standard for exchanging authentication and authorization data between an identity provider (IdP) and a service provider (SP). SAML 2.0 is the current version and is widely used for enterprise single sign-on (SSO).\n\nIn a SAML flow, the IdP authenticates the user and sends a signed SAML assertion to the SP, which grants access based on the assertion\'s attributes. SAML assertions contain authentication statements, attribute statements, and authorization decision statements.',
 'Example: "The company configured **SAML 2.0** federation between their Okta identity provider and all SaaS applications, enabling employees to access everything with a single login."'),

(72, 'IPsec', '/ËŒaÉª.piËËˆsek/', 'Protocols', 'Network Security',
 'Internet Protocol Security (IPsec) is a suite of protocols that secures Internet Protocol (IP) communications by authenticating and encrypting each IP packet in a communication session. IPsec operates at the network layer (Layer 3) of the OSI model.\n\nIPsec has two modes: Transport mode (encrypts payload only) and Tunnel mode (encrypts entire IP packet, used in VPNs). It uses two main protocols: Authentication Header (AH) for integrity and Encapsulating Security Payload (ESP) for encryption. IKE (Internet Key Exchange) handles key negotiation.',
 'Example: "The site-to-site VPN uses **IPsec** tunnel mode with AES-256 encryption and IKEv2 key exchange to securely connect the headquarters and branch office networks."'),

(73, 'SSH', '/É›s.É›s.eÉªtÊƒ/', 'Protocols', 'Remote Access',
 'Secure Shell (SSH) is a cryptographic network protocol for operating network services securely over an unsecured network. SSH provides a secure channel for remote command-line login, remote command execution, file transfer (SCP/SFTP), and port forwarding/tunneling.\n\nSSH uses public-key cryptography for authentication and symmetric encryption for session data. Key-based authentication is preferred over passwords. OpenSSH is the most widely used implementation. Best practices include disabling root login, using key-based auth only, and changing the default port.',
 'Example: "System administrators use **SSH** with key-based authentication to securely manage remote Linux servers, eliminating the risk of password-based brute force attacks."'),

(74, 'RADIUS', '/ËˆreÉª.di.É™s/', 'Protocols', 'Authentication',
 'Remote Authentication Dial-In User Service (RADIUS) is a networking protocol that provides centralized Authentication, Authorization, and Accounting (AAA) management for users who connect to and use a network service. It is widely used for controlling access to network resources.\n\nRADIUS is commonly used for enterprise Wi-Fi authentication (802.1X), VPN access, ISP subscriber management, and network device administration. The protocol communicates between a NAS (Network Access Server) and the RADIUS server. RADIUS over TLS (RadSec) provides encrypted transport.',
 'Example: "The corporate Wi-Fi network uses **RADIUS** with 802.1X authentication to verify employee credentials against Active Directory before granting network access."'),

(75, 'WPA3', '/ËŒdÊŒb.É™l.juË.piË.ËˆeÉª Î¸riË/', 'Protocols', 'Wireless Security',
 'Wi-Fi Protected Access 3 (WPA3) is the latest security protocol for wireless networks, introduced by the Wi-Fi Alliance in 2018 as the successor to WPA2. WPA3 provides stronger encryption and authentication mechanisms to address vulnerabilities found in WPA2.\n\nKey improvements include Simultaneous Authentication of Equals (SAE) replacing the vulnerable PSK handshake (preventing offline dictionary attacks), 192-bit security suite for enterprise (aligned with CNSA), forward secrecy (past sessions remain secure even if the password is compromised), and enhanced protection for open networks through Opportunistic Wireless Encryption (OWE).',
 'Example: "The office upgraded all access points to **WPA3**-Enterprise to take advantage of SAE handshakes and forward secrecy, significantly improving wireless security."'),

-- ============================================================
-- TOOLS (new terms 76-85)
-- ============================================================

(76, 'Wireshark', '/ËˆwaÉªÉ™r.ÊƒÉ‘Ërk/', 'Tools', 'Network Analysis',
 'Wireshark is the world\'s most widely used open-source network protocol analyzer. It allows users to capture and interactively browse network traffic in real-time or from saved capture files. Wireshark can decode hundreds of protocols and is used for network troubleshooting, analysis, protocol development, and security auditing.\n\nKey features include live packet capture, deep inspection of hundreds of protocols, powerful display filters (e.g., `http.request.method == \"POST\"`), packet reassembly, and export to various formats. It runs on Windows, macOS, and Linux.',
 'Example: "The security analyst used **Wireshark** to capture and analyze suspicious network traffic, discovering that malware was exfiltrating data through DNS tunneling."'),

(77, 'Nmap', '/É›n.mÃ¦p/', 'Tools', 'Reconnaissance',
 'Nmap (Network Mapper) is a free, open-source tool used for network discovery, security auditing, and vulnerability scanning. Created by Gordon Lyon, it is one of the most essential tools in any security professional\'s toolkit.\n\nNmap can discover hosts, services, operating systems, and firewalls on a network. Key features include port scanning (SYN, TCP connect, UDP), service/version detection (-sV), OS fingerprinting (-O), scripting engine (NSE) with 600+ scripts, and output in multiple formats. Zenmap provides a GUI interface.',
 'Example: "The penetration tester ran **Nmap** with service detection and script scanning to map all open ports and identify vulnerable services across the target network."'),

(78, 'Metasploit', '/Ëˆmet.É™.splÉ”Éªt/', 'Tools', 'Penetration Testing',
 'Metasploit is the world\'s most widely used penetration testing framework. Developed by Rapid7, it provides security professionals with tools to verify vulnerabilities, manage security assessments, and improve security awareness.\n\nThe Metasploit Framework includes over 2,200 exploits, 1,300 auxiliary modules, 400+ post-exploitation modules, and hundreds of payloads including Meterpreter. It supports the complete penetration testing lifecycle: reconnaissance, exploitation, post-exploitation, and reporting. Available in open-source (Framework) and commercial (Pro) editions.',
 'Example: "Using **Metasploit**, the red team exploited an unpatched vulnerability on the web server, established a Meterpreter session, and demonstrated the potential for lateral movement."'),

(79, 'Burp Suite', '/bÉœËrp swiËt/', 'Tools', 'Web Security Testing',
 'Burp Suite, developed by PortSwigger, is the industry-standard platform for web application security testing. It provides an integrated set of tools for performing comprehensive security assessments of web applications.\n\nKey components include the Proxy (intercepting HTTP/S traffic), Scanner (automated vulnerability detection), Intruder (customized attack automation), Repeater (manual request manipulation), and Decoder (data transformation). Burp Suite supports testing for OWASP Top 10 vulnerabilities, API security issues, and business logic flaws.',
 'Example: "The application security engineer used **Burp Suite** to intercept API requests and discover that the server accepted modified price values, revealing a critical business logic vulnerability."'),

(80, 'Nessus', '/Ëˆnes.É™s/', 'Tools', 'Vulnerability Scanning',
 'Nessus, developed by Tenable, is one of the most widely deployed vulnerability assessment solutions. It scans networks and systems for known vulnerabilities, misconfigurations, default credentials, and compliance violations.\n\nNessus uses a database of over 200,000 plugins that are continuously updated. It supports credentialed and non-credentialed scans, compliance auditing (PCI DSS, HIPAA, CIS benchmarks), malware detection, and configuration assessment. Results include CVSS scores and remediation guidance.',
 'Example: "The weekly **Nessus** vulnerability scan detected a critical OpenSSL vulnerability on 15 servers, enabling the team to prioritize and apply patches before attackers could exploit them."'),

(81, 'Snort', '/snÉ”Ërt/', 'Tools', 'Network Security',
 'Snort is an open-source intrusion detection and prevention system (IDS/IPS) capable of performing real-time traffic analysis and packet logging. Originally developed by Martin Roesch, Snort is now maintained by Cisco and is the most widely deployed IDS/IPS in the world.\n\nSnort operates in three modes: sniffer (read and display packets), packet logger (log packets to disk), and Network Intrusion Detection System (analyze traffic against a rule set). The Snort community maintains thousands of detection rules covering known attacks, malware signatures, and suspicious network behavior.',
 'Example: "The security team deployed **Snort** in inline IPS mode to automatically block known attack signatures while logging all suspicious activity for further analysis."'),

(82, 'CrowdStrike Falcon', '/kraÊŠd.straÉªk ËˆfÃ¦l.kÉ™n/', 'Tools', 'Endpoint Security',
 'CrowdStrike Falcon is a cloud-native endpoint protection platform that combines next-generation antivirus (NGAV), endpoint detection and response (EDR), managed threat hunting, and threat intelligence into a single lightweight agent.\n\nFalcon uses behavioral AI and indicators of attack (IOAs) rather than signatures to detect threats including fileless malware, zero-day attacks, and sophisticated adversaries. The platform provides real-time visibility into all endpoint activity and integrates threat intelligence from CrowdStrike\'s analysis of over 6 trillion security events per week.',
 'Example: "After deploying **CrowdStrike Falcon** across all endpoints, the SOC gained real-time visibility and detected a fileless attack that traditional antivirus had completely missed."'),

(83, 'SOAR', '/sÉ”Ër/', 'Tools', 'Security Operations',
 'Security Orchestration, Automation, and Response (SOAR) platforms integrate security tools and automate incident response workflows. SOAR solutions help security teams manage alerts more efficiently by combining orchestration (connecting tools), automation (executing playbooks), and case management.\n\nSOAR platforms typically integrate with SIEMs, firewalls, EDR solutions, threat intelligence feeds, and ticketing systems. Automated playbooks handle repetitive tasks like enriching IOCs, blocking malicious IPs, isolating compromised endpoints, and notifying stakeholders. Popular SOAR solutions include Palo Alto XSOAR, Splunk SOAR, and IBM Resilient.',
 'Example: "By implementing a **SOAR** platform, the security team reduced their mean time to respond from 4 hours to 12 minutes through automated phishing investigation playbooks."'),

(84, 'WAF', '/wÃ¦f/', 'Tools', 'Web Security',
 'A Web Application Firewall (WAF) is a security solution that monitors, filters, and blocks HTTP/HTTPS traffic to and from a web application. WAFs protect against web exploits like SQL injection, cross-site scripting (XSS), file inclusion, and other OWASP Top 10 attacks.\n\nWAFs operate using rule sets (signatures), anomaly detection, and behavioral analysis. They can be deployed as network appliances, host-based agents, or cloud-based services (e.g., AWS WAF, Cloudflare, Akamai). WAFs complement traditional firewalls by understanding application-layer (Layer 7) protocols.',
 'Example: "The cloud **WAF** blocked over 10,000 SQL injection attempts in the first week after deployment, protecting the web application from automated attack tools."'),

(85, 'Password Manager', '/ËˆpÃ¦s.wÉœËrd ËˆmÃ¦n.Éª.dÊ’É™r/', 'Tools', 'Identity & Access',
 'A password manager is a software application that stores, generates, and manages passwords for online accounts in an encrypted vault secured by a single master password. Password managers promote security by enabling users to use unique, complex passwords for every account without memorizing them.\n\nFeatures typically include password generation, auto-fill, secure sharing, breach monitoring, two-factor authentication support, and cross-device synchronization. Enterprise password managers add features like role-based access, audit logs, and shared vaults. Popular options include Bitwarden, 1Password, KeePass, and Dashlane.',
 'Example: "After the company mandated **password manager** usage, password reuse dropped by 95% and help desk password reset tickets decreased by 60%."');


-- ----------------------------------------------------------
-- 7b. Related Terms (cross-references)
-- ----------------------------------------------------------
INSERT INTO term_links (term_id, linked_id) VALUES
-- Access Control
(1, 4),
(1, 5),
(1, 45),

-- Advanced Persistent Threat
(2, 44),
(2, 35),
(2, 39),
(2, 28),

-- API Security
(3, 4),
(3, 37),
(3, 36),
(3, 14),

-- Authentication
(4, 5),
(4, 40),
(4, 1),
(4, 32),

-- Authorization
(5, 4),
(5, 1),
(5, 45),

-- Attack Vector
(6, 32),
(6, 35),
(6, 20),
(6, 28),

-- Antivirus
(7, 19),
(7, 28),
(7, 33),

-- Botnet
(8, 15),
(8, 28),
(8, 43),

-- Brute Force Attack
(9, 4),
(9, 40),
(9, 23),

-- Backdoor
(10, 28),
(10, 20),
(10, 2),

-- CIA Triad
(11, 18),
(11, 1),
(11, 34),

-- Cryptography
(12, 18),
(12, 23),
(12, 37),

-- Cross-Site Scripting
(13, 14),
(13, 36),
(13, 3),

-- CSRF
(14, 13),
(14, 36),
(14, 4),

-- DDoS Attack
(15, 8),
(15, 21),
(15, 26),

-- Data Breach
(16, 22),
(16, 26),
(16, 18),
(16, 32),

-- Dark Web
(17, 16),
(17, 39),
(17, 18),

-- Encryption
(18, 12),
(18, 37),
(18, 23),
(18, 41),

-- Endpoint Security
(19, 7),
(19, 28),
(19, 38),

-- Exploit
(20, 44),
(20, 42),
(20, 31),

-- Firewall
(21, 41),
(21, 25),
(21, 30),

-- GDPR
(22, 16),
(22, 18),
(22, 34),

-- Hashing
(23, 12),
(23, 18),
(23, 9),

-- Honeypot
(24, 25),
(24, 39),
(24, 26),

-- IDS
(25, 21),
(25, 38),
(25, 30),

-- Incident Response
(26, 16),
(26, 38),
(26, 39),
(26, 34),

-- Keylogger
(27, 28),
(27, 32),
(27, 35),

-- Malware
(28, 33),
(28, 43),
(28, 7),
(28, 19),

-- MitM
(29, 18),
(29, 37),
(29, 41),

-- Network Segmentation
(30, 21),
(30, 45),
(30, 41),

-- Penetration Testing
(31, 42),
(31, 20),
(31, 36),
(31, 13),

-- Phishing
(32, 35),
(32, 40),
(32, 6),
(32, 16),

-- Ransomware
(33, 28),
(33, 26),
(33, 16),
(33, 10),

-- Risk Assessment
(34, 11),
(34, 42),
(34, 22),

-- Social Engineering
(35, 32),
(35, 6),
(35, 40),

-- SQL Injection
(36, 13),
(36, 3),
(36, 31),

-- SSL/TLS
(37, 18),
(37, 12),
(37, 41),

-- SIEM
(38, 25),
(38, 26),
(38, 39),

-- Threat Intelligence
(39, 38),
(39, 26),
(39, 2),
(39, 17),

-- 2FA
(40, 4),
(40, 32),
(40, 9),

-- VPN
(41, 18),
(41, 21),
(41, 37),
(41, 45),

-- Vulnerability
(42, 20),
(42, 31),
(42, 44),
(42, 34),

-- Worm
(43, 28),
(43, 8),
(43, 42),

-- Zero-Day
(44, 20),
(44, 42),
(44, 2),
(44, 31),

-- Zero Trust
(45, 1),
(45, 30),
(45, 4),
(45, 41),

-- HIPAA
(46, 22),
(46, 55),
(46, 18),
(46, 34),

-- PCI DSS
(47, 18),
(47, 21),
(47, 80),
(47, 48),

-- SOC 2
(48, 49),
(48, 34),
(48, 50),

-- ISO 27001
(49, 48),
(49, 50),
(49, 34),
(49, 22),

-- NIST Framework
(50, 49),
(50, 54),
(50, 34),
(50, 45),

-- CCPA
(51, 22),
(51, 55),
(51, 16),

-- SOX Compliance
(52, 48),
(52, 1),
(52, 34),

-- FERPA
(53, 46),
(53, 55),
(53, 1),

-- CMMC
(54, 50),
(54, 49),
(54, 1),

-- Data Privacy
(55, 22),
(55, 51),
(55, 46),
(55, 18),

-- AES
(56, 60),
(56, 18),
(56, 63),
(56, 57),

-- RSA Encryption
(57, 61),
(57, 58),
(57, 64),
(57, 62),

-- PKI
(58, 57),
(58, 62),
(58, 37),
(58, 67),

-- End-to-End Encryption
(59, 18),
(59, 61),
(59, 63),

-- Symmetric Encryption
(60, 56),
(60, 61),
(60, 63),
(60, 18),

-- Asymmetric Encryption
(61, 57),
(61, 64),
(61, 60),
(61, 62),

-- Digital Signature
(62, 57),
(62, 58),
(62, 23),
(62, 61),

-- Key Management
(63, 56),
(63, 18),
(63, 58),

-- ECC
(64, 57),
(64, 61),
(64, 37),

-- Homomorphic Encryption
(65, 18),
(65, 55),
(65, 63),

-- OAuth 2.0
(66, 71),
(66, 4),
(66, 5),
(66, 3),

-- HTTPS
(67, 37),
(67, 58),
(67, 68),

-- DNSSEC
(68, 67),
(68, 62),
(68, 29),

-- LDAP
(69, 70),
(69, 4),
(69, 1),
(69, 71),

-- Kerberos
(70, 69),
(70, 4),
(70, 40),
(70, 71),

-- SAML
(71, 66),
(71, 70),
(71, 4),

-- IPsec
(72, 41),
(72, 18),
(72, 73),
(72, 21),

-- SSH
(73, 72),
(73, 18),
(73, 4),
(73, 41),

-- RADIUS
(74, 69),
(74, 70),
(74, 75),
(74, 4),

-- WPA3
(75, 18),
(75, 74),
(75, 56),

-- Wireshark
(76, 77),
(76, 81),
(76, 25),

-- Nmap
(77, 76),
(77, 78),
(77, 80),
(77, 31),

-- Metasploit
(78, 77),
(78, 79),
(78, 31),
(78, 20),

-- Burp Suite
(79, 78),
(79, 84),
(79, 36),
(79, 13),

-- Nessus
(80, 77),
(80, 42),
(80, 47),

-- Snort
(81, 25),
(81, 21),
(81, 76),
(81, 38),

-- CrowdStrike Falcon
(82, 19),
(82, 7),
(82, 38),
(82, 83),

-- SOAR
(83, 38),
(83, 26),
(83, 82),

-- WAF
(84, 21),
(84, 36),
(84, 13),
(84, 79),

-- Password Manager
(85, 40),
(85, 9),
(85, 4),
(85, 18);


-- ----------------------------------------------------------
-- 7c. Term Threats
-- ----------------------------------------------------------
INSERT INTO threats (term_id, threat_title, threat_desc) VALUES
-- API Security
(3, 'Injection Attacks', 'SQL, NoSQL, or command injection through API endpoints.'),
(3, 'Broken Authentication', 'Weak or compromised authentication mechanisms.'),
(3, 'Data Exposure', 'Sensitive data leaked through API responses.'),
(3, 'DDoS Attacks', 'Overwhelming API endpoints with requests.'),

-- Authentication
(4, 'Credential Stuffing', 'Attackers use leaked credentials from other breaches.'),
(4, 'Brute Force', 'Automated password guessing attempts.'),
(4, 'Session Hijacking', 'Stealing authenticated session tokens.'),

-- Encryption
(18, 'Key Management Failures', 'Poor key storage or rotation practices.'),
(18, 'Protocol Downgrade', 'Forcing use of weaker encryption algorithms.'),
(18, 'Side-Channel Attacks', 'Extracting keys through timing or power analysis.'),

-- Firewall
(21, 'Firewall Bypass', 'Techniques to circumvent firewall rules using tunneling.'),
(21, 'Misconfiguration', 'Overly permissive rules that allow unauthorized traffic.'),

-- Phishing
(32, 'Credential Harvesting', 'Fake login pages designed to steal usernames and passwords.'),
(32, 'Malware Delivery', 'Phishing emails carrying malicious attachments or links.'),
(32, 'Business Email Compromise', 'Impersonating executives to authorize fraudulent transactions.'),

-- Ransomware
(33, 'Data Encryption', 'Files encrypted and held for ransom payment.'),
(33, 'Double Extortion', 'Threatening to publish stolen data if ransom is not paid.'),
(33, 'Supply Chain Attacks', 'Ransomware delivered through compromised software updates.'),

-- SQL Injection
(36, 'Data Theft', 'Extracting sensitive data from databases through malicious queries.'),
(36, 'Authentication Bypass', 'Bypassing login forms to gain unauthorized access.'),
(36, 'Database Destruction', 'Dropping tables or corrupting data through injected commands.'),

-- Social Engineering
(35, 'Pretexting', 'Creating fabricated scenarios to manipulate victims.'),
(35, 'Baiting', 'Offering something enticing to lure victims into a trap.'),
(35, 'Tailgating', 'Physically following authorized personnel into restricted areas.');


-- ----------------------------------------------------------
-- 7d. Per-Term Learning Resources
-- ----------------------------------------------------------
INSERT INTO resources (term_id, resource_title, resource_url, resource_icon) VALUES
-- Access Control
(1, 'NIST Access Control Guide', 'https://csrc.nist.gov/publications/detail/sp/800-162/final', 'fas fa-book'),
(1, 'OWASP Access Control Cheat Sheet', 'https://cheatsheetseries.owasp.org/cheatsheets/Access_Control_Cheat_Sheet.html', 'fas fa-shield-alt'),

-- APT
(2, 'MITRE ATT&CK Framework', 'https://attack.mitre.org/', 'fas fa-crosshairs'),
(2, 'CISA APT Resources', 'https://www.cisa.gov/topics/cyber-threats-and-advisories/advanced-persistent-threats', 'fas fa-flag'),

-- API Security
(3, 'OWASP API Security Top 10', 'https://owasp.org/API-Security/', 'fas fa-shield-alt'),
(3, 'API Security Best Practices', 'https://swagger.io/resources/articles/best-practices-in-api-security/', 'fas fa-code'),

-- Authentication
(4, 'NIST Authentication Guidelines', 'https://pages.nist.gov/800-63-3/', 'fas fa-book'),
(4, 'OWASP Authentication Cheat Sheet', 'https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html', 'fas fa-shield-alt'),

-- Authorization
(5, 'OWASP Authorization Cheat Sheet', 'https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html', 'fas fa-shield-alt'),

-- Attack Vector
(6, 'MITRE ATT&CK Techniques', 'https://attack.mitre.org/techniques/enterprise/', 'fas fa-crosshairs'),

-- Antivirus
(7, 'AV-TEST Institute', 'https://www.av-test.org/', 'fas fa-vial'),
(7, 'VirusTotal', 'https://www.virustotal.com/', 'fas fa-search'),

-- Botnet
(8, 'Spamhaus Botnet Threat Report', 'https://www.spamhaus.org/statistics/botnet/', 'fas fa-chart-bar'),

-- Brute Force Attack
(9, 'OWASP Brute Force Prevention', 'https://owasp.org/www-community/controls/Blocking_Brute_Force_Attacks', 'fas fa-shield-alt'),

-- Backdoor
(10, 'MITRE Backdoor Techniques', 'https://attack.mitre.org/techniques/T1547/', 'fas fa-crosshairs'),

-- CIA Triad
(11, 'NIST Cybersecurity Framework', 'https://www.nist.gov/cyberframework', 'fas fa-landmark'),
(11, 'ISO/IEC 27001 Overview', 'https://www.iso.org/isoiec-27001-information-security.html', 'fas fa-certificate'),

-- Cryptography
(12, 'Khan Academy Cryptography', 'https://www.khanacademy.org/computing/computer-science/cryptography', 'fas fa-graduation-cap'),
(12, 'Cryptographic Standards (NIST)', 'https://csrc.nist.gov/projects/cryptographic-standards-and-guidelines', 'fas fa-book'),

-- XSS
(13, 'OWASP XSS Prevention', 'https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html', 'fas fa-shield-alt'),
(13, 'PortSwigger XSS Labs', 'https://portswigger.net/web-security/cross-site-scripting', 'fas fa-flask'),

-- CSRF
(14, 'OWASP CSRF Prevention', 'https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html', 'fas fa-shield-alt'),

-- DDoS
(15, 'Cloudflare DDoS Learning', 'https://www.cloudflare.com/learning/ddos/what-is-a-ddos-attack/', 'fas fa-cloud'),
(15, 'CISA DDoS Guide', 'https://www.cisa.gov/sites/default/files/publications/understanding-and-responding-to-ddos-attacks_508c.pdf', 'fas fa-flag'),

-- Data Breach
(16, 'Have I Been Pwned', 'https://haveibeenpwned.com/', 'fas fa-search'),
(16, 'Verizon DBIR Report', 'https://www.verizon.com/business/resources/reports/dbir/', 'fas fa-chart-line'),

-- Dark Web
(17, 'Tor Project', 'https://www.torproject.org/', 'fas fa-user-secret'),

-- Encryption
(18, 'NIST Encryption Standards', 'https://csrc.nist.gov/projects/cryptographic-standards-and-guidelines', 'fas fa-book'),
(18, 'Let''s Encrypt (Free TLS)', 'https://letsencrypt.org/', 'fas fa-lock'),

-- Endpoint Security
(19, 'Gartner EPP Magic Quadrant', 'https://www.gartner.com/reviews/market/endpoint-protection-platforms', 'fas fa-chart-bar'),

-- Exploit
(20, 'Exploit Database', 'https://www.exploit-db.com/', 'fas fa-database'),
(20, 'CVE Details', 'https://www.cvedetails.com/', 'fas fa-bug'),

-- Firewall
(21, 'NIST Firewall Guidelines', 'https://csrc.nist.gov/publications/detail/sp/800-41/rev-1/final', 'fas fa-book'),
(21, 'PfSense Documentation', 'https://docs.netgate.com/pfsense/en/latest/', 'fas fa-server'),

-- GDPR
(22, 'Official GDPR Text', 'https://gdpr.eu/', 'fas fa-gavel'),
(22, 'ICO GDPR Guide', 'https://ico.org.uk/for-organisations/guide-to-data-protection/guide-to-the-general-data-protection-regulation-gdpr/', 'fas fa-book'),

-- Hashing
(23, 'OWASP Password Storage', 'https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html', 'fas fa-shield-alt'),

-- Honeypot
(24, 'HoneyDB', 'https://honeydb.io/', 'fas fa-database'),

-- IDS
(25, 'Snort IDS', 'https://www.snort.org/', 'fas fa-shield-alt'),
(25, 'Suricata', 'https://suricata.io/', 'fas fa-shield-alt'),

-- Incident Response
(26, 'NIST IR Guide (SP 800-61)', 'https://csrc.nist.gov/publications/detail/sp/800-61/rev-2/final', 'fas fa-book'),
(26, 'SANS Incident Handler Handbook', 'https://www.sans.org/white-papers/33901/', 'fas fa-graduation-cap'),

-- Keylogger
(27, 'KnowBe4 Keylogger Info', 'https://www.knowbe4.com/keylogger', 'fas fa-info-circle'),

-- Malware
(28, 'VirusTotal', 'https://www.virustotal.com/', 'fas fa-search'),
(28, 'MalwareBazaar', 'https://bazaar.abuse.ch/', 'fas fa-database'),

-- MitM
(29, 'OWASP MitM Prevention', 'https://owasp.org/www-community/attacks/Manipulator-in-the-middle_attack', 'fas fa-shield-alt'),

-- Network Segmentation
(30, 'NIST Network Segmentation', 'https://csrc.nist.gov/publications/detail/sp/800-125b/final', 'fas fa-book'),

-- Penetration Testing
(31, 'OWASP Testing Guide', 'https://owasp.org/www-project-web-security-testing-guide/', 'fas fa-shield-alt'),
(31, 'Hack The Box', 'https://www.hackthebox.com/', 'fas fa-terminal'),
(31, 'TryHackMe', 'https://tryhackme.com/', 'fas fa-graduation-cap'),

-- Phishing
(32, 'Anti-Phishing Working Group', 'https://apwg.org/', 'fas fa-users'),
(32, 'KnowBe4 Phishing Resources', 'https://www.knowbe4.com/phishing', 'fas fa-graduation-cap'),
(32, 'Google Phishing Quiz', 'https://phishingquiz.withgoogle.com/', 'fas fa-gamepad'),

-- Ransomware
(33, 'No More Ransom Project', 'https://www.nomoreransom.org/', 'fas fa-unlock'),
(33, 'CISA Ransomware Guide', 'https://www.cisa.gov/stopransomware', 'fas fa-flag'),

-- Risk Assessment
(34, 'NIST Risk Assessment (SP 800-30)', 'https://csrc.nist.gov/publications/detail/sp/800-30/rev-1/final', 'fas fa-book'),
(34, 'FAIR Institute', 'https://www.fairinstitute.org/', 'fas fa-balance-scale'),

-- Social Engineering
(35, 'Social Engineer Toolkit', 'https://www.social-engineer.org/', 'fas fa-user-secret'),
(35, 'KnowBe4 Security Awareness', 'https://www.knowbe4.com/', 'fas fa-graduation-cap'),

-- SQL Injection
(36, 'OWASP SQLi Prevention', 'https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html', 'fas fa-shield-alt'),
(36, 'PortSwigger SQLi Labs', 'https://portswigger.net/web-security/sql-injection', 'fas fa-flask'),
(36, 'SQLMap Tool', 'https://sqlmap.org/', 'fas fa-terminal'),

-- SSL/TLS
(37, 'SSL Labs Server Test', 'https://www.ssllabs.com/ssltest/', 'fas fa-search'),
(37, 'Mozilla SSL Configuration', 'https://ssl-config.mozilla.org/', 'fas fa-cog'),

-- SIEM
(38, 'Splunk Documentation', 'https://docs.splunk.com/', 'fas fa-book'),
(38, 'ELK Stack Guide', 'https://www.elastic.co/guide/index.html', 'fas fa-graduation-cap'),

-- Threat Intelligence
(39, 'MITRE ATT&CK', 'https://attack.mitre.org/', 'fas fa-crosshairs'),
(39, 'AlienVault OTX', 'https://otx.alienvault.com/', 'fas fa-globe'),

-- 2FA
(40, 'FIDO Alliance', 'https://fidoalliance.org/', 'fas fa-key'),
(40, 'Authy Guide', 'https://authy.com/guides/', 'fas fa-mobile-alt'),

-- VPN
(41, 'WireGuard', 'https://www.wireguard.com/', 'fas fa-shield-alt'),
(41, 'OpenVPN', 'https://openvpn.net/', 'fas fa-lock'),

-- Vulnerability
(42, 'NVD (National Vulnerability Database)', 'https://nvd.nist.gov/', 'fas fa-database'),
(42, 'CVE Program', 'https://www.cve.org/', 'fas fa-bug'),
(42, 'CVSS Calculator', 'https://www.first.org/cvss/calculator/3.1', 'fas fa-calculator'),

-- Worm
(43, 'SANS Malware Analysis', 'https://www.sans.org/cyber-security-courses/reverse-engineering-malware-malware-analysis-tools-techniques/', 'fas fa-graduation-cap'),

-- Zero-Day
(44, 'Google Project Zero', 'https://googleprojectzero.blogspot.com/', 'fas fa-search'),
(44, 'Zero Day Initiative', 'https://www.zerodayinitiative.com/', 'fas fa-bug'),

-- Zero Trust
(45, 'NIST Zero Trust Architecture', 'https://csrc.nist.gov/publications/detail/sp/800-207/final', 'fas fa-book'),
(45, 'CISA Zero Trust Maturity Model', 'https://www.cisa.gov/zero-trust-maturity-model', 'fas fa-flag'),
(45, 'Microsoft Zero Trust Guide', 'https://www.microsoft.com/en-us/security/business/zero-trust', 'fab fa-microsoft'),

-- HIPAA
(46, 'HHS HIPAA Home', 'https://www.hhs.gov/hipaa/index.html', 'fas fa-landmark'),
(46, 'HIPAA Journal', 'https://www.hipaajournal.com/', 'fas fa-newspaper'),

-- PCI DSS
(47, 'PCI Security Standards Council', 'https://www.pcisecuritystandards.org/', 'fas fa-credit-card'),
(47, 'PCI DSS Quick Reference Guide', 'https://www.pcisecuritystandards.org/document_library/', 'fas fa-book'),

-- SOC 2
(48, 'AICPA SOC 2 Overview', 'https://www.aicpa.org/topic/audit-assurance/audit-and-assurance-greater-than-soc-2', 'fas fa-certificate'),
(48, 'SOC 2 Compliance Guide', 'https://socreports.com/soc-2-overview', 'fas fa-book'),

-- ISO 27001
(49, 'ISO 27001 Official Page', 'https://www.iso.org/isoiec-27001-information-security.html', 'fas fa-certificate'),
(49, 'ISO 27001 Implementation Guide', 'https://advisera.com/27001academy/', 'fas fa-graduation-cap'),

-- NIST Framework
(50, 'NIST CSF Official', 'https://www.nist.gov/cyberframework', 'fas fa-landmark'),
(50, 'NIST CSF 2.0 Reference Tool', 'https://csrc.nist.gov/Projects/Cybersecurity-Framework', 'fas fa-tools'),

-- CCPA
(51, 'California AG CCPA Page', 'https://oag.ca.gov/privacy/ccpa', 'fas fa-landmark'),
(51, 'IAPP CCPA Resources', 'https://iapp.org/resources/topics/ccpa-and-cpra/', 'fas fa-book'),

-- SOX Compliance
(52, 'SEC SOX Resources', 'https://www.sec.gov/spotlight/sarbanes-oxley.htm', 'fas fa-landmark'),
(52, 'SOX Compliance Guide', 'https://www.sarbanes-oxley-101.com/', 'fas fa-book'),

-- FERPA
(53, 'Dept. of Education FERPA', 'https://studentprivacy.ed.gov/', 'fas fa-landmark'),
(53, 'FERPA Compliance Checklist', 'https://studentprivacy.ed.gov/resources', 'fas fa-clipboard-check'),

-- CMMC
(54, 'DoD CMMC Official', 'https://dodcio.defense.gov/CMMC/', 'fas fa-flag'),
(54, 'CMMC-AB Marketplace', 'https://cyberab.org/', 'fas fa-store'),

-- Data Privacy
(55, 'IAPP Data Privacy Resources', 'https://iapp.org/', 'fas fa-globe'),
(55, 'NIST Privacy Framework', 'https://www.nist.gov/privacy-framework', 'fas fa-landmark'),

-- AES
(56, 'NIST FIPS 197 (AES Standard)', 'https://csrc.nist.gov/publications/detail/fips/197/final', 'fas fa-book'),
(56, 'AES Explained (Computerphile)', 'https://www.youtube.com/watch?v=O4xNJsjtN6E', 'fab fa-youtube'),

-- RSA Encryption
(57, 'RSA Algorithm Explained', 'https://www.khanacademy.org/computing/computer-science/cryptography/modern-crypt/v/intro-to-rsa-encryption', 'fas fa-graduation-cap'),
(57, 'NIST Key Management Guidelines', 'https://csrc.nist.gov/publications/detail/sp/800-57-part-1/rev-5/final', 'fas fa-book'),

-- PKI
(58, 'PKI Consortium', 'https://pkic.org/', 'fas fa-certificate'),
(58, 'Let''s Encrypt (Free CA)', 'https://letsencrypt.org/', 'fas fa-lock'),

-- End-to-End Encryption
(59, 'Signal Protocol Documentation', 'https://signal.org/docs/', 'fas fa-shield-alt'),
(59, 'EFF Encryption Guide', 'https://ssd.eff.org/module/communicating-others', 'fas fa-book'),

-- Symmetric Encryption
(60, 'NIST Block Cipher Techniques', 'https://csrc.nist.gov/projects/block-cipher-techniques', 'fas fa-book'),

-- Asymmetric Encryption
(61, 'Khan Academy Public Key Crypto', 'https://www.khanacademy.org/computing/computer-science/cryptography/modern-crypt/v/diffie-hellman-key-exchange', 'fas fa-graduation-cap'),

-- Digital Signature
(62, 'NIST Digital Signature Standard', 'https://csrc.nist.gov/publications/detail/fips/186/5/final', 'fas fa-book'),
(62, 'DocuSign Digital Signatures', 'https://www.docusign.com/how-it-works/electronic-signature/digital-signature/digital-signature-faq', 'fas fa-file-signature'),

-- Key Management
(63, 'NIST SP 800-57 Key Management', 'https://csrc.nist.gov/publications/detail/sp/800-57-part-1/rev-5/final', 'fas fa-book'),
(63, 'AWS KMS Documentation', 'https://docs.aws.amazon.com/kms/', 'fab fa-aws'),

-- ECC
(64, 'Cloudflare ECC Primer', 'https://blog.cloudflare.com/a-relatively-easy-to-understand-primer-on-elliptic-curve-cryptography/', 'fas fa-cloud'),

-- Homomorphic Encryption
(65, 'Microsoft SEAL Library', 'https://www.microsoft.com/en-us/research/project/microsoft-seal/', 'fab fa-microsoft'),
(65, 'HomomorphicEncryption.org', 'https://homomorphicencryption.org/', 'fas fa-lock'),

-- OAuth 2.0
(66, 'OAuth 2.0 RFC 6749', 'https://tools.ietf.org/html/rfc6749', 'fas fa-book'),
(66, 'OAuth.net Resources', 'https://oauth.net/2/', 'fas fa-globe'),

-- HTTPS
(67, 'Google HTTPS Transparency Report', 'https://transparencyreport.google.com/https/overview', 'fab fa-google'),
(67, 'Mozilla SSL Configuration Generator', 'https://ssl-config.mozilla.org/', 'fas fa-cogs'),

-- DNSSEC
(68, 'ICANN DNSSEC Guide', 'https://www.icann.org/resources/pages/dnssec-what-is-it-why-important-2019-03-05-en', 'fas fa-globe'),
(68, 'Cloudflare DNSSEC Guide', 'https://www.cloudflare.com/dns/dnssec/', 'fas fa-cloud'),

-- LDAP
(69, 'LDAP.com Introduction', 'https://ldap.com/', 'fas fa-book'),
(69, 'Microsoft AD Documentation', 'https://learn.microsoft.com/en-us/windows-server/identity/ad-ds/', 'fab fa-microsoft'),

-- Kerberos
(70, 'MIT Kerberos Documentation', 'https://web.mit.edu/kerberos/', 'fas fa-graduation-cap'),
(70, 'MITRE Kerberoasting', 'https://attack.mitre.org/techniques/T1558/', 'fas fa-crosshairs'),

-- SAML
(71, 'OASIS SAML Wiki', 'https://wiki.oasis-open.org/security/FrontPage', 'fas fa-book'),
(71, 'Okta SAML Guide', 'https://developer.okta.com/docs/concepts/saml/', 'fas fa-key'),

-- IPsec
(72, 'Cisco IPsec Overview', 'https://www.cisco.com/c/en/us/products/security/vpn-endpoint-security-clients/what-is-ipsec.html', 'fas fa-network-wired'),
(72, 'RFC 4301 (IPsec Architecture)', 'https://tools.ietf.org/html/rfc4301', 'fas fa-book'),

-- SSH
(73, 'OpenSSH Official', 'https://www.openssh.com/', 'fas fa-terminal'),
(73, 'SSH Hardening Guide', 'https://www.ssh-audit.com/', 'fas fa-shield-alt'),

-- RADIUS
(74, 'FreeRADIUS Documentation', 'https://freeradius.org/', 'fas fa-book'),
(74, 'RFC 2865 (RADIUS)', 'https://tools.ietf.org/html/rfc2865', 'fas fa-file-alt'),

-- WPA3
(75, 'Wi-Fi Alliance WPA3 Spec', 'https://www.wi-fi.org/discover-wi-fi/security', 'fas fa-wifi'),
(75, 'WPA3 Security Overview', 'https://www.wi-fi.org/news-events/newsroom/wi-fi-alliance-introduces-wi-fi-certified-wpa3-security', 'fas fa-shield-alt'),

-- Wireshark
(76, 'Wireshark Official', 'https://www.wireshark.org/', 'fas fa-globe'),
(76, 'Wireshark User Guide', 'https://www.wireshark.org/docs/wsug_html_chunked/', 'fas fa-book'),

-- Nmap
(77, 'Nmap Official', 'https://nmap.org/', 'fas fa-globe'),
(77, 'Nmap Reference Guide', 'https://nmap.org/book/man.html', 'fas fa-book'),

-- Metasploit
(78, 'Metasploit Documentation', 'https://docs.metasploit.com/', 'fas fa-book'),
(78, 'Rapid7 Metasploit', 'https://www.metasploit.com/', 'fas fa-globe'),

-- Burp Suite
(79, 'PortSwigger Burp Suite', 'https://portswigger.net/burp', 'fas fa-globe'),
(79, 'PortSwigger Web Security Academy', 'https://portswigger.net/web-security', 'fas fa-graduation-cap'),

-- Nessus
(80, 'Tenable Nessus', 'https://www.tenable.com/products/nessus', 'fas fa-globe'),
(80, 'Nessus Plugins', 'https://www.tenable.com/plugins', 'fas fa-puzzle-piece'),

-- Snort
(81, 'Snort Official', 'https://www.snort.org/', 'fas fa-globe'),
(81, 'Snort Rules Documentation', 'https://www.snort.org/documents', 'fas fa-book'),

-- CrowdStrike Falcon
(82, 'CrowdStrike Falcon', 'https://www.crowdstrike.com/products/endpoint-security/', 'fas fa-globe'),
(82, 'CrowdStrike Threat Reports', 'https://www.crowdstrike.com/resources/reports/', 'fas fa-chart-bar'),

-- SOAR
(83, 'Gartner SOAR Guide', 'https://www.gartner.com/reviews/market/security-orchestration-automation-and-response-solutions', 'fas fa-chart-line'),
(83, 'Palo Alto XSOAR', 'https://www.paloaltonetworks.com/cortex/cortex-xsoar', 'fas fa-globe'),

-- WAF
(84, 'OWASP WAF Guide', 'https://owasp.org/www-community/Web_Application_Firewall', 'fas fa-shield-alt'),
(84, 'Cloudflare WAF', 'https://www.cloudflare.com/waf/', 'fas fa-cloud'),

-- Password Manager
(85, 'Bitwarden (Open Source)', 'https://bitwarden.com/', 'fas fa-key'),
(85, 'EFF Password Manager Guide', 'https://ssd.eff.org/module/creating-strong-passwords', 'fas fa-book');

-- ----------------------------------------------------------
-- 8. Partner Organizations
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS partners (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    description     VARCHAR(500) DEFAULT NULL,
    org_type        VARCHAR(100) DEFAULT 'Organization',
    logo_url        VARCHAR(500) DEFAULT NULL,
    cover_url       VARCHAR(500) DEFAULT NULL,
    website_url     VARCHAR(500) DEFAULT '#',
    display_order   INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS org_features (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    partner_id  INT NOT NULL,
    feature     VARCHAR(255) NOT NULL,
    icon        VARCHAR(100) DEFAULT 'fas fa-check',
    FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO partners (name, description, org_type, logo_url, website_url, display_order) VALUES
('CSIA', 'Cybersecurity Intelligence Alliance', 'SCHOOL ORGANIZATION', 'IMGS/org_logos/csia_logo.png', 'https://www.facebook.com/csia.hausoc', 1),
('CISCO', 'Networking Technology Leader', 'ENTERPRISE', 'IMGS/org_logos/cisco.png', 'https://www.cisco.com/site/ph/en/index.html', 2),
('GDG', 'Google Developers Group on Campus', 'GLOBAL SCHOOL ORGANIZATION', 'IMGS/org_logos/gdg_logo.png', 'https://www.facebook.com/gdsc.hau', 3);

INSERT INTO org_features (partner_id, feature, icon) VALUES
(1, '50+ Members', 'fas fa-users'),
(1, 'Regular Workshop & Events', 'fas fa-calendar-alt'),
(1, 'Professional Certification', 'fas fa-certificate'),
(2, 'Networking Infrastructure', 'fas fa-network-wired'),
(2, 'Cybersecurity Solutions', 'fas fa-shield-alt'),
(2, 'Cloud Technologies', 'fas fa-cloud'),
(3, 'Student Developer Community', 'fas fa-code'),
(3, 'Google Technologies', 'fab fa-google'),
(3, 'Regular Workshops & Events', 'fas fa-calendar-alt');

-- ----------------------------------------------------------
-- 9. Partnership Benefits (for partners page)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS benefits (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description VARCHAR(500) DEFAULT NULL,
    icon        VARCHAR(100) DEFAULT 'fas fa-handshake',
    display_order INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO benefits (title, description, icon, display_order) VALUES
('Collaboration', 'Joint projects and initiatives', 'fas fa-comments', 1),
('Learning', 'Shared knowledge and training', 'fas fa-graduation-cap', 2),
('Networking', 'Professional connections', 'fas fa-project-diagram', 3),
('Innovation', 'Technology advancement', 'fas fa-rocket', 4);

-- ----------------------------------------------------------
-- 10. Python Courses (Compiler sidebar)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS courses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    language    VARCHAR(50) DEFAULT 'python',
    image_url   VARCHAR(500) DEFAULT NULL,
    course_url  VARCHAR(500) DEFAULT '#',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- 10b. User-Course Enrollment (Junction Table)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS enrollments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    course_id   INT NOT NULL,
    progress    INT DEFAULT 0,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO courses (title, language, image_url) VALUES
('Basic Python Commands', 'python', NULL),
('Python Data Structures', 'python', NULL),
('Web Development with Python', 'python', NULL);

-- ----------------------------------------------------------
-- 11. Category counts view helper
-- ----------------------------------------------------------
CREATE OR REPLACE VIEW term_cat_counts AS
SELECT category, COUNT(*) as term_count FROM terms GROUP BY category;
