<?php
/**
 * Sentinel Access - Contact Tracing Kiosk Landing Page
 * USC Department of Computer Engineering
 */
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentinel Access - Contact Tracing Kiosk</title>
    
    <!-- Design System Global Styling -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Kiosk Top Navigation Bar -->
    <header class="kiosk-header">
        <div class="brand-section">
            <div class="brand-icon">
                <!-- SVG Linear Shield Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <span class="brand-name">SENTINEL ACCESS</span>
                <span class="brand-tag">USC CpE</span>
            </div>
        </div>
        
        <div class="system-status">
            <div class="status-indicator">
                <span class="status-dot"></span>
                <span>SYSTEM ONLINE</span>
            </div>
            <!-- Real-time digital clock -->
            <div id="kiosk-clock" style="font-weight: 600; font-variant-numeric: tabular-nums;">--:--:-- --</div>
        </div>
    </header>

    <!-- Main Dynamic Kiosk Section -->
    <main class="kiosk-main">
        <div class="slide-container">
            
            <!-- SLIDE 1: Welcome & Search Screen -->
            <div id="slide-welcome" class="slide active">
                <div class="kiosk-card">
                    <div class="card-header">
                        <h2>USC Department of Computer Engineering</h2>
                        <p>Welcome! Please sign in or out of the office to maintain our secure contact records.</p>
                    </div>
                    
                    <form id="form-welcome" onsubmit="handleIdentitySubmit(event)">
                        <div class="kiosk-search-container">
                            <div class="form-group">
                                <label for="kiosk-identity">Enter USC ID Number or Phone / Email</label>
                                <input type="text" id="kiosk-identity" class="kiosk-input-large" placeholder="ID Number, Phone, or Email" required autofocus autocomplete="off">
                                <span class="validation-error" id="err-identity">Please enter a valid credential.</span>
                            </div>
                        </div>
                        
                        <div class="button-group">
                            <button type="submit" class="btn btn-primary">Identify & Continue</button>
                            
                            <div class="divider">or</div>
                            
                            <button type="button" class="btn btn-secondary" onclick="navigateTo('slide-register')">Register as New Visitor</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SLIDE 2: First-Time Visitor Registration -->
            <div id="slide-register" class="slide">
                <div class="kiosk-card">
                    <div class="card-header">
                        <h2>First-Time Registration</h2>
                        <p>Please enter your contact details. This information is saved for rapid access on your next visit.</p>
                    </div>
                    
                    <form id="form-register" onsubmit="handleRegistrationSubmit(event)">
                        <!-- Toggle USC ID field -->
                        <label class="usc-toggle-container">
                            <input type="checkbox" id="usc-member-toggle" onchange="toggleUscFields(this.checked)">
                            <div class="usc-checkbox"></div>
                            <span class="usc-toggle-label">I am from USC (Student, Faculty, or Staff)</span>
                        </label>
                        
                        <!-- USC ID input container (slides/toggles) -->
                        <div id="usc-id-container" class="form-group" style="display: none; transition: all 0.3s ease;">
                            <label for="reg-usc-id">USC ID Number</label>
                            <input type="text" id="reg-usc-id" placeholder="e.g. 20102345">
                            <span class="validation-error" id="err-usc-id">USC ID is required for members.</span>
                        </div>
                        
                        <!-- Complete Name Section (Grid) -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="reg-first-name">First Name</label>
                                <input type="text" id="reg-first-name" placeholder="e.g. Maria" required>
                            </div>
                            <div class="form-group">
                                <label for="reg-middle-name">Middle Name</label>
                                <input type="text" id="reg-middle-name" placeholder="e.g. Santos">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg-last-name">Last Name</label>
                            <input type="text" id="reg-last-name" placeholder="e.g. Dela Cruz" required>
                        </div>
                        
                        <!-- Address Details Section -->
                        <div class="form-group">
                            <label for="reg-barangay">Barangay</label>
                            <input type="text" id="reg-barangay" placeholder="e.g. Talamban" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="reg-city">City or Town</label>
                                <input type="text" id="reg-city" placeholder="e.g. Cebu City" required>
                            </div>
                            <div class="form-group">
                                <label for="reg-province">Province</label>
                                <input type="text" id="reg-province" placeholder="e.g. Cebu" required>
                            </div>
                        </div>
                        
                        <!-- Contact Details Section -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="reg-phone">Contact Number</label>
                                <input type="tel" id="reg-phone" placeholder="e.g. 09171234567" required>
                            </div>
                            <div class="form-group">
                                <label for="reg-email">Email Address</label>
                                <input type="email" id="reg-email" placeholder="e.g. maria@email.com" required>
                            </div>
                        </div>
                        
                        <div class="button-group">
                            <button type="submit" class="btn btn-primary">Review & Confirm</button>
                            <button type="button" class="btn btn-secondary" onclick="resetAndReturn()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SLIDE 3: Information Verification & Check-In Approval -->
            <div id="slide-verify" class="slide">
                <div class="kiosk-card">
                    <div class="card-header">
                        <h2>Verify Your Information</h2>
                        <p id="verify-subtitle">Welcome back! Please verify that your registered details are correct before checking in.</p>
                    </div>
                    
                    <div class="info-review-grid">
                        <div class="info-item">
                            <span class="info-label">Full Name</span>
                            <span id="verify-name" class="info-value">Dela Cruz, Maria Santos</span>
                        </div>
                        <div id="verify-usc-id-row" class="info-item" style="display: none;">
                            <span class="info-label">USC ID Number</span>
                            <span id="verify-usc-id" class="info-value">20102345</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Barangay</span>
                            <span id="verify-barangay" class="info-value">Talamban</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">City</span>
                            <span id="verify-city" class="info-value">Cebu City</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Province</span>
                            <span id="verify-province" class="info-value">Cebu</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Contact Number</span>
                            <span id="verify-phone" class="info-value">09171234567</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Address</span>
                            <span id="verify-email" class="info-value">maria@email.com</span>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" id="btn-confirm-login" class="btn btn-primary" onclick="confirmCheckin()">Correct: Sign In</button>
                        <button type="button" class="btn btn-secondary" onclick="editInfo()">Incorrect: Edit Details</button>
                    </div>
                </div>
            </div>

            <!-- SLIDE 4: Completion / Thank You Screen -->
            <div id="slide-thankyou" class="slide">
                <div class="kiosk-card" style="text-align: center;">
                    <div class="success-checkmark">✓</div>
                    
                    <div class="success-banner">
                        <p id="thankyou-banner-title">ACTION COMPLETED</p>
                    </div>
                    
                    <h2 id="thankyou-title" style="font-size: 1.75rem; font-weight: 700; margin-bottom: 1rem;">Thank You!</h2>
                    <p id="thankyou-message" style="color: var(--text-secondary); font-size: 1rem; line-height: 1.6; margin-bottom: 2rem;">
                        Your access logs have been recorded. Have a great day!
                    </p>
                    
                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                        This terminal will reset in <span id="reset-counter" style="font-weight: 700; color: var(--text-secondary);">5</span> seconds.
                    </div>
                </div>
            </div>
            
        </div>
    </main>

    <!-- Overlay: Administrator Login Modal -->
    <div id="admin-login-overlay" class="overlay">
        <div class="overlay-content">
            <div class="kiosk-card">
                <div class="card-header" style="margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.4rem;">Administrator Authentication</h2>
                    <p>Access attendance logs, registry, and contact tracing logs.</p>
                </div>
                
                <form id="form-admin-login" onsubmit="handleAdminLogin(event)">
                    <div class="form-group">
                        <label for="admin-user">Username</label>
                        <input type="text" id="admin-user" placeholder="e.g. admin" required autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-pass">Password</label>
                        <input type="password" id="admin-pass" placeholder="Enter password" required>
                        <span class="validation-error" id="err-admin-login">Invalid credentials. Try again.</span>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Authenticate</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleAdminModal(false)">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification for fast non-intrusive notices -->
    <div id="kiosk-toast" class="toast">
        <div style="font-size: 1.25rem;">ℹ️</div>
        <div class="toast-message" id="toast-text">Alert message goes here.</div>
    </div>

    <!-- Footer Bar -->
    <footer class="kiosk-footer">
        <div>Sentinel Access v1.0.0 &bull; USC Department of Computer Engineering</div>
        <a class="footer-admin-link" onclick="toggleAdminModal(true)">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>Admin Portal</span>
        </a>
    </footer>

    <!-- Kiosk Javascript Controller Logic -->

