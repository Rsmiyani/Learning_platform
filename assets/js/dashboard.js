// ============================================
// FEATURE 1 & 2: Search & Filter
// ============================================

const searchInput = document.getElementById('globalSearch');
if (searchInput) {
    // Initialize with current query from URL if present
    const urlParams = new URLSearchParams(window.location.search);
    const currentQuery = urlParams.get('search') || '';
    if (!searchInput.value) {
        searchInput.value = currentQuery;
    }

    // Simple debounce helper
    let searchDebounceTimer = null;
    function debounce(fn, delay) {
        return function(...args) {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function applySearch(query) {
        const url = new URL(window.location.href);
        if (query && query.trim().length > 0) {
            url.searchParams.set('search', query.trim());
        } else {
            url.searchParams.delete('search');
        }
        // Preserve other filters like category; just navigate
        window.location.href = url.toString();
    }

    function goToAllCourses() {
        const currentPath = window.location.pathname;
        if (currentPath.includes('/pages/trainee/all-courses.php')) return;

        const target = new URL('../../pages/trainee/all-courses.php', window.location.href);
        const query = searchInput.value.trim();
        if (query) target.searchParams.set('search', query);
        const cat = urlParams.get('category');
        if (cat) target.searchParams.set('category', cat);
        window.location.href = target.toString();
    }

    // Debounced input search (dashboard can still filter its own tiles)
    const debouncedApplySearch = debounce((q) => applySearch(q), 400);
    searchInput.addEventListener('input', (e) => {
        debouncedApplySearch(e.target.value);
    });

    // Enter to search immediately
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            applySearch(searchInput.value);
        }
    });

    // Redirect to All Courses reliably on user interaction
    searchInput.addEventListener('focus', goToAllCourses);
    searchInput.addEventListener('click', goToAllCourses);
    searchInput.addEventListener('mousedown', goToAllCourses);
}

// ============================================
// FEATURE 5: Bookmarks/Quick Links
// ============================================

document.querySelectorAll('.bookmark-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const courseId = btn.getAttribute('data-course-id');
        const isBookmarked = btn.classList.contains('bookmarked');
        
        try {
            const response = await fetch('../../handlers/bookmark-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: courseId,
                    action: isBookmarked ? 'remove' : 'add'
                })
            });

            const data = await response.json();
            if (data.success) {
                btn.classList.toggle('bookmarked');
                btn.style.animation = 'pulse 0.5s ease-out';
                showNotification('Bookmark ' + (isBookmarked ? 'removed' : 'added') + '!');
            }
        } catch (error) {
            console.error('Bookmark error:', error);
            showNotification('Error saving bookmark!', 'error');
        }
    });
});

// ============================================
// FEATURE 3: Leaderboard (No JS needed)
// ============================================

// ============================================
// FEATURE 4: Certificates Download
// ============================================

function downloadCertificate(certId) {
    console.log('Downloading certificate:', certId);
    window.location.href = `../../handlers/generate-certificate.php?cert_id=${certId}`;
}

// ============================================
// CONTINUE BUTTON - Continue Course
// ============================================

document.querySelectorAll('.continue-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const courseCard = this.closest('.course-card-circular');
        const courseName = courseCard.querySelector('h3').textContent;
        showNotification('Loading: ' + courseName, 'info');
        setTimeout(() => {
            console.log('Redirecting to course page...');
        }, 500);
    });
});

// ============================================
// ENROLL BUTTON - Simple (Just change text)
// ============================================

document.querySelectorAll('.enroll-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.preventDefault();
        
        const courseId = btn.getAttribute('data-course-id');
        
        try {
            const response = await fetch('../../handlers/enroll-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: courseId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Change button text to Enrolled
                btn.textContent = '✓ Enrolled';
                btn.disabled = true;
                showNotification('✅ Enrolled Successfully!', 'success');
            } else {
                showNotification('❌ ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error enrolling course', 'error');
        }
    });
});

