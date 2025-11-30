/**
 * Admin Dashboard JavaScript
 * Full functionality for course and instructor management
 */

// Global state
let courses = [];
let instructors = [];
let registrations = [];

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    checkAuth();

    initializeDashboard();
    setupEventListeners();
    loadAllData();

    // Setup edit application form submit
    const editForm = document.getElementById('editApplicationForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveEditedApplication();
        });
    }
});

// Check if user is authenticated
function checkAuth() {
    const isLoggedIn = sessionStorage.getItem('adminLoggedIn');
    const loginTime = sessionStorage.getItem('loginTime');

    if (!isLoggedIn || isLoggedIn !== 'true') {
        window.location.href = 'admin-login.html';
        return;
    }

    // Check session expiry (8 hours)
    const currentTime = new Date().getTime();
    const hoursPassed = (currentTime - loginTime) / (1000 * 60 * 60);

    if (hoursPassed >= 8) {
        sessionStorage.removeItem('adminLoggedIn');
        sessionStorage.removeItem('loginTime');
        alert('Session vaxtı bitib. Yenidən daxil olun.');
        window.location.href = 'admin-login.html';
        return;
    }
}

function initializeDashboard() {
    console.log('Admin Dashboard Initialized');

    // Show dashboard by default
    showSection('dashboard');

    // Activate first menu item
    document.querySelector('.sidebar-menu a').classList.add('active');
}

function setupEventListeners() {
    // Sidebar menu clicks
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all
            document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));

            // Add active class to clicked
            this.classList.add('active');

            // Show corresponding section
            const section = this.dataset.section;
            showSection(section);
        });
    });
}

function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });

    // Show selected section
    const section = document.getElementById(sectionName);
    if (section) {
        section.classList.add('active');
    }

    // Load section-specific data
    switch(sectionName) {
        case 'dashboard':
            loadDashboardStats();
            break;
        case 'courses':
            loadCourses();
            break;
        case 'instructors':
            loadInstructors();
            break;
        case 'registrations':
            loadRegistrations();
            break;
        case 'badges':
            window.location.href = 'badge-generator.html';
            break;
    }
}

async function loadAllData() {
    try {
        // Load courses
        const coursesRes = await fetch('upcoming-courses.json');
        courses = await coursesRes.json();

        // Load instructors
        try {
            const instructorsRes = await fetch('api/instructors.php');
            const instructorsData = await instructorsRes.json();
            instructors = instructorsData.data || [];
        } catch (e) {
            instructors = [];
        }

        // Load registrations from teacher.json
        try {
            const regRes = await fetch('teacher.json');
            const regData = await regRes.json();
            registrations = regData.applications || [];
        } catch (e) {
            registrations = [];
        }

        // Update dashboard
        loadDashboardStats();

    } catch (error) {
        console.error('Error loading data:', error);
    }
}

function loadDashboardStats() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Total courses
    document.getElementById('totalCourses').textContent = courses.length;

    // Active courses (upcoming)
    const activeCourses = courses.filter(course => {
        const courseDate = new Date(course.date);
        courseDate.setHours(0, 0, 0, 0);
        return courseDate >= today;
    });
    document.getElementById('activeCourses').textContent = activeCourses.length;

    // Total instructors
    document.getElementById('totalInstructors').textContent = instructors.length;

    // Total registrations
    document.getElementById('totalRegistrations').textContent = registrations.length;
}

