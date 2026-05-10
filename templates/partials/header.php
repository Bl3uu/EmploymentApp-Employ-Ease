<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'EmployEase'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            
            <div class="flex items-center gap-6">
                <h1 class="text-xl font-bold tracking-tight text-black">
                    <?php echo ($_SESSION['role_id'] == 1) ? 'Employer' : 'EmployEase'; ?>
                </h1>

                <?php if ($_SESSION['role_id'] == 1): ?>
                <nav class="hidden md:flex items-center gap-4 border-l pl-6 border-gray-200">
                    <a href="dashboard" class="text-sm font-medium <?php echo $path === '/dashboard' ? 'text-black' : 'text-gray-500 hover:text-black'; ?>">
                        Dashboard
                    </a>
                    <a href="post-job" class="text-sm font-medium <?php echo $path === '/post-job' ? 'text-black' : 'text-gray-500 hover:text-black'; ?>">
                        Post Job
                    </a>
                    <a href="manage-exams" class="text-sm font-medium <?php echo $path === '/manage-exams' ? 'text-black' : 'text-gray-500 hover:text-black'; ?>">
                        Manage Exams
                    </a>
                </nav>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-4">
                <?php if ($_SESSION['role_id'] == 2): ?>
                    <div class="relative">
                        <button id="notificationBtn" class="relative p-2 text-gray-500 hover:text-black transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V7a3 3 0 00-6 0v5l-5 5h5m0 0v1a3 3 0 006 0v-1m-6 0h6"></path>
                            </svg>
                            <span id="notificationBadge" class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center hidden">0</span>
                        </button>
                        <div id="notificationDropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 hidden z-50">
                            <div class="p-4 border-b border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                            </div>
                            <div id="notificationList" class="max-h-64 overflow-y-auto">
                                <!-- Notifications will be loaded here -->
                            </div>
                            <div class="p-4 border-t border-gray-100">
                                <a href="notifications" class="text-sm text-black hover:underline">View all notifications</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <span class="hidden sm:inline text-sm font-medium text-gray-600">
                    Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?>
                </span>
                <button onclick="handleLogout()" class="text-sm px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl hover:bg-gray-100 transition-colors font-semibold">
                    Logout
                </button>
            </div>
        </div>
    </header>

    <script>
        function handleLogout() {
            if(confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout';
            }
        }

        <?php if ($_SESSION['role_id'] == 2): ?>
        // Notification dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationList = document.getElementById('notificationList');
            const notificationBadge = document.getElementById('notificationBadge');

            // Toggle dropdown
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('hidden');
                if (!notificationDropdown.classList.contains('hidden')) {
                    loadNotifications();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
            });

            // Load notifications
            async function loadNotifications() {
                try {
                    const response = await fetch('notifications');
                    const data = await response.json();
                    displayNotifications(data.notifications);
                } catch (error) {
                    console.error('Error loading notifications:', error);
                }
            }

            function displayNotifications(notifications) {
                notificationList.innerHTML = '';
                if (notifications.length === 0) {
                    notificationList.innerHTML = '<p class="p-4 text-sm text-gray-500">No notifications yet.</p>';
                    notificationBadge.classList.add('hidden');
                } else {
                    notifications.slice(0, 5).forEach(notification => {
                        const item = document.createElement('div');
                        item.className = 'p-4 border-b border-gray-100 hover:bg-gray-50';
                        item.innerHTML = `
                            <p class="text-xs uppercase tracking-widest text-gray-400">Notification</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">${notification.title || 'Update'}</p>
                            <p class="text-sm text-gray-600 mt-1">${notification.message || 'No details available.'}</p>
                            <p class="text-xs text-gray-400 mt-1">${new Date(notification.created_at).toLocaleDateString()}</p>
                        `;
                        notificationList.appendChild(item);
                    });
                    if (notifications.length > 5) {
                        const moreItem = document.createElement('div');
                        moreItem.className = 'p-4 text-center';
                        moreItem.innerHTML = '<a href="notifications" class="text-sm text-black hover:underline">View more...</a>';
                        notificationList.appendChild(moreItem);
                    }
                    notificationBadge.classList.add('hidden');
                }
            }

            // Load notifications on page load
            loadNotifications();
        });
        <?php endif; ?>
    </script>