// ============================================
// CIRCULAR PROGRESS ANIMATION
// ============================================

function animateProgress() {
    const circles = document.querySelectorAll('.progress-fill');
    circles.forEach(circle => {
        const parent = circle.closest('.circular-progress');
        const progress = parseInt(parent.getAttribute('data-progress')) || 0;
        const circumference = 2 * Math.PI * 45;
        const offset = circumference - (progress / 100) * circumference;
        circle.style.strokeDashoffset = offset;
    });
}

// ============================================
// CHART.JS - Weekly Activity
// ============================================

const ctx = document.getElementById('weeklyChart');
if (ctx) {
    // Get real data from PHP (passed via global variable)
    const realData = typeof weeklyActivityData !== 'undefined' ? weeklyActivityData : [];
    
    // Create arrays for last 7 days
    const today = new Date();
    const labels = [];
    const dataValues = [];
    const backgroundColors = [];
    
    // Generate labels and data for last 7 days
    for (let i = 6; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        
        // Get day name
        const dayName = date.toLocaleDateString('en-US', { weekday: 'short' });
        labels.push(dayName);
        
        // Get date string for matching
        const dateString = date.toISOString().split('T')[0];
        
        // Find matching data from database
        const matchingData = realData.find(d => d.date === dateString);
        const hours = matchingData ? parseFloat(matchingData.hours) : 0;
        dataValues.push(hours);
        
        // Alternate colors (purple and teal)
        backgroundColors.push(i % 2 === 0 ? '#6B5B95' : '#009B95');
    }
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Hours Studied',
                data: dataValues,
                backgroundColor: backgroundColors,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' hours';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + 'h';
                        }
                    }
                }
            }
        }
    });
}

// ============================================
// NOTIFICATIONS
// ============================================

document.getElementById('notificationBtn')?.addEventListener('click', () => {
    document.getElementById('notificationDropdown').classList.toggle('show');
});

document.addEventListener('click', (e) => {
    const notifBtn = document.getElementById('notificationBtn');
    const notifDropdown = document.getElementById('notificationDropdown');
    if (notifBtn && notifDropdown && !notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
        notifDropdown.classList.remove('show');
    }
});

// ============================================
// TOAST NOTIFICATION HELPER
// ============================================

function showNotification(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 14px 20px;
        background: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        font-weight: 500;
        font-family: 'Inter', sans-serif;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ============================================
// SIDEBAR MENU TOGGLE (Mobile)
// ============================================

const menuToggle = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('.sidebar');

if (menuToggle) {
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
    });
}

document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('show');
        }
    });
});

// ============================================
// INITIALIZE ON PAGE LOAD
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('✨ Dashboard loaded with all features!');
    animateProgress();
    
    document.querySelectorAll('.stat-box').forEach((box, index) => {
        box.style.animation = `slideIn 0.5s ease-out ${index * 0.1}s forwards`;
        box.style.opacity = '0';
    });
    
    document.querySelectorAll('.card').forEach((card, index) => {
        card.style.animation = `slideIn 0.5s ease-out ${index * 0.1 + 0.3}s forwards`;
        card.style.opacity = '0';
    });
});

// ============================================
// KEYBOARD SHORTCUTS
// ============================================

document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('globalSearch')?.focus();
    }
});

// ============================================
// SMOOTH SCROLL UTILITY
// ============================================

function smoothScroll(element) {
    element.scrollIntoView({ behavior: 'smooth' });
}

// ============================================
// ADD HOVER EFFECTS
// ============================================

document.querySelectorAll('.course-card-circular').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px)';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

document.querySelectorAll('.recommended-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px)';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// ============================================
// RESPONSIVE ADJUSTMENTS
// ============================================

window.addEventListener('resize', () => {
    if (window.innerWidth > 768 && sidebar) {
        sidebar.classList.remove('show');
    }
});