</body>
    <script>
        // Store session state variables locally
        let activeContactData = null;
        let isUscMember = false;
        let countdownTimer = null;

        // Start the Digital Clock on DOM load
        function startClock() {
            const clockEl = document.getElementById('kiosk-clock');
            function updateClock() {
                const now = new Date();
                let hours = now.getHours();
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // block '0' hours
                const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;
                clockEl.textContent = timeString;
            }
            updateClock();
            setInterval(updateClock, 1000);
        }
        startClock();

        // Screen Navigation Router
        function navigateTo(slideId) {
            console.log("Dylan Testing: Not Fucking working")
            // Cancel any active redirect timers
            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
            }

            // Hide all slides, display target slide
            document.querySelectorAll('.slide').forEach(slide => {
                slide.classList.remove('active');
            });
            
            const targetSlide = document.getElementById(slideId);
            if (targetSlide) {
                targetSlide.classList.add('active');
                
                // Set focus automatically for quick input operations
                if (slideId === 'slide-welcome') {
                    document.getElementById('kiosk-identity').value = '';
                    setTimeout(() => document.getElementById('kiosk-identity').focus(), 150);
                } else if (slideId === 'slide-register') {
                    setTimeout(() => document.getElementById('reg-first-name').focus(), 150);
                }
            }
        }

        // Toggle USC Fields display when checkbox triggers
        function toggleUscFields(isChecked) {
            isUscMember = isChecked;
            const container = document.getElementById('usc-id-container');
            const input = document.getElementById('reg-usc-id');
            if (isChecked) {
                container.style.display = 'block';
                input.setAttribute('required', 'true');
                setTimeout(() => input.focus(), 100);
            } else {
                container.style.display = 'none';
                input.removeAttribute('required');
                input.value = '';
            }
        }

        // Trigger Kiosk Toast Notification
        function showToast(message, type = 'info') {
            const toast = document.getElementById('kiosk-toast');
            const text = document.getElementById('toast-text');
            text.textContent = message;
            
            if (type === 'error') {
                toast.style.borderLeftColor = 'var(--accent-error)';
            } else {
                toast.style.borderLeftColor = 'var(--accent-primary)';
            }
            
            toast.classList.add('active');
            setTimeout(() => {
                toast.classList.remove('active');
            }, 3500);
        }

        // Toggle Admin Authentication Modal Overlay
        function toggleAdminModal(show) {
            const overlay = document.getElementById('admin-login-overlay');
            const userIn = document.getElementById('admin-user');
            const errSpan = document.getElementById('err-admin-login');
            errSpan.style.display = 'none';
            
            if (show) {
                overlay.classList.add('active');
                document.getElementById('admin-user').value = '';
                document.getElementById('admin-pass').value = '';
                setTimeout(() => userIn.focus(), 150);
            } else {
                overlay.classList.remove('active');
            }
        }
             // Reset state and return to welcome slide
        function resetAndReturn() {
            activeContactData = null;
            isUscMember = false;
            
            // Reset forms
            document.getElementById('form-welcome').reset();
            document.getElementById('form-register').reset();
            document.getElementById('usc-member-toggle').checked = false;
            document.getElementById('usc-id-container').style.display = 'none';
            document.getElementById('reg-usc-id').removeAttribute('required');
            
            navigateTo('slide-welcome');
        }

        // Handle Identification Search Submission via AJAX
        function handleIdentitySubmit(event) {
            event.preventDefault();
            const identity = document.getElementById('kiosk-identity').value.trim();
            const errSpan = document.getElementById('err-identity');
            errSpan.style.display = 'none';

            if (!identity) return;

            showToast("Verifying identity...", "info");
            
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'identify', identity: identity })
            })
            .then(res => res.json())
            .catch(() => {
                showToast("Connection to backend lost.", "error");
                throw new Error("API Connection Failed");
            })
            .then(data => {
                if (data.error) {
                    showToast(data.error, "error");
                    errSpan.textContent = data.error;
                    errSpan.style.display = 'block';
                    return;
                }

                // A. New Visitor Flow
                if (data.status === 'new') {
                    showToast("No previous records found. Please register.", "info");
                    
                    // Pre-fill search value in registration form if it looks like an ID, phone, or email
                    const isNum = /^\d+$/.test(identity);
                    const isEmail = identity.includes('@');
                    
                    if (isEmail) {
                        document.getElementById('reg-email').value = identity;
                    } else if (isNum) {
                        if (identity.length >= 6) {
                            document.getElementById('usc-member-toggle').checked = true;
                            toggleUscFields(true);
                            document.getElementById('reg-usc-id').value = identity;
                        } else {
                            document.getElementById('reg-phone').value = identity;
                        }
                    }
                    
                    navigateTo('slide-register');
                } 
                
                // B. Automatic Checkout Registered Flow
                else if (data.status === 'checkout') {
                    showToast("Checkout successful!", "success");
                    showThankYouScreen(
                        "Sign-Out Registered",
                        "Goodbye, " + data.name + "!",
                        "Your checkout time has been recorded successfully. Have a safe journey home!"
                    );
                } 
                
                // C. Returning User Check-In Flow
                else if (data.status === 'returning') {
                    activeContactData = data.contact;
                    activeContactData.is_new = false;
                    
                    showToast("Record retrieved.", "success");

                    // Populate verification data sheet
                    document.getElementById('verify-subtitle').textContent = "Welcome back! Please verify that your registered details are correct before checking in.";
                    
                    const mName = activeContactData.middle_name ? ' ' + activeContactData.middle_name : '';
                    document.getElementById('verify-name').textContent = activeContactData.last_name + ", " + activeContactData.first_name + mName;
                    
                    const uscIdRow = document.getElementById('verify-usc-id-row');
                    if (activeContactData.usc_id_number) {
                        document.getElementById('verify-usc-id').textContent = activeContactData.usc_id_number;
                        uscIdRow.style.display = 'flex';
                    } else {
                        uscIdRow.style.display = 'none';
                    }
                    
                    document.getElementById('verify-barangay').textContent = activeContactData.barangay;
                    document.getElementById('verify-city').textContent = activeContactData.city;
                    document.getElementById('verify-province').textContent = activeContactData.province;
                    document.getElementById('verify-phone').textContent = activeContactData.phone_number;
                    document.getElementById('verify-email').textContent = activeContactData.email;
                    
                    document.getElementById('btn-confirm-login').textContent = "Correct: Sign In";
                    
                    navigateTo('slide-verify');
                }
            });
        }

        // Handle Registration Form Submission
        function handleRegistrationSubmit(event) {
            event.preventDefault();
            
            const isUsc = document.getElementById('usc-member-toggle').checked;
            const uscId = document.getElementById('reg-usc-id').value.trim();
            const firstName = document.getElementById('reg-first-name').value.trim();
            const middleName = document.getElementById('reg-middle-name').value.trim();
            const lastName = document.getElementById('reg-last-name').value.trim();
            const barangay = document.getElementById('reg-barangay').value.trim();
            const city = document.getElementById('reg-city').value.trim();
            const province = document.getElementById('reg-province').value.trim();
            const phone = document.getElementById('reg-phone').value.trim();
            const email = document.getElementById('reg-email').value.trim();

            if (isUsc && !uscId) {
                document.getElementById('err-usc-id').style.display = 'block';
                return;
            }

            activeContactData = {
                usc_id_number: isUsc ? uscId : null,
                first_name: firstName,
                middle_name: middleName ? middleName : null,
                last_name: lastName,
                barangay: barangay,
                city: city,
                province: province,
                phone_number: phone,
                email: email,
                is_new: true
            };

            // Populate Review Panel
            document.getElementById('verify-subtitle').textContent = "Please review your registration details carefully before confirming.";
            
            const mName = middleName ? ' ' + middleName : '';
            document.getElementById('verify-name').textContent = lastName + ", " + firstName + mName;
            
            const uscIdRow = document.getElementById('verify-usc-id-row');
            if (isUsc) {
                document.getElementById('verify-usc-id').textContent = uscId;
                uscIdRow.style.display = 'flex';
            } else {
                uscIdRow.style.display = 'none';
            }
            
            document.getElementById('verify-barangay').textContent = barangay;
            document.getElementById('verify-city').textContent = city;
            document.getElementById('verify-province').textContent = province;
            document.getElementById('verify-phone').textContent = phone;
            document.getElementById('verify-email').textContent = email;
            
            document.getElementById('btn-confirm-login').textContent = "Confirm & Check-In";
            
            navigateTo('slide-verify');
        }

        // Return to registration form to edit details
        function editInfo() {
            if (activeContactData && activeContactData.is_new) {
                navigateTo('slide-register');
            } else {
                resetAndReturn();
            }
        }

        // Check-in / Registration Save Action via AJAX
        function confirmCheckin() {
            if (!activeContactData) return;

            showToast("Recording entry log...", "info");

            // Option A: Save New Registration & Check-in
            if (activeContactData.is_new) {
                const payload = Object.assign({ action: 'register' }, activeContactData);
                
                fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        showToast(data.error, "error");
                        return;
                    }

                    showToast("Registration successful!", "success");
                    showThankYouScreen(
                        "Sign-In Registered",
                        "Welcome, " + data.name + "!",
                        "Your registration details have been saved, and check-in time recorded successfully. Have a nice stay!"
                    );
                });
            } 
            
            // Option B: Check-in Returning User
            else {
                fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'checkin', contact_id: activeContactData.id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        showToast(data.error, "error");
                        return;
                    }

                    showToast("Check-in successful!", "success");
                    showThankYouScreen(
                        "Sign-In Registered",
                        "Welcome Back, " + data.name + "!",
                        "Your entry time has been recorded successfully. Have a productive stay!"
                    );
                });
            }
        }

        // Redirect helper showing successful results
        function showThankYouScreen(banner, title, msg) {
            document.getElementById('thankyou-banner-title').textContent = banner.toUpperCase();
            document.getElementById('thankyou-title').textContent = title;
            document.getElementById('thankyou-message').textContent = msg;
            
            navigateTo('slide-thankyou');
            
            // 5 second visual redirection loop back to Welcome Screen
            let secsLeft = 5;
            const counter = document.getElementById('reset-counter');
            counter.textContent = secsLeft;
            
            countdownTimer = setInterval(() => {
                secsLeft--;
                counter.textContent = secsLeft;
                if (secsLeft <= 0) {
                    clearInterval(countdownTimer);
                    resetAndReturn();
                }
            }, 1000);
        }

        // Handle Admin Login Verification via AJAX
        function handleAdminLogin(event) {
            event.preventDefault();
            const user = document.getElementById('admin-user').value.trim();
            const pass = document.getElementById('admin-pass').value;
            const errSpan = document.getElementById('err-admin-login');
            errSpan.style.display = 'none';
            
            showToast("Authenticating credentials...", "info");
            
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'admin_login', username: user, password: pass })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast("Authentication successful! Redirecting...", "success");
                    setTimeout(() => {
                        window.location.href = 'admin.php';
                    }, 1000);
                } else {
                    errSpan.style.display = 'block';
                    showToast("Authentication failed.", "error");
                }
            });
        }
    </script>
</html>