function loadCourses() {
    const tbody = document.getElementById('coursesTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (courses.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Kurs tapılmadı</td></tr>';
        return;
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    courses.forEach(course => {
        const courseDate = new Date(course.date);
        courseDate.setHours(0, 0, 0, 0);
        const isActive = courseDate >= today;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>#${course.id}</strong></td>
            <td>${course.title.az}</td>
            <td>${course.date}</td>
            <td>${course.location.az}</td>
            <td>${course.seat}</td>
            <td>
                <span class="badge-status ${isActive ? 'badge-active' : 'badge-expired'}">
                    ${isActive ? 'Aktiv' : 'Bitib'}
                </span>
            </td>
            <td class="action-buttons">
                <button class="btn-sm-custom" style="background: #3b82f6; color: white;" onclick="viewCourse(${course.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-sm-custom" style="background: #10b981; color: white;" onclick="editCourse(${course.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-sm-custom" style="background: #ef4444; color: white;" onclick="deleteCourse(${course.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function loadInstructors() {
    const tbody = document.getElementById('instructorsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (instructors.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px;">Həkim tapılmadı</td></tr>';
        return;
    }

    instructors.forEach(instructor => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <img src="${instructor.image || 'img/default-avatar.png'}"
                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"
                     onerror="this.src='img/default-avatar.png'">
            </td>
            <td><strong>${instructor.name}</strong></td>
            <td>${instructor.specialty || '-'}</td>
            <td>${instructor.email || '-'}</td>
            <td>${instructor.phone || '-'}</td>
            <td class="action-buttons">
                <button class="btn-sm-custom" style="background: #10b981; color: white;" onclick="editInstructor(${instructor.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-sm-custom" style="background: #ef4444; color: white;" onclick="deleteInstructor(${instructor.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function loadRegistrations() {
    const tbody = document.getElementById('registrationsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (registrations.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px;">Təlimçi müraciəti yoxdur</td></tr>';
        return;
    }

    // Filter out test entries
    const realApplications = registrations.filter(app =>
        !app.id.startsWith('test_') && app.tamAd && app.email
    );

    realApplications.forEach(app => {
        const row = document.createElement('tr');

        // Format phone number
        const phone = app.telefonNömrəsi?.tam || '-';

        // Format date
        const date = app.təqdimTarixiFormatted || new Date(app.təqdimTarixi).toLocaleString('az-AZ');

        row.innerHTML = `
            <td><strong>${app.tamAd}</strong></td>
            <td>${app.email}</td>
            <td>${phone}</td>
            <td>${app.akademikDərəcə || '-'}</td>
            <td>${app.tibbiİxtisas || '-'}</td>
            <td>${date}</td>
            <td class="action-buttons">
                <button class="btn-sm-custom" style="background: #3b82f6; color: white;" onclick="viewApplication('${app.id}')" title="Görüntülə">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-sm-custom" style="background: #10b981; color: white;" onclick="editApplication('${app.id}')" title="Redaktə et">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-sm-custom" style="background: #f59e0b; color: white;" onclick="generateCertificate('${app.id}')" title="Sertifikat">
                    <i class="fas fa-certificate"></i>
                </button>
                <button class="btn-sm-custom" style="background: #ef4444; color: white;" onclick="deleteApplication('${app.id}')" title="Sil">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
    });

    if (realApplications.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px;">Təlimçi müraciəti yoxdur</td></tr>';
    }
}

// Course functions
function viewCourse(id) {
    window.location.href = `viewcourse.html?id=${id}`;
}

function editCourse(id) {
    alert('Edit Course #' + id + '\n\nBu funksiya tezliklə əlavə ediləcək.');
}

function deleteCourse(id) {
    if (!confirm('Bu kursu silmək istədiyinizə əminsiniz?')) return;

    courses = courses.filter(c => c.id !== id);

    // Save to JSON
    fetch('add-course.php', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(() => {
        alert('Kurs silindi');
        loadCourses();
        loadDashboardStats();
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Xəta baş verdi');
    });
}

// Instructor functions
function editInstructor(id) {
    alert('Edit Instructor #' + id + '\n\nBu funksiya tezliklə əlavə ediləcək.');
}

function deleteInstructor(id) {
    if (!confirm('Bu həkimi silmək istədiyinizə əminsiniz?')) return;

    fetch(`api/instructors.php?id=${id}`, {
        method: 'DELETE'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Həkim silindi');
            loadAllData();
        } else {
            alert('Xəta: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Xəta baş verdi');
    });
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

// Dark mode toggle
function toggleDarkMode() {
    if (window.themeManager) {
        window.themeManager.toggleTheme();
    }
}

// Logout function
function logout() {
    if (confirm('Çıxış etmək istədiyinizə əminsiniz?')) {
        sessionStorage.removeItem('adminLoggedIn');
        sessionStorage.removeItem('loginTime');
        window.location.href = 'admin-login.html';
    }
}

// Open add instructor modal
function openAddInstructorModal() {
    alert('Həkim Əlavə Et funksiyası\n\nTezliklə tam form əlavə ediləcək.\n\nHazırda API hazırdır:\nPOST /api/instructors.php');
}

// Application (Registrations) functions
function viewApplication(id) {
    const app = registrations.find(a => a.id === id);
    if (!app) return;

    alert(`Müraciət Məlumatları:\n\nAd Soyad: ${app.tamAd}\nEmail: ${app.email}\nTelefon: ${app.telefonNömrəsi?.tam || '-'}\nAkademik Dərəcə: ${app.akademikDərəcə || '-'}\nTibbi İxtisas: ${app.tibbiİxtisas || '-'}\nİş Təcrübəsi: ${app.işTəcrübəsi || '-'}\nÜstünlük: ${app.üstünlükVerilənKurs || '-'}`);
}

function editApplication(id) {
    const app = registrations.find(a => a.id === id);
    if (!app) return;

    // Fill modal with application data
    document.getElementById('editAppId').value = app.id;
    document.getElementById('editTamAd').value = app.tamAd || '';
    document.getElementById('editEmail').value = app.email || '';
    document.getElementById('editTelefon').value = app.telefonNömrəsi?.tam || '';
    document.getElementById('editAkademikDərəcə').value = app.akademikDərəcə || '';
    document.getElementById('editTibbiİxtisas').value = app.tibbiİxtisas || '';
    document.getElementById('editİşTəcrübəsi').value = app.işTəcrübəsi || '';
    document.getElementById('editÜstünlükVerilənKurs').value = app.üstünlükVerilənKurs || '';
    document.getElementById('editQoşulmaSəbəbi').value = app.qoşulmaSəbəbi || '';

    // Open modal
    openModal('editApplicationModal');
}

function generateCertificate(id) {
    const app = registrations.find(a => a.id === id);
    if (!app) return;

    // Redirect to certificate generator with pre-filled data
    const params = new URLSearchParams({
        name: app.tamAd,
        email: app.email,
        appId: id
    });

    // Add preferred course if available
    if (app.üstünlükVerilənKurs) {
        // Find matching course from upcoming-courses.json by category or title
        const matchedCourse = courses.find(c => {
            // Match by category value or title
            const categoryMatch = c.category && (
                c.category.az?.toLowerCase().includes(app.üstünlükVerilənKurs.toLowerCase()) ||
                c.category.en?.toLowerCase().includes(app.üstünlükVerilənKurs.toLowerCase())
            );
            const titleMatch = c.title && (
                c.title.az?.toLowerCase().includes(app.üstünlükVerilənKurs.toLowerCase()) ||
                c.title.en?.toLowerCase().includes(app.üstünlükVerilənKurs.toLowerCase())
            );

            // Special case for "Vinir" -> Aesthetic
            if (app.üstünlükVerilənKurs === 'Vinir') {
                return c.title.az?.includes('Estetik') || c.category.az?.includes('Estetik');
            }

            return categoryMatch || titleMatch;
        });

        if (matchedCourse) {
            params.append('courseId', matchedCourse.id);
        }
    }

    window.location.href = `admin-certificate.html?${params.toString()}`;
}

function deleteApplication(id) {
    if (!confirm('Bu müraciəti silmək istədiyinizə əminsiniz?')) return;

    // Filter out the application
    registrations = registrations.filter(a => a.id !== id);

    // Save back to teacher.json
    fetch('api/save-applications.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ applications: registrations })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Müraciət silindi');
            loadRegistrations();
            loadDashboardStats();
        } else {
            alert('Xəta: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Xəta baş verdi');
    });
}

function saveEditedApplication() {
    const id = document.getElementById('editAppId').value;
    const telefon = document.getElementById('editTelefon').value;

    // Parse phone number
    let telefonNömrəsi = null;
    if (telefon) {
        const cleanPhone = telefon.replace(/[^0-9+]/g, '');
        if (cleanPhone.startsWith('+')) {
            const parts = cleanPhone.split(' ');
            telefonNömrəsi = {
                ölkəKodu: parts[0] || '+994',
                nömrə: parts.slice(1).join('').replace(/[^0-9]/g, ''),
                tam: cleanPhone
            };
        } else {
            telefonNömrəsi = {
                ölkəKodu: '+994',
                nömrə: cleanPhone,
                tam: '+994 ' + cleanPhone
            };
        }
    }

    // Update application in registrations array
    const appIndex = registrations.findIndex(a => a.id === id);
    if (appIndex === -1) {
        alert('Müraciət tapılmadı');
        return;
    }

    // Update the application
    registrations[appIndex] = {
        ...registrations[appIndex],
        tamAd: document.getElementById('editTamAd').value,
        email: document.getElementById('editEmail').value,
        telefonNömrəsi: telefonNömrəsi,
        akademikDərəcə: document.getElementById('editAkademikDərəcə').value,
        tibbiİxtisas: document.getElementById('editTibbiİxtisas').value,
        işTəcrübəsi: document.getElementById('editİşTəcrübəsi').value,
        üstünlükVerilənKurs: document.getElementById('editÜstünlükVerilənKurs').value,
        qoşulmaSəbəbi: document.getElementById('editQoşulmaSəbəbi').value
    };

    // Save to teacher.json
    fetch('api/save-applications.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ applications: registrations })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Müraciət yeniləndi');
            closeModal('editApplicationModal');
            loadRegistrations();
        } else {
            alert('Xəta: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Xəta baş verdi');
    });
}

// Export functions to global scope
window.showSection = showSection;
window.viewCourse = viewCourse;
window.editCourse = editCourse;
window.deleteCourse = deleteCourse;
window.editInstructor = editInstructor;
window.deleteInstructor = deleteInstructor;
window.openModal = openModal;
window.closeModal = closeModal;
window.toggleDarkMode = toggleDarkMode;
window.logout = logout;
window.openAddInstructorModal = openAddInstructorModal;
window.viewApplication = viewApplication;
window.editApplication = editApplication;
window.generateCertificate = generateCertificate;
window.deleteApplication = deleteApplication